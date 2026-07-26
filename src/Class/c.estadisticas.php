<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsEstadisticas {

	public function __construct() {
	}

	private function getStats(string $type, string $table, string $idField, string $statusField, array $states): array {
		$sqlParts = [];
		foreach ($states as $alias => $value) {
			// COALESCE = https://www.w3schools.com/sql/func_mysql_coalesce.asp
			// CASE     = https://www.w3schools.com/sql/func_mysql_case.asp
			$sqlParts[] = "COALESCE(SUM(CASE WHEN $statusField = '$value' THEN 1 ELSE 0 END), 0) AS $alias";
		}
		$sql = "SELECT " . implode(',', $sqlParts) . "  FROM $table";
		$data = DB::fetch($sql);
		$data['total'] = array_sum($data);
		return [$type => array_map('intval', $data)];
	}

	private function getStatSingle(string $type, string $table, string $idField, string $who): int {
		$data = DB::fetch("SELECT COALESCE(SUM($idField), 0) AS total FROM $table");
		return array_map('intval', $data)['total'];
	}

	private function estadisticasPosts(): array {
		$data = $this->getStats('posts', 'p_posts', 'post_id', 'post_status', [
			'visibles' => 'visibles',
			'ocultos' => 'ocultos',
			'eliminados' => 'eliminados',
			'revision' => 'revision',
			'borrador' => 'borrador'
		]);
		$data['posts']['favoritos'] = $this->getStatSingle('post', 'p_favoritos', 'fav_id', 'favoritos');

		$data['posts']['borradores'] = $data['posts']['borrador'];
		$data['posts']['compartidos'] = (int)DB::value("SELECT COALESCE(SUM(follow_id), 0) FROM u_follows WHERE f_type = :type", ['type' => 3]);

		return $data;
	}

	private function estadisticasFotos(): array {
		$data = $this->getStats('fotos', 'f_fotos', 'foto_id', 'f_status', [
			'visibles' => 0,
			'ocultas' => 1,
			'eliminadas' => 2
		]);
		$data['fotos']['comentarios'] = $this->getStatSingle('fotos', 'f_comentarios', 'cid', 'comentarios');
		return $data;
	}

	private function estadisticasComentarios(): array {
		return $this->getStats('comentarios', 'p_comentarios', 'cid', 'c_status', [
			'visibles' => 0,
			'ocultos' => 1
		]);
	}

	private function estadisticasUsuarios(): array {
		$activo = $this->getStats('usuarios', 'u_miembros', 'user_id', 'user_activo', [
			'inactivos' => 0,
			'activos' => 1
		]);
		$activo['usuarios']['baneados'] = (int)DB::fetch("SELECT COALESCE(SUM(user_id), 0) AS total FROM u_miembros WHERE user_baneado = 1")['total'];

		$activo['usuarios']['bloqueados'] = $this->getStatSingle('usuarios', 'u_bloqueos', 'bid', 'usuarios_bloqueados');
		return $activo;
	}

	private function estadisticasSeguimientos(): array {
		return $this->getStats('seguimientos', 'u_follows', 'follow_id', 'f_type', [
			'usuarios' => 1,
			'posts' => 2
		]);
	}

	private function estadisticasMensajes(): array {
		$data['mensajes']['de_eliminados'] = (int)DB::value("SELECT COALESCE(SUM(mp_id), 0) FROM u_mensajes WHERE mp_del_to = :del", ['del' => 1]);
		$data['mensajes']['para_eliminados'] = (int)DB::value("SELECT COALESCE(SUM(mp_id), 0) FROM u_mensajes WHERE mp_del_from = :del", ['del' => 1]);

		$data['mensajes']['respuestas'] = $this->getStatSingle('mensajes', 'u_respuestas', 'mr_id', 'respuestas');
		$data['mensajes']['total'] = array_sum($data);
		return $data;
	}

	private function estadisticasMedallas(): array {
		$data = $this->getStats('medallas', 'w_medallas', 'medal_id', 'm_type', [
			'usuarios' => 1,
			'posts' => 2,
			'fotos' => 3
		]);
		$data['medallas']['asignadas'] = $this->getStatSingle('mensajes', 'w_medallas_assign', 'id', 'asignadas');
		return $data;
	}

	private function estadisticasAfiliados(): array {
		return $this->getStats('afiliados', 'w_afiliados', 'aid', 'a_active', [
			'inactivos' => 0,
			'activos' => 1
		]);
	}

	private function estadisticasMuro(): array {
		$estados = $this->getStatSingle('muro', 'u_muro', 'pub_id', 'estados');
		$comentarios = $this->getStatSingle('muro', 'u_muro_comentarios', 'cid', 'comentarios');
		return [
			'muro' => [
				'total' => $estados + $comentarios,
				'estados' => $estados,
				'comentarios' => $comentarios
			]
		];
	}

	public function obtenerEstadisticas(): array {
		$data = array_merge(
			$this->estadisticasPosts(),
			$this->estadisticasFotos(),
			$this->estadisticasComentarios(),
			$this->estadisticasUsuarios(),
			$this->estadisticasSeguimientos(),
			$this->estadisticasMensajes(),
			$this->estadisticasMedallas(),
			$this->estadisticasAfiliados(),
			$this->estadisticasMuro()
		);
		return $data;
	 }
}
