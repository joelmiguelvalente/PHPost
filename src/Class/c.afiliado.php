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

class tsAfiliado {

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User
	) {
	}

	/**
	 * @access public
	 * @param string
	 * @return array
	*/
	public function getAfiliados(string $type = 'home'): array {
		$query = "SELECT aid, a_titulo, a_url, a_banner, a_descripcion";
		if($type === 'admin') $query .= ", a_sid, a_hits_in, a_hits_out, a_date, a_active";
		$query .= " FROM w_afiliados";
		if($type === 'home') $query .= " WHERE a_active = 1 ORDER BY RAND() LIMIT 5";
		return result_array(db_exec([__FILE__, __LINE__], 'query', $query));
	}

	/**
	 * @access public
	 * @param string
	 * @return array
	*/
	public function getAfiliado(string $type = ''): array {
		$id = ($type === 'admin') ? (int)($_GET['aid'] ?? 0) : (int)($_POST['ref'] ?? 0);
		$query = "SELECT aid, a_titulo, a_url, a_banner, a_descripcion FROM w_afiliados WHERE aid = {$id}";
		return db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', $query));
	}

	private function getData(): array {
		$data = [];
		foreach($_POST as $key => $value) {
			$value = htmlspecialchars(trim($value ?? ''));
			$data[$key] = $this->Core->setSecure($this->Core->parseBadWords($value));
		}
		return $data;
	}

	/**
	 * @access public
	 * @return array
	*/
	public function newAfiliado(): string {
		global $tsMonitor;
		$dataIn = $this->getData();
		$time = time();
		//
		$checked = $dataIn; // Evitamos modificar el array principal
		unset($checked['a_sid']); // Solo borramos el item de la copia del array
		if(in_array('', $checked, true)) {
		  return '2: Faltan datos';
		}
		if(!filter_var($dataIn['a_url'], FILTER_VALIDATE_URL)) { 
			return '0: Url incorrecta'; 
		}
		//
		if(db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_afiliados (a_titulo, a_url, a_banner, a_descripcion, a_sid, a_date) VALUES ('{$dataIn['a_titulo']}', '{$dataIn['a_url']}', '{$dataIn['a_banner']}', '{$dataIn['a_descripcion']}', '{$dataIn['a_sid']}', {$time})")) {
			$afid = (int)db_exec('insert_id');
		  	// AVISO
			$aviso = "<center>
				<a href=\"{$dataIn['a_url']}\">
					<img alt=\"banner del sitio {$dataIn['a_titulo']}\" src=\"{$dataIn['a_banner']}\" title=\"{$dataIn['a_titulo']}\"/>
				</a>
			</center>
			<br />
			<span>{$dataIn['a_titulo']} quiere ser su afiliado, dir&iacute;jase a la administraci&oacute;n para aceptar o cancelarla.</span>";
			$tsMonitor->setAviso(1,'Nueva afiliaci&oacute;n', (string)$aviso, 0);
			//
			$titleSite  = $this->Core->settings['titulo'];
			$urlSiteRef = $this->Core->settings['url'].'/?ref='.$afid;
			$bannerSite = $this->Core->settings['banner'];
			//
			return "1: <div class=\"emptyData\">Tu afiliaci&oacute;n ha sido agregada!</div><br>
			<div>Se le ha notificado al administrador tu afiliaci&oacute;n para que la apruebe, mientras tanto copia el siguiente c&oacute;digo, ser&aacute; con el cual nos debes enlazar.<br><br>
				<div class=\"form-line\">
					<label for=\"atitle\">C&oacute;digo HTML</label>
					<textarea tabindex=\"4\" style=\"border:1px solid #CCC;border-radius:.325rem;height:100px;width:100%\" onclick=\"select(this)\"><a href=\"$urlSiteRef\" target=\"_blank\" title=\"$titleSite\"><img src=\"$bannerSite\" alt=\"banner del sitio $titleSite\"></a></textarea>
				</div>
			</div>";
		}
	}
	
	/**
	 * @access public
	 * @return string
	*/
	public function editarAfiliado(): string {
		$afiliado = (int)($_GET['aid'] ?? 0);
		$newData = $this->getData();  
	   if(!$afiliado || in_array('', $newData, true)) {
		  return '0: Faltan datos';
		}
		if(!filter_var($newData['a_url'], FILTER_VALIDATE_URL)) { 
			return '0: Url incorrecta'; 
		}
		//
		$afs = $this->Core->buildSqlSet($newData);
		if(!db_exec([__FILE__, __LINE__], 'query', "UPDATE w_afiliados SET $afs WHERE aid= '$afiliado'")) {
			return '0: Ocurri&oacute; un error';
		}
		return '1: Guardado';
	}
	
	/**
	 * @access public
	 * @return string
	*/
	public function DeleteAfiliado(): string {
		$aid = (int)($_POST['afid'] ?? 0);
		if($this->User->is_admod !== 1) {
			return '0: Tu, no puedes hacer eso';
		} 
		if(!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM w_afiliados WHERE aid = $aid")) {
			return '0: No se pudo eliminar el afiliado.';
		}
		return '1: Afiliado eliminado.';
	}
	
	/**
	 * @access public
	 * @return string
	*/
	public function activeAfiliado(): string {
		$afiliado = (int)($_POST['aid'] ?? 0);
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT a_active FROM w_afiliados WHERE aid = $afiliado"));
		//
		$active = ((int)$data['a_active'] === 1) ? 0 : 1;
		if(!db_exec([__FILE__, __LINE__], 'query', "UPDATE w_afiliados SET a_active = $active WHERE aid = $afiliado")) {
			return '0: Ocurri&oacute, un error';
		}
		return ($active === 1) ? '2: Afiliado deshabilitado' : '1: Afiliado habilitado.';
	}
	
	/**
	 * @access public
	 * @return void
	*/
	public function urlOut(): void {
		$afiliado = (int)($_GET['ref'] ?? 0);
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT a_url, a_sid FROM w_afiliados WHERE aid = $afiliado LIMIT 1"));
		if(!isset($data['a_url']) || $data['a_url'] === '') {
			$this->Core->redirectTo($this->Core->settings['url']);
			exit();
		}
		db_exec([__FILE__, __LINE__], 'query', "UPDATE w_afiliados SET a_hits_out = a_hits_out + 1 WHERE aid = $afiliado");
		// Y REDIRECCIONAMOS
		$this->Core->redirectTo(
			"{$data['a_url']}/" . ($data['a_sid'] === '' ? '' : "?ref={$data['a_sid']}")
		);
		exit();
	}
	
	/**
	 * @access public
	 * @return void
	*/
	public function urlInRef(): void {
		$afiliado = (int)($_GET['ref'] ?? 0);
		if($ref > 0) db_exec([__FILE__, __LINE__], 'query', "UPDATE `w_afiliados` SET a_hits_in = a_hits_in + 1 WHERE aid = $afiliado");
		$this->Core->redirectTo($this->Core->settings['url']);
		exit();
	}
}
