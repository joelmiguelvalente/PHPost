<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class tsRangos {

	private const COLUMNS = "rango_id, r_allows, r_cant, r_color, r_image, r_name, r_type";

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected AdminHelper $AdminHelper,
		protected Paginator $Paginator
	) {
	}

	private function getRangoID(string $key = 'rid'): int {
		if($key === 'rid') {
			return (int)($_GET[$key] ?? 0);
		}
		return (int)($_POST[$key] ?? 0);
	}

	public function getRangos(): array {
		$data = [];
		$columns = self::COLUMNS;
		$rangos = DB::fetchAll("SELECT $columns FROM u_rangos ORDER BY rango_id, r_cant");
		// ARMAR ARRAY
		foreach($rangos as $row) {
			$stored = json_decode($row['r_allows'], true);
			$extra = array_merge(Permissions::definitions(), is_array($stored) ? $stored : []);
			$key = $row['r_type'] === 0 ? 'regular' : 'post';
			$data[$key][$row['rango_id']] = [
				'id' => $row['rango_id'],
				'name' => $row['r_name'],
				'color' => $row['r_color'],
				'imagen' => $row['r_image'],
				'cant' => $row['r_cant'],
				'max_points' => $extra['gopfp'],
				'user_puntos' => $extra['gopfd'],
				'type' => $row['r_type'],
				'num_members' => 0
			];
		}
		$usersRanks = ['post', 'regular'];
		foreach(['post', 'regular'] as $urank) {
			if (empty($data[$urank])) continue;
			$in = implode(', ', array_keys($data[$urank]));
			$rows = DB::fetchAll("SELECT user_rango AS ID_GROUP, COUNT(*) AS num_members FROM u_miembros WHERE user_rango IN ($in) GROUP BY user_rango");
			foreach ($rows as $row) {
				$data[$urank][$row['ID_GROUP']]['num_members'] += $row['num_members'];
			}
		}
		//
		return $data;
	}

	private function decodePermisos(string $json): array {
		$stored = json_decode($json, true);
		$result = [];
		$walk = function(array $tree, string $prefix = '') use (&$walk, &$result, $stored) {
			foreach ($tree as $key => $node) {
				$path = $prefix ? "$prefix.$key" : $key;
				if (isset($node['code'])) {
					$result[$path] = $stored[$node['code']] ?? ($node['type'] === 'bool' ? false : 0);
				} else {
					$walk($node, $path);
				}
			}
		};
		$walk(Permissions::TREE);
		return $result;
	}

	public function getRango(): array {
		$id = $this->getRangoID();
		$columns = self::COLUMNS;
		$data = DB::fetch("SELECT $columns FROM u_rangos WHERE rango_id = :rid LIMIT 1", ['rid' => $id]);
		$stored = json_decode($data['r_allows'] ?? '', true);
		$data['permisos'] = array_merge(Permissions::definitions(), is_array($stored) ? $stored : []);
		return $data;
	}

	public function getRangoUsers(): array {
		$rid = $this->getRangoID();
		$max = 12; // MAXIMO A MOSTRAR
		// TIPO DE BUSQUEDA
		$type = trim($_GET['type'] ?? 'special');
		// SELECCIONAMOS
		$limit = $this->Paginator->setPageLimit($max, true);
		$data['data'] = DB::fetchAll("SELECT u.user_id, u.user_name, u.user_email, u.user_registro, u.user_lastlogin FROM u_miembros AS u WHERE u.user_rango = :rid LIMIT $limit", [
			'rid' => $rid
		]);
		# Paginamos
		list($total) = DB::value("SELECT COUNT(*) FROM u_miembros WHERE user_rango = :rid", ['rid' => $rid]);
		$data['pages'] = $this->Paginator->pageIndex("/admin/rangos?act=list&rid=$rid&type=$type", (int)($_GET['s']??0), (int)$total, $max);
		# Retornamos
		return $data;
	}

	private function rangoFields(): array {
		$cantidad = (int)($_POST['global-cantidadrequerida'] ?? 0);
		$tipo = (int)($_POST['global-type'] ?? 0);
		$tipo = ($tipo > 4) ? 0 : $tipo;
		$image = Html::escape($_POST['r_img']);
		$rango = [
			'r_name' => Html::escape($this->Core->parseBadWords($_POST['r_name'])),
			'r_color' => ltrim(Html::escape($_POST['r_color']), '#'),
			'r_image' => $image,
			'r_cant' => (int)$cantidad,
			'r_allows' => $this->AdminHelper->optionsRange($_POST),
			'r_type' => (int)$tipo
		];
		return $rango;
	}

	private function rangoChecking(string $name): string {
		if (empty($name)) return 'Debes ingresar el nombre del nuevo rango.';
		if ($_POST['global-pointsforposts'] > $_POST['global-pointsforday']) return 'El rango no puede dar más puntos de los que tiene al día.';
		return '';
	}

	public function saveRango(): bool {
		$rid = $this->getRangoID();
		$rango = $this->rangoFields();
		$this->rangoChecking($rango['r_name']);
		if(DB::update('u_rangos', $rango, 'rango_id = :id', ['id' => $rid])) {
			return true;
		}
		return false;
	}

	public function newRango(): string|bool {
		$rango = $this->rangoFields();
		$this->rangoChecking($rango['r_name']);
		//
		if (empty($rango['r_name'])) return 'Debes ingresar el nombre del nuevo rango.';
		if ($_POST['global-pointsforposts'] > $_POST['global-pointsforday']) return 'El rango no puede dar más puntos de los que tiene al día.';
		//
		if(DB::insert('u_rangos', $rango)) return true;
	}

	public function delRango(): string|bool {
		//
		$rid = intval($_GET['rid']);
		$nid = intval($_POST['new_rango']);
		//
		if ($rid < 3) {
			return 'No es posible eliminar este rango';
		}
		if (DB::query('UPDATE u_miembros SET user_rango = :nid WHERE user_rango = :rid', ['nid' => $nid, 'rid' => $rid])) {
			if (DB::delete('u_rangos', 'rango_id = :rid', ['rid' => $rid])) return true;
		}
	}
	public function SetDefaultRango(): string|bool {
		//
		if($_SERVER['HTTP_REFERER'] == $this->Core->settings['url'].'/admin/rangos?save=true' || $_SERVER['HTTP_REFERER'] == $this->Core->settings['url'].'/admin/rangos') {
			$rid = (int)($_GET['rid'] ?? 0);
			//
			$dato = DB::fetch('SELECT rango_id, r_type FROM u_rangos WHERE rango_id = :rid LIMIT 1', ['rid' => $rid]);
			if (!empty($dato['rango_id']) && (int)$dato['r_type'] === 0) {
				if (DB::update('w_configuracion', ['c_reg_rango' => $rid], 'phppost_id = :id', ['id' => 1])) return true;
			} else return 'El rango no existe o no es posible utilizarlo';
		} else return 'Petición inválida';
	}
}
