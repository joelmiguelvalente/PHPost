<?php

declare(strict_types=1);

/**
 * @package    PHPost/Class
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsFotos {

	protected Paginator $Paginator;
	protected IP $IP;
	private string $myIP;

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User
	) {
		$this->Paginator = new Paginator;
		$this->IP = new IP;
		$this->myIP = $this->IP->getIP();
	}

	private function isUserLogin(): bool {
		return $this->User->is_member && (int)$this->User->info['user_baneado'] === 0 && (int)$this->User->info['user_activo'] === 1;
	}

	private function isAdminBan(): bool {
		return $this->isUserLogin() && ($this->User->is_admod || $this->User->permiso('global.fotos.publicar'));
	}

	private function collectPostData(): array {
		$title = trim($_POST['title'] ?? '');
		return [
			'title' => $this->Core->setSecure($this->Core->parseBadWords($title), true),
			'url' => $this->Core->setSecure($this->Core->parseBadWords($_POST['url'] ?? ''), true),
			'file' => $_FILES['file'] ?? null,
			'description' => $this->Core->setSecure($this->Core->parseBadWords(substr($_POST['description'] ?? '', 0, 500)), true),
			'closed' => (int)($_POST['closed'] ?? 0),
			'visitas' => (int)($_POST['visitas'] ?? 0),
			'ip' => $this->myIP
		];
	}

	/**
	 * Verifica anti-flood y duplicados
	 */
	private function validateAntiFlood(array $data): ?string {
		$antiflood = (int)$this->User->permiso('limites.antiflood') * 5;
		$f_date = time() - $antiflood;

		$exists = DB::exists("SELECT 1 FROM f_fotos WHERE (f_date > :f_date AND f_user = :uid) OR (f_url = :url OR (f_title = :title AND f_date > :f_date_extended AND f_user = :uid)) LIMIT 1",  [
				'title' => $data['title'],
				'url' => $data['url'],
				'uid' => $this->User->uid,
				'f_date' => $f_date,
				'f_date_extended' => $f_date * 12
			]
		);

		return $exists ? "Espera {$antiflood} segundos para continuar." : null;
	}

	/**
	 * Sube la foto usando el sistema de upload.php
	 */
	private function uploadPhoto(array $data): array {
		$isFile = (int)$this->Core->settings['c_allow_upload'] === 1;
		$hasFile = !empty($data['file']) && !empty($data['file']['name']);
		$hasUrl = !empty($data['url']);
		// Validaciones
		if ($isFile && !$hasFile && !$hasUrl) {
			return ['status' => 0, 'msg' => 'No has seleccionado ningún archivo.'];
		}
		if (!$isFile && !$hasUrl) {
			return ['status' => 0, 'msg' => 'No has ingresado ninguna URL.'];
		}
		// Anti-flood del core
		$this->Core->antiFlood(true, 'foto', 'Para el carro, chacho...');
		try {
			if ($isFile && $hasFile) {
				require_once TS_EXTRAS . '/upload.php';
				// Subir desde archivo (asi reusamos upload.php)
				$_FILES['img'] = $_FILES['file'];
				// Validar archivo
				validateUploadedFile($_FILES['img']);
				// Obtener proveedor y subir
				$provider = resolveProvider();
				$imageUrl = $provider->upload($_FILES['img']['tmp_name']);
				return ['status' => 1, 'msg' => 'OK', 'url' => $imageUrl];
			} elseif ($hasUrl) {
				// Usar URL proporcionada
				// Validar que sea una URL válida
				if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
					return ['status' => 0, 'msg' => 'La URL proporcionada no es válida.'];
				}
				return ['status' => 1, 'msg' => 'OK', 'url' => $data['url']];
			}
			return ['status' => 0, 'msg' => 'No se pudo procesar la imagen.'];
		} catch (RuntimeException $e) {
			return ['status' => 0, 'msg' => $e->getMessage()];
		}
	}

	/**
	 * Crear nueva foto
	 */
	public function newFoto(): string|int {
		global $tsMonitor, $tsActividad;

		// Verificar permisos
		if (!$this->isAdminBan()) {
			return 'No tienes permiso para continuar.';
		}

		// Recolectar datos
		$data = $this->collectPostData();

		// Validar título
		if (empty($data['title'])) {
			return 'La foto no tiene título.';
		}

		// Anti-flood y duplicados
		$floodError = $this->validateAntiFlood($data);
		if ($floodError !== null) {
			return $floodError;
		}

		// Subir foto
		$uploadResult = $this->uploadPhoto($data);

		if ($uploadResult['status'] === 0) {
			return $uploadResult['msg'];
		}

		$imgUrl = $uploadResult['url'];

		if (empty($imgUrl)) {
			return 'Lo sentimos, ocurrió un error al procesar la imagen.';
		}

		try {
			// Iniciar transacción
			DB::begin();

			// Marcar la última foto como no última
			DB::query("UPDATE f_fotos SET f_last = 0 WHERE f_user = :uid AND f_last = 1", ['uid' => $this->User->uid]);

			// Insertar nueva foto
			$fotoId = DB::insert('f_fotos', [
				'f_title' => $data['title'],
				'f_date' => time(),
				'f_description' => $data['description'],
				'f_url' => $imgUrl,
				'f_user' => $this->User->uid,
				'f_closed' => $data['closed'],
				'f_visitas' => $data['visitas'],
				'f_last' => 1,
				'f_ip' => $data['ip']
			]);

			if (!$fotoId) {
				throw new RuntimeException('Error al insertar la foto en la base de datos.');
			}
			// Actualizar estadísticas
			DB::increment('w_stats', 'stats_fotos', 'stats_no = :stats_no', ['stats_no' => 1]);
			// Notificar a seguidores
			$tsMonitor->setFollowNotificacion(10, 1, $this->User->uid, $fotoId);
			// Registrar actividad
			$tsActividad->setActividad(9, $fotoId);
			// Confirmar transacción
			DB::commit();
			return (int)$fotoId;
		} catch (Exception $e) {
			// Revertir cambios en caso de error
			DB::rollback();
			return 'Error al guardar la foto: ' . $e->getMessage();
		}
	}

	/*
		getFotoEdit()
	*/
	public function getFotoEdit(): array|string {
		$fid = (int)$this->Core->setSecure($_GET['id']);

		// Obtener datos de la foto
		$data = DB::fetch("SELECT * FROM f_fotos WHERE foto_id = :fid LIMIT 1", ['fid' => $fid]);

		if (empty($data)) {
			return 'La foto que intentas editar no existe.';
		}

		// Verificar permisos
		if ($data['f_user'] != $this->User->uid && !$this->User->is_admod && !$this->User->permiso('moderacion.fotos.editar')) {
			return 'La foto que intentas editar no es tuya.';
		}

		return $data;
	}

	/*
		editFoto()
	*/
	public function editFoto(): string {
		global $tsMonitor;

		$fid = (int)$_GET['id'];

		// Obtener datos de la foto
		$data = DB::fetch("SELECT f.foto_id, f.f_title, f.f_user, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = :fid LIMIT 1", ['fid' => $fid]);

		if (empty($data)) {
			return 'La foto no existe.';
		}

		// Verificar permisos
		if ($data['f_user'] != $this->User->uid && !$this->User->is_admod && !$this->User->permiso('moderacion.fotos.editar')) {
			return 'No tienes permiso para editar esta foto.';
		}

		$fData = [
			'titulo' => $this->Core->setSecure($this->Core->parseBadWords($_POST['titulo']), true),
			'desc' => $this->Core->setSecure($this->Core->parseBadWords(substr($_POST['desc'], 0, 1500)), true),
			'privada' => empty($_POST['privada']) ? 0 : 1,
			'closed' => empty($_POST['closed']) ? 0 : 1,
			'visitas' => empty($_POST['visitas']) ? 0 : 1,
			'razon' => empty($_POST['razon']) ? 'undefined' : $this->Core->setSecure($_POST['razon'], true),
		];

		// Actualizar foto
		$affected = DB::update(
			'f_fotos',
			[
				'f_title' => $fData['titulo'],
				'f_description' => $fData['desc'],
				'f_closed' => $fData['closed'],
				'f_visitas' => $fData['visitas']
			],
			'foto_id = :fid',
			['fid' => $fid]
		);

		// Si es un moderador editando la foto de otro usuario
		if ($this->User->is_admod && $data['f_user'] != $this->User->uid && $fData['razon'] != 'undefined') {
			// Notificar al usuario
			$razon = "{$this->User->nick} ha editado tu foto, razón: {$fData['razon']}";
			$tsMonitor->setAviso($data['f_user'], 'fotos', $razon, 2);
		}

		return $affected > 0 ? '1: Foto editada correctamente' : '0: No se realizaron cambios';
	}

	/*
		delFoto()
	*/
	public function delFoto(): string {
		$fid = (int)$this->Core->setSecure($_POST['fid']);

		// Obtener datos de la foto
		$data = DB::fetch("SELECT foto_id, f_user FROM f_fotos WHERE foto_id = :fid LIMIT 1", ['fid' => $fid]);

		if (empty($data)) {
			return '0: La foto no existe.';
		}

		// Verificar permisos
		if ($data['f_user'] != $this->User->uid && !$this->User->is_admod && !$this->User->permiso('moderacion.fotos.eliminar')) {
			return '0: No tienes permiso para eliminar esta foto.';
		}

		try {
			DB::begin();
			// Eliminar foto
			DB::delete('f_fotos', 'foto_id = :fid', ['fid' => $fid]);
			// Eliminar comentarios
			DB::delete('f_comentarios', 'c_foto_id = :fid', ['fid' => $fid]);
			// Eliminar votos
			DB::delete('f_votos', 'v_foto_id = :fid', ['fid' => $fid]);
			// Actualizar estadísticas
			DB::decrement('w_stats', 'stats_fotos', 'stats_no = :stats_no', ['stats_no' => 1]);
			DB::commit();
			return '1: Foto eliminada correctamente';
		} catch (Exception $e) {
			DB::rollback();
			return '0: Error al eliminar la foto';
		}
	}

	/*
		getFoto() - Obtener datos de una foto
	*/
	public function getFoto__() {
        //
        $fid = (int)($_GET['fid'] ?? 0);
        $isAdmod = $this->User->is_admod || $this->User->permiso('moderacion.panel.acceso') ? '' : "AND f.f_status = 0 AND u.user_activo = 1";
        // MORE FOTOS
        $data = DB::fetch("SELECT f.*, u.user_name, u.user_activo, p.user_pais, p.user_sexo, u.user_rango, r.r_name, r.r_color, r.r_image FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN u_perfil AS p ON p.user_id = u.user_id LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE f.foto_id = :fid $isAdmod LIMIT 1", ['fid' => $fid]);
        // Parseamos la descripcion
        $data['f_description'] = $this->Core->parseBBCode($data['f_description']);
        // País
        $tsPaises = require_once TS_EXTRAS . '/Paises.php';
        $pais = empty($data['user_pais']) ? 'XX' : $data['user_pais'];
        $data['user_pais'] = [$pais, $tsPaises[$pais]];
        // Obtener comentarios
        $data['user_foto_comments'] = DB::value("SELECT COUNT(cid) FROM f_comentarios WHERE c_user = :user", ['user' => $data['f_user']]);
        // Obtener fotos
        $data['user_fotos'] = DB::value("SELECT COUNT(foto_id) FROM f_fotos WHERE f_user = :user AND f_status = 0", ['user' => $data['f_user']]);
        // Existe la foto?
        $data['exist'] = count($data) >= 1;
        // FOLLOW
        $data['follow'] = DB::numRows("SELECT `follow_id` FROM `u_follows` WHERE f_user = :uid AND f_id = :fid AND f_type = 1 LIMIT 1", ['uid' => $this->User->uid, 'fid' => $data['f_user']]);

        // SEGUIDORES
        $data['amigos'] = DB::fetchAll("SELECT f.f_id, p.foto_id, p.f_title, p.f_url, u.user_name FROM u_follows AS f LEFT JOIN f_fotos AS p ON f.f_id = p.f_user LEFT JOIN u_miembros AS u ON p.f_user = u.user_id WHERE f.f_user = :fuser AND f.f_type = 1 AND p.f_last = 1 LIMIT 5", ['fuser' => $data['f_user']]);

		$data['last'] = $this->lastPhotosUser((int)$data['foto']['f_user']);
		#$data['amigos'] = $this->userFollows((int)$data['foto']['f_user']);

        // COMENTARIOS
        $isAdmod = $this->User->is_admod && (int)$this->Core->settings['c_see_mod'] === 1 ? '' : "AND u.user_activo = 1 AND u.user_baneado = 0";
        $comments = DB::fetchAll("SELECT c.*, u.user_name, u.user_activo FROM f_comentarios AS c LEFT JOIN u_miembros AS u ON c.c_user = u.user_id WHERE c.c_foto_id = :foto_id $isAdmod", ['foto_id' => $fid]);
        foreach($comments as $key => $val){
            $val['c_body'] = $this->Core->parseBadWords($this->Core->parseBBCode($val['c_body']), true);
            $data['comments'][] = $val;
        }
        $data['foto']['f_comments'] = count($comments);

        // MEDALLAS
        $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = \''.(int)$fid.'\' AND m.m_type = \'3\' ORDER BY a.medal_date DESC LIMIT 10');
        $data['medallas'] = result_array($query);
        $data['m_total'] = count($data['medallas']);

        //VISITANTES RECIENTES
        $data['visitas'] = 0;
		if ($data['foto']['f_visitas']) {
			$data['visitas'] = DB::fetchAll("SELECT v.*, u.user_id, u.user_name FROM w_visitas AS v LEFT JOIN u_miembros AS u ON v.user = u.user_id WHERE v.for = :fid AND v.type = 3 AND v.user > 0 ORDER BY v.date DESC LIMIT 15", ['fid' => $fid]);
		}
        // Registrar visita
		$this->registrarVisita($fid);
        // Dar medallas si corresponde
		$this->DarMedalla($fid);
        //
        return $data;
    }

	public function __getFoto(): array|string {
		$fid = (int)$_GET['fid'];

		// Obtener datos de la foto con información del usuario
		$data['foto'] = DB::fetch("SELECT f.*, u.user_id, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = :fid LIMIT 1", ['fid' => $fid]);
		$data['foto']['exist'] = 1;
		if (empty($data['foto'])) {
			$data['foto']['exist'] = 0;
			return 'La foto no existe o fue eliminada.';
		}

		// Obtener comentarios
		$data['comentarios'] = DB::fetchAll("SELECT c.*, u.user_id, u.user_name FROM f_comentarios AS c LEFT JOIN u_miembros AS u ON c.c_user = u.user_id WHERE c.c_foto_id = :fid ORDER BY c.cid ASC", ['fid' => $fid]);

		// Obtener medallas
		$data['medallas'] = DB::fetchAll("SELECT m.*, ma.medal_date FROM w_medallas AS m LEFT JOIN w_medallas_assign AS ma ON m.medal_id = ma.medal_id WHERE ma.medal_for = :fid AND m.m_type = 3 ORDER BY ma.medal_date DESC", ['fid' => $fid]);

		$data['m_total'] = count($data['medallas']);

		// Visitantes recientes

		$data['last'] = $this->lastPhotosUser((int)$data['foto']['f_user']);
		$data['amigos'] = $this->userFollows((int)$data['foto']['f_user']);

		// Registrar visita
		$this->registrarVisita($fid);

		// Dar medallas si corresponde
		$this->DarMedalla($fid);


		return $data;
	}

	private function lastPhotosUser(int $fuser = 0): array {
		// ULTIMAS FOTOS
        $query = "SELECT f.foto_id, f.f_title, f.f_date, f.f_status, f.f_url, u.user_name, u.user_activo FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user WHERE f.f_user = :fuser";
		if($this->User->is_admod && (int)$this->Core->settings['c_see_mod'] === 1) {
			$query .= " AND f.f_status = 0 AND u.user_activo = 1 AND u.user_baneado = 0";
		}
		$query .= " ORDER BY f.foto_id DESC LIMIT 5";
        return DB::fetchAll($query, ['fuser' => $fuser]);
	}

	private function userFollows(int $fuser = 0): array {
        return DB::fetchAll('SELECT f.f_id, p.foto_id, p.f_title, p.f_url, u.user_name FROM u_follows AS f LEFT JOIN f_fotos AS p ON f.f_id = p.f_user LEFT JOIN u_miembros AS u ON p.f_user = u.user_id WHERE f.f_user = :fuser AND f.f_type = 1 AND p.f_last = 1 LIMIT 5', ['fuser' => $fuser]);
	}

	/**
	 * Registrar visita a la foto
	 */
	private function registrarVisita(int $fid): void {
		$whereCondition = $this->User->is_member ? "(user = :uid OR ip = :ip)" : "ip = :ip";

		$params = ['fid' => $fid, 'ip' => $this->myIP];
		if ($this->User->is_member) {
			$params['uid'] = $this->User->uid;
		}

		$visitado = DB::exists("SELECT 1 FROM w_visitas WHERE `for` = :fid AND type = '3' AND {$whereCondition} LIMIT 1", $params);

		if ($this->User->is_member && !$visitado) {
			// Insertar nueva visita (evitamos problemas con palabras reservadas)
			DB::query("INSERT INTO w_visitas (`user`, `for`, `type`, `date`, `ip`) VALUES (:user, :for, :type, :date, :ip)", [
				'user' => $this->User->uid,
				'for' => $fid,
				'type' => 3,
				'date' => time(),
				'ip' => $this->myIP
			]);

			// Incrementar contador solo si no es el dueño
			DB::query("UPDATE f_fotos SET f_hits = f_hits + 1 WHERE foto_id = :fid AND f_user != :uid", ['fid' => $fid, 'uid' => $this->User->uid]);
		} elseif ($visitado) {
			// Actualizar fecha de visita existente
			DB::update('w_visitas', ['date' => time(), 'ip' => $this->myIP], "`for` = :fid AND `type` = 3", ['fid' => $fid]
			);
		}

		// Visitas de invitados
		if ((int)$this->Core->settings['c_hits_guest'] === 1 && !$this->User->is_member && !$visitado) {
			DB::insert('w_visitas', [
				'user' => 0,
				'for' => $fid,
				'type' => 3,
				'date' => time(),
				'ip' => $this->myIP
			]);

			DB::increment('f_fotos', 'f_hits', 'foto_id = :fid', ['fid' => $fid]);
		}
	}

	/*
		DarMedalla()
	*/
	public function DarMedalla(int $fid): void {
		// Obtener datos de la foto
		$data = DB::fetch(
			"SELECT f.foto_id, f.f_user, f.f_hits, v.v_pos, v.v_neg
			FROM f_fotos AS f LEFT JOIN f_votos AS v ON v.v_foto_id = f.foto_id
			WHERE f.foto_id = :fid LIMIT 1",
			['fid' => $fid]
		);

		if (empty($data)) {
			return;
		}

		// Contar comentarios
		$totalComments = DB::value(
			"SELECT COUNT(cid) FROM f_comentarios WHERE c_foto_id = :fid",
			['fid' => $fid]
		) ?? 0;

		// Contar medallas actuales
		$totalMedals = DB::value(
			"SELECT COUNT(wm.medal_id)
			FROM w_medallas AS wm
			LEFT JOIN w_medallas_assign AS wma ON wm.medal_id = wma.medal_id
			WHERE wm.m_type = '3' AND wma.medal_for = :fid",
			['fid' => $fid]
		) ?? 0;

		// Obtener medallas disponibles
		$medallas = DB::fetchAll(
			"SELECT * FROM w_medallas WHERE m_type = '3' ORDER BY m_cant DESC"
		);

		foreach ($medallas as $medalla) {
			$newmedalla = null;

			// Verificar condiciones para otorgar medalla
			if ($medalla['m_cond_foto'] == 1 && !empty($data['f_votos_pos']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $data['f_votos_pos']) {
				$newmedalla = $medalla['medal_id'];
			} elseif ($medalla['m_cond_foto'] == 2 && !empty($data['f_votos_neg']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $data['f_votos_neg']) {
				$newmedalla = $medalla['medal_id'];
			} elseif ($medalla['m_cond_foto'] == 3 && $totalComments > 0 && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $totalComments) {
				$newmedalla = $medalla['medal_id'];
			} elseif ($medalla['m_cond_foto'] == 4 && !empty($data['f_hits']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $data['f_hits']) {
				$newmedalla = $medalla['medal_id'];
			} elseif ($medalla['m_cond_foto'] == 5 && $totalMedals > 0 && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $totalMedals) {
				$newmedalla = $medalla['medal_id'];
			}

			// Si hay nueva medalla, verificar que no exista ya
			if ($newmedalla !== null) {
				$exists = DB::exists(
					"SELECT 1 FROM w_medallas_assign WHERE medal_id = :medal_id AND medal_for = :fid",
					['medal_id' => $newmedalla, 'fid' => $fid]
				);

				if (!$exists) {
					// Asignar medalla
					DB::insert('w_medallas_assign', [
						'medal_id' => $newmedalla,
						'medal_for' => $fid,
						'medal_date' => time(),
						'medal_ip' => $this->myIP
					]);

					// Notificar al usuario
					DB::insert('u_monitor', [
						'user_id' => $data['f_user'],
						'obj_uno' => $newmedalla,
						'obj_dos' => $fid,
						'not_type' => 17,
						'not_date' => time()
					]);

					// Actualizar contador de medallas
					DB::increment('w_medallas', 'm_total', 'medal_id = :medal_id', ['medal_id' => $newmedalla]);
				}
			}
		}
	}

	/*
		votarFoto()
	*/
	public function votarFoto(): string {
		// Solo miembros pueden votar
		if (!$this->User->is_member) {
			return '0: Lo sentimos, para poder votar debes estar registrado.';
		}

		$fid = (int)$this->Core->setSecure($_POST['fotoid']);
		$voto = $this->Core->setSecure($_POST['voto']);

		// Determinar tipo de voto
		$votoColumn = ($voto === 'pos') ? 'f_votos_pos' : 'f_votos_neg';
		$type = ($voto === 'pos') ? 0 : 1;

		// Obtener datos de la foto
		$data = DB::fetch(
			"SELECT f_user FROM f_fotos WHERE foto_id = :fid LIMIT 1",
			['fid' => $fid]
		);

		if (empty($data)) {
			return '0: La foto no existe.';
		}

		// No se puede votar la propia foto
		if ($data['f_user'] == $this->User->uid) {
			return '0: No puedes votar tu propia foto.';
		}

		// Verificar si ya votó
		$votado = DB::exists(
			"SELECT 1 FROM f_votos WHERE v_foto_id = :fid AND v_user = :uid LIMIT 1",
			['fid' => $fid, 'uid' => $this->User->uid]
		);

		if ($votado) {
			return '0: Ya has votado esta foto.';
		}

		// Registrar voto
		DB::increment('f_fotos', $votoColumn, 'foto_id = :fid', ['fid' => $fid]);

		DB::insert('f_votos', [
			'v_foto_id' => $fid,
			'v_user' => $this->User->uid,
			'v_type' => $type,
			'v_date' => time()
		]);

		return '1: Votado';
	}

	/************ COMENTARIOS *******************/

	/*
		newComentario()
	*/
	public function newComentario(): string|array {
		global $tsMonitor;

		if (!$this->User->is_member || (int)$this->User->info['user_baneado'] !== 0 || (int)$this->User->info['user_activo'] !== 1 || (!$this->User->is_admod && !$this->User->permiso('global.fotos.comentar'))) {
			return '0: Necesitas permisos para continuar.';
		}

		// Obtener datos
		$comentario = $this->Core->setSecure(substr($_POST['comentario'], 0, 1500), true);
		$fid = (int)$_POST['fotoid'];

		// Validar comentario
		$tsText = preg_replace('# +#', "", $comentario);
		$tsText = str_replace(["\n", "\t"], "", $tsText);

		if ($tsText === '') {
			return '0: El campo <b>Mensaje</b> es requerido para esta operación';
		}

		// Obtener datos de la foto
		$data = DB::fetch(
			"SELECT f_user, f_closed FROM f_fotos WHERE foto_id = :fid LIMIT 1",
			['fid' => $fid]
		);

		if (empty($data)) {
			return '0: La foto no existe.';
		}

		// Verificar si está cerrada
		if ((int)$data['f_closed'] === 1 && $data['f_user'] != $this->User->uid) {
			return '0: La foto se encuentra cerrada y no se permiten comentarios.';
		}

		// Anti-flood
		$this->Core->antiFlood();

		// Insertar comentario
		$fecha = time();

		$cid = DB::insert('f_comentarios', [
			'c_foto_id' => $fid,
			'c_user' => $this->User->uid,
			'c_date' => $fecha,
			'c_body' => $comentario,
			'c_ip' => $this->myIP
		]);

		if (!$cid) {
			return '0: Ocurrió un error, inténtalo más tarde.';
		}

		// Actualizar estadísticas
		DB::increment('w_stats', 'stats_foto_comments', 'stats_no = :stats_no', ['stats_no' => 1]);

		// Notificar al dueño de la foto
		$tsMonitor->setNotificacion(11, $data['f_user'], $this->User->uid, $fid);

		// Retornar datos del comentario
		return [
			$cid,
			$this->Core->parseBadWords($this->Core->parseBBCode($comentario), true),
			$fecha,
			$_POST['auser'] ?? ''
		];
	}

	/*
		delComentario()
	*/
	public function delComentario(): string {
		$cid = (int)$this->Core->setSecure($_POST['cid']);

		// Obtener datos del comentario
		$data = DB::fetch(
			"SELECT c.cid, c.c_user, f.foto_id, f.f_user
			FROM f_comentarios AS c
			LEFT JOIN f_fotos AS f ON c.c_foto_id = f.foto_id
			WHERE c.cid = :cid LIMIT 1",
			['cid' => $cid]
		);

		if (empty($data)) {
			return '0: El comentario no existe.';
		}

		// Verificar permisos
		if ($data['f_user'] != $this->User->uid && !$this->User->is_admod && !$this->User->permiso('moderacion.fotos.eliminar_comentarios')) {
			return '0: Hmmm... ¿Haciendo pruebas?';
		}

		// Eliminar comentario
		$deleted = DB::delete('f_comentarios', 'cid = :cid', ['cid' => $cid]);

		if ($deleted > 0) {
			// Actualizar estadísticas
			DB::decrement('w_stats', 'stats_foto_comments', 'stats_no = :stats_no', ['stats_no' => 1]);
			return '1: Borrado';
		}

		return '0: Error al eliminar el comentario';
	}

	function getLastFotos(){
		//
		$max = 10; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		// PAGINAS
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(f.foto_id) FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user '.($this->User->is_admod && $this->Core->settings['c_see_mod'] == 1 ? '' : 'WHERE f.f_status = \'0\' AND u.user_activo = \'1\' && u.user_baneado = \'0\''));
		list ($total) = db_exec('fetch_row', $query);

		$data['pages'] = $this->Paginator->pageIndex($this->Core->settings['url']."/fotos/?",(int)($_GET['s'] ?? 0),(int)$total,(int)$max);
		//
		$query = 'SELECT f.foto_id, f.f_title, f.f_date, f.f_description, f.f_url, f.f_status, u.user_name, u.user_activo, u.user_baneado FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user '.($this->User->is_admod && $this->Core->settings['c_see_mod'] == 1 ? '' : 'WHERE f.f_status = \'0\' AND u.user_activo = \'1\' && u.user_baneado = \'0\'').' ORDER BY f.foto_id DESC LIMIT '.$limit;
		$data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', $query));


		//
		return $data;
	}

	function getLastComments(){
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT c.cid, c.c_user, f.foto_id, f.f_title, f.f_status, u.user_name, u.user_activo FROM f_comentarios AS c LEFT JOIN f_fotos AS f ON c.c_foto_id = f.foto_id LEFT JOIN u_miembros AS u ON f.f_user = u.user_id '.($this->User->is_admod && $this->Core->settings['c_see_mod'] == 1 ? '' : 'WHERE f.f_status = \'0\' && u.user_activo = \'1\' && u.user_baneado = \'0\'').' ORDER BY c.c_date DESC LIMIT 10');
		$data = result_array($query);

		//
		return $data;
	}
}
