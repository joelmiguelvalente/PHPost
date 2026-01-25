<?php

/**
 * @name c.afiliado.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsAfiliado {
	
	protected tsCore $Core;
	protected tsUser $User;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
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
		$aid = ($type === 'admin') ? (int)($_GET['aid'] ?? 0) : (int)($_POST['ref'] ?? 0);
		$query = "SELECT aid, a_titulo, a_url, a_banner, a_descripcion FROM w_afiliados WHERE aid = $aid";
		return db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', $query));
	}

	/**
	 * @access public
	 * @return array
	*/
	public function newAfiliado(): string {
		global $tsMonitor;
		$dataIn = [];
		$time = time();
		foreach($_POST as $key => $value) {
			$value = htmlspecialchars(trim($value ?? ''));
			$dataIn[$key] = $this->Core->setSecure($this->Core->parseBadWords($value));
		}
		$checked = $dataIn; // Evitamos modificar el array principal
		unset($checked['sid']); // Solo borramos el item de la copia del array
		if(in_array('', $checked, true)) {
		  return '2: Faltan datos';
		}
		if(!filter_var($dataIn['url'], FILTER_VALIDATE_URL)) { 
			return '0: Url incorrecta'; 
		}
		//
		if(db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_afiliados (a_titulo, a_url, a_banner, a_descripcion, a_sid, a_date) VALUES ('{$dataIn['titulo']}', '{$dataIn['url']}', '{$dataIn['banner']}', '{$dataIn['descripcion']}', '{$dataIn['sid']}', {$time})")) {
			$afid = (int)db_exec('insert_id');
		  	// AVISO
			$aviso = "<center>
				<a href=\"{$dataIn['url']}\">
					<img alt=\"banner del sitio {$dataIn['titulo']}\" src=\"{$dataIn['banner']}\" title=\"{$dataIn['titulo']}\"/>
				</a>
			</center>
			<br />
			<span>{$dataIn['titulo']} quiere ser su afiliado, dir&iacute;jase a la administraci&oacute;n para aceptar o cancelarla.</span>";
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
					<textarea tabindex=\"4\" rows=\"10\" style=\"height:60px;width:100%\" onclick=\"select(this)\"><a href=\"$urlSiteRef\" target=\"_blank\" title=\"$titleSite\"><img src=\"$bannerSite\" alt=\"banner del sitio $titleSite\"></a></textarea>
				</div>
			</div>";
		}
	}
	
	/**
	 * @access public
	 * @return string
	*/
	public function EditarAfiliado(): string {
		$afiliado = (int)($_GET['aid'] ?? 0);
		$newData = [
			'titulo' => $this->Core->parseBadWords($_POST['af_title']),
			'url' => $this->Core->parseBadWords($_POST['af_url']),
			'banner' => $this->Core->parseBadWords($_POST['af_banner']),
			'descripcion' => $this->Core->parseBadWords($_POST['af_desc'])
		];  
	   if(!$afiliado || !$newData['titulo'] || !$newData['url'] || !$newData['banner'] || !$newData['descripcion']){
		  return '0: Faltan datos';
		}
		if(!filter_var($newData['url'], FILTER_VALIDATE_URL)){ return '0: Url incorrecta'; }
		//
		$afs = $this->Core->buildSqlSet($newData , 'a_');
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
	public function SetActionAfiliado(): string {
		$afiliado = (int)($_POST['aid'] ?? 0);
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT a_active FROM w_afiliados WHERE aid = $afiliado"));
		//
		$active = ($data['a_active'] === 1) ? 0 : 1;
		if(!db_exec([__FILE__, __LINE__], 'query', "UPDATE w_afiliados SET a_active = $active WHERE aid = $afiliado")) {
			return '0: Ocurri&oacute, un error';
		}
		return ($data['a_active'] === 1) ? '2: Afiliado deshabilitado' : '1: Afiliado habilitado.';
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