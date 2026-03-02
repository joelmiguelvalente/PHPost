<?php

/**
 * @name c.estadisticas.php
 * @author PHPost Team
 * @copyright 2026
 * > Si, tengo que mejorar estos bloques
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsEstadisticas {

	public function __construct() {
	}

	private function getStats(string $type, string $table, string $idField, string $statusField, array $states): array {
		$sqlParts = [];
		foreach ($states as $alias => $value) {
			// COALESCE = https://www.w3schools.com/sql/func_mysql_coalesce.asp
			// CASE     = https://www.w3schools.com/sql/func_mysql_case.asp
			$sqlParts[] = "COALESCE(
				SUM(CASE WHEN $statusField = '$value' THEN 1 ELSE 0 END), 0
			) AS $alias";
		}
		$sql = "SELECT " . implode(',', $sqlParts) . "  FROM $table";
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', $sql));
		$data['total'] = array_sum($data);
		return [$type => array_map('intval', $data)];
	}

	private function getStatSingle(string $type, string $table, string $idField, string $who) {
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 
			"SELECT COALESCE(SUM($idField), 0) AS total FROM $table"
		));
		return array_map('intval', $data)['total'];
	}

	private function estadisticasPosts() {
		$data = $this->getStats('posts', 'p_posts', 'post_id', 'post_status', [
			'visibles' => 0,
			'ocultos' => 1,
			'eliminados' => 2,
			'revision' => 3
		]);
		$data['posts']['favoritos'] = $this->getStatSingle('post', 'p_favoritos', 'fav_id', 'favoritos');
		$borradores = $this->getStats('posts', 'p_posts', 'post_id', 'post_draft', [
			'visibles' => 1
		]);
		$data['posts']['borradores'] = $borradores['posts']['visibles'];
		$data['posts']['compartidos'] = (int)db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT COALESCE(SUM(follow_id), 0) AS total FROM u_follows WHERE f_type = 3"))['total'];

		return $data + $borradores;
	}

	private function estadisticasFotos() {
		$data = $this->getStats('fotos', 'f_fotos', 'foto_id', 'f_status', [
			'visibles' => 0,
			'ocultas' => 1,
			'eliminadas' => 2
		]);
		$data['fotos']['comentarios'] = $this->getStatSingle('fotos', 'f_comentarios', 'cid', 'comentarios');
		return $data;
	}

	private function estadisticasComentarios() {
		return $this->getStats('comentarios', 'p_comentarios', 'cid', 'c_status', [
			'visibles' => 0,
			'ocultos' => 1
		]);
	}

	private function estadisticasUsuarios() {
		$activo = $this->getStats('usuarios', 'u_miembros', 'user_id', 'user_activo', [
			'inactivos' => 0,
			'activos' => 1
		]);
		$activo['usuarios']['baneados'] = (int)db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT COALESCE(SUM(user_id), 0) AS total FROM u_miembros WHERE user_baneado = 1"))['total'];

		$activo['usuarios']['bloqueados'] = $this->getStatSingle('usuarios', 'u_bloqueos', 'bid', 'usuarios_bloqueados');
		return $activo;
	}

	private function estadisticasSeguimientos() {		
		return $this->getStats('seguimientos', 'u_follows', 'follow_id', 'f_type', [
			'usuarios' => 1,
			'posts' => 2
		]);
	}

	private function estadisticasMensajes() {
		$data['mensajes']['de_eliminados'] = (int)db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 
			"SELECT COALESCE(SUM(mp_id), 0) AS total FROM u_mensajes WHERE mp_del_to = 1"
		))['total'];
		$data['mensajes']['para_eliminados'] = (int)db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 
			"SELECT COALESCE(SUM(mp_id), 0) AS total FROM u_mensajes WHERE mp_del_from = 1"
		))['total'];

		$data['mensajes']['respuestas'] = $this->getStatSingle('mensajes', 'u_respuestas', 'mr_id', 'respuestas');
		$data['mensajes']['total'] = array_sum($data);
		return $data;
	}

	private function estadisticasMedallas() {		
		$data = $this->getStats('medallas', 'w_medallas', 'medal_id', 'm_type', [
			'usuarios' => 1,
			'posts' => 2,
			'fotos' => 3
		]);
		$data['medallas']['asignadas'] = $this->getStatSingle('mensajes', 'w_medallas_assign', 'id', 'asignadas');
		return $data;
	}

	private function estadisticasAfiliados() {		
		return $this->getStats('afiliados', 'w_afiliados', 'aid', 'a_active', [
			'inactivos' => 0,
			'activos' => 1
		]);
	}

	private function estadisticasMuro() {
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

	public function obtenerEstadisticas() {
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