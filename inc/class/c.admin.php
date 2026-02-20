<?php

/**
 * @name c.admin.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

require_once TS_HELPERS . '/AdminHelper.php';
require_once TS_CLASS . '/c.emails.php';

class tsAdmin {
   
   protected tsCore $Core;
   protected tsUser $User;
   protected Paginator $Paginator;

   # Cantidad de objeto a mostrar
   CONST MAX_SHOW = 20;

   protected AdminHelper $AdminHelper;

   public function __construct(tsCore $Core, tsUser $User) {
      $this->Core = $Core;
      $this->User = $User;
      $this->AdminHelper = new AdminHelper;
      $this->Paginator = new Paginator($this->Core->settings['url']);
   }

   /**
    * Obtenemos a todos los administradores
   */
   public function getAdmins(): array {
      return result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT `user_id`, `user_name` FROM `u_miembros` WHERE user_rango = 1 ORDER BY user_id'));
   }
   /**
    * Obtenemos fundación y acutalización
   */
   public function getInst(): array {
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT stats_time_foundation as foundation, stats_time_upgrade as upgrade FROM `w_stats` WHERE stats_no = 1'));
      return $data;
   }
   /**
    * Obtenemos las versiones
   */
   public function getVersions(): array {
      $data = [];
      // PHP
      $data['php'] = [
         'version' => PHP_VERSION,
         'sapi' => PHP_SAPI,
         'memory_limit' => ini_get('memory_limit'),
         'timezone' => date_default_timezone_get(),
      ];
      // Database
      $row = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT VERSION() AS v'));
      $data['database'] = [
         'engine' => 'mysql',
         'version' => $row['v'] ?? null,
      ];
      // Server
      $data['server'] = [
         'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
         'os' => PHP_OS_FAMILY,
      ];
      // Extensions
      $data['extensions'] = [
         'gd' => extension_loaded('gd') ? [
            'enabled' => true,
            'version' => gd_info()['GD Version'] ?? null,
         ] : ['enabled' => false],
         'mbstring' => extension_loaded('mbstring'),
         'intl'     => extension_loaded('intl'),
         'curl'     => extension_loaded('curl'),
         'openssl'  => extension_loaded('openssl'),
         'json'     => extension_loaded('json'),
      ];
      return $data;
   }

   /**
    * @access public
    * @return bool
   */   
   public function saveConfig(string $table = 'w_configuracion', string $id = 'phpost_id'): bool {
      $columnas = $this->Core->buildSqlSet($_POST);
      $update = "UPDATE {$table} SET {$columnas} WHERE {$id} = 1";
      return (db_exec([__FILE__, __LINE__], "query", $update));
   }
   
   /**
    * ------------------------------
    * PUBLICIDADES
    * saveAds() :: Guardamos las publicidades
    * ------------------------------ 
   */
   public function saveAds() {
      /**
       * Podria ser un riesgo de seguridad no limpiar estas variables? 
       * no lo creo pues cuando definimos el nivel de acceso solo 
       * pueden entrar administradores.
      */
      $publicidades = $this->Core->buildSqlSet([
         'ads_300' => $this->Core->setSecure(html_entity_decode($_POST['ads_300'])),
         'ads_468' => $this->Core->setSecure(html_entity_decode($_POST['ads_468'])),
         'ads_160' => $this->Core->setSecure(html_entity_decode($_POST['ads_160'])),
         'ads_728' => $this->Core->setSecure(html_entity_decode($_POST['ads_728'])),
         'ads_search' => $this->Core->setSecure($_POST['ads_search'])
      ]);
      # Guardamos los datos en la base
      if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE `w_configuracion` SET '.$publicidades.' WHERE phpost_id = 1')) return true;
   }
   /**
    * ------------------------------
    * CATEGORIAS
    * saveOrden() :: Guardamos nuevo orden de las categorías
    * getCat() :: Obtenemos la categoría por ID
    * saveCat() :: Guardamos los nuevos datos de la categoría
    * MoveCat() :: Mover de categoría
    * newCat() :: Creamos una nueva categoría
    * delCat() :: Eliminamos la categoría 
    * ------------------------------ 
   */
   public function saveOrden() {
      global $tsCore;
      # 
      $ordenado = [];
      # Obtenemos lista con el nuevo orden
      $nuevo_orden = 1;
      foreach (explode(',', $_POST["cats"]) as $orden) {
         db_exec([__FILE__, __LINE__], 'query', "UPDATE p_categorias SET c_orden = ".$nuevo_orden." WHERE cid = ".$orden);
         array_push($ordenado, $nuevo_orden);
         $nuevo_orden++;
      }
   }
   public function getCat() {
      global $tsCore;
      # Obtenemos la ID de la categoría
      $cid = intval($_GET['cid']);
      # Obtenemos la información
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT cid, c_orden, c_nombre, c_seo, c_img FROM p_categorias WHERE cid = '.$cid.' LIMIT 1'));
      # Retornamos los daots
      return $data;
   }
   public function saveCat() {
      global $tsCore;
      # Obtenemos la ID de la categoría
      $cid = intval($_GET['cid']);
      //
      $nombre = $tsCore->setSecure($tsCore->parseBadWords($_POST['c_nombre']));
      $categoria = $tsCore->buildSqlSet([
         "nombre" => $nombre,
         "seo" => $tsCore->setSEO($nombre),
         "img" => $tsCore->setSecure($tsCore->parseBadWords($_POST['c_img'])),
      ], 'c_');
      # Guardamos en la tabla
      if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE `p_categorias` SET '.$categoria.' WHERE cid = ' . $cid)) return true;
   }
   public function MoveCat() {
      $new = intval($_POST['newcid']);
      if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE `p_posts` SET post_category = '.$new.' WHERE post_category = ' . intval($_POST['oldcid']))) return true;
   }
   public function newCat() {
      global $tsCore;
      # Valores
      $c_nombre = $tsCore->setSecure($tsCore->parseBadWords($_POST['c_nombre']));
      $c_seo = $tsCore->setSEO($c_nombre);
      $c_img = $tsCore->setSecure($tsCore->parseBadWords($_POST['c_img']));
      # Orden
      $orden = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(cid) AS total FROM p_categorias'));
      $orden = $orden['total'] + 1;
      # Insertamos los datos
      if (db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO `p_categorias` (`c_orden`, `c_nombre`, `c_seo`, `c_img`) VALUES ('.$orden.', \''.$c_nombre.'\',\''.$c_seo.'\', \''.$c_img.'\')')) return true;
   }
   public function delCat() {
      global $tsCore;
      //
      $cid = intval($_GET['cid']);
      $ncid = intval($_POST['ncid']);
      // MOVER
      if (!empty($ncid) && $ncid > 0) {
         if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE `p_posts` SET post_category = '.$ncid.' WHERE post_category = ' . $cid)) {
            if (db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `p_categorias` WHERE cid = ' . $cid)) return true;
         // SI LLEGÓ HASTA AQUI HUBO UN ERROR.
         } else return 'Lo sentimos ocurri&oacute; un error, pongase en contacto con PHPost.';
      } else return 'Antes de eliminar una categor&iacute;a debes elegir a donde mover sus subcategor&iacute;as.';
   }
   /**
    * ------------------------------
    * RANGOS
    * getRangos() :: Obtenemos todos los rangos
    * getRango() :: Obtenemos el rango por ID
    * getRangoUsers() :: Obtenemos rangos de usuarios
    * saveRango() :: Guardamos los datos del rango
    * newRango() :: Creamos un nuevo rango
    * delRango() :: Eliminamos el rango
    * SetDefaultRango() :: Rango predeterminado
    * ------------------------------ 
   */
   public function getRangos() {
      global $tsCore;
      // RANGOS SIN PUNTOS
      $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM u_rangos ORDER BY rango_id, r_cant');
      // ARMAR ARRAY
      while ($row = db_exec('fetch_assoc', $query)) {
         $extra = unserialize($row['r_allows']);
         $data[$row['r_type'] == 0 ? 'regular' : 'post'][$row['rango_id']] = array(
            'id' => $row['rango_id'],
            'name' => $row['r_name'],
            'color' => $row['r_color'],
            'imagen' => $row['r_image'],
            'cant' => $row['r_cant'],
            'max_points' => $extra['gopfp'],
            'user_puntos' => $extra['gopfd'],
            'type' => $row['r_type'],
            'num_members' => 0
         );
      }
      db_exec('free_result', $query);
      // NUMERO DE USUARIOS EN CADA RANGO
      if (!empty($data['post'])) {
         $query = db_exec([__FILE__, __LINE__], 'query', "SELECT user_rango AS ID_GROUP, COUNT(user_id) AS num_members FROM u_miembros WHERE user_rango IN (" . implode(', ', array_keys($data['post'])) . ") GROUP BY user_rango");
         while ($row = db_exec('fetch_assoc', $query)) $data['post'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
         db_exec('free_result', $query);
      }
      // NUMERO DE USUARIOS EN RANGOS REGULARES
      if (!empty($data['regular'])) {
         $query = db_exec([__FILE__, __LINE__], 'query', "SELECT user_rango AS ID_GROUP, COUNT(*) AS num_members FROM u_miembros WHERE user_rango IN (" . implode(', ', array_keys($data['regular'])) . ") GROUP BY user_rango");
         while ($row = db_exec('fetch_assoc', $query)) $data['regular'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
         db_exec('free_result', $query);
      }
      //
      return $data;
   }
   public function getRango() {
      global $tsCore;
      # Obtenemos la ID
      $id = intval($_GET['rid']);
      # Obtenemos datos
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM u_rangos WHERE rango_id = \'' . $id .'\' LIMIT 1'));
      # Deserializamos
      $data['permisos'] = unserialize($data['r_allows']);
      # Retornamos los datos
      return $data;
   }
   public function getRangoUsers() {
      global $tsCore;
      //
      $rid = intval($_GET['rid']);
      $max = 10; // MAXIMO A MOSTRAR
      // TIPO DE BUSQUEDA
      $type = $_GET['t'];
      $where = 'user_rango = ' . $rid;
      // SELECCIONAMOS
      $limit = $tsCore->setPageLimit($max, true);
      $data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_id, u.user_name, u.user_email, u.user_registro, u.user_lastlogin FROM u_miembros AS u WHERE u.' . $where . ' LIMIT ' . $limit));
      # Paginamos
      list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(*) FROM u_miembros WHERE ' . $where));
      $data['pages'] = $tsCore->pageIndex($tsCore->settings['url'] . '/admin/rangos?act=list&rid=' . $rid . '&t=' . $type . '', $_GET['s'], $total, $max);
      # Retornamos
      return $data;
   }
   public function saveRango() {
      global $tsCore;
      //
      $rid = intval($_GET['rid']);
      $r = [
         'r_name' => $tsCore->setSecure($tsCore->parseBadWords($_POST['rName'])),
         'r_color' => $tsCore->setSecure($_POST['rColor']),
         'r_image' => $tsCore->setSecure($_POST['r_img']),
         'r_cant' => intval(empty($_POST['global-cantidadrequerida']) ? 0 : $tsCore->setSecure($_POST['global-cantidadrequerida'])),
         'r_type' => $_POST['global-type'] > 4 ? 0 : $_POST['global-type'],
         'r_allows' => $this->AdminHelper->optionsRange($_POST)
      ];
      //
      if (empty($r['r_name']))  return 'Debes ingresar el nombre del nuevo rango.';
      if ($_POST['global-pointsforposts'] > $_POST['global-pointsforday']) return 'El rango no puede dar m&aacute;s puntos de los que tiene al d&iacute;a.';
      //
      $columnas = $tsCore->buildSqlSet( $r );
      // 
      return (db_exec([__FILE__, __LINE__], 'query', 'UPDATE `u_rangos` SET '.$columnas.' WHERE rango_id = ' . $rid)) ? true : exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') );
   }
   public function newRango() {
      global $tsCore;
      //
      $r = [
         'r_name' => $tsCore->setSecure($tsCore->parseBadWords($_POST['rName'])),
         'r_color' => $tsCore->setSecure($_POST['rColor']),
         'r_img' => $tsCore->setSecure($_POST['r_img']),
         'r_cant' => intval(empty($_POST['global-cantidadrequerida']) ? 0 : $tsCore->setSecure($_POST['global-cantidadrequerida'])),
         'r_type' => intval($_POST['global-type'] > 4 ? 0 : $_POST['global-type']),
         'r_allows' => $this->AdminHelper->optionsRange($_POST)
      ];
      //
      if (empty($r['r_name'])) return 'Debes ingresar el nombre del nuevo rango.';
      if ($_POST['global-pointsforposts'] > $_POST['global-pointsforday']) return 'El rango no puede dar m&aacute;s puntos de los que tiene al d&iacute;a.';
      //
      if (db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO `u_rangos` (`r_name`, `r_color`, `r_image`, `r_cant`, `r_allows`, `r_type`) VALUES (\'' . $r['r_name'] . '\', \'' . $r['r_color'] . '\', \'' . $r['r_img'] . '\', \'' . $r['r_cant'] . '\', \'' . $r['r_allows'] . '\', \'' . $r['r_type'] . '\')')) return 1;
   }
   public function delRango() {
      global $tsCore;
      //
      $rid = intval($_GET['rid']);
      $nid = intval($_POST['new_rango']);
      //
      if ($rid > 3) {
         if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_rango = '.$nid.' WHERE user_rango = ' . $rid )) {
            if (db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM u_rangos WHERE rango_id = ' . $rid)) return true;
         }
      } else return 'No es posible eliminar este rango';
   }
   public function SetDefaultRango() {
      global $tsCore;
      //
      if($_SERVER['HTTP_REFERER'] == $tsCore->settings['url'].'/admin/rangos?save=true' || $_SERVER['HTTP_REFERER'] == $tsCore->settings['url'].'/admin/rangos') {
         $rid = intval($_GET['rid']);
         //
         $dato = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT rango_id, r_type FROM u_rangos WHERE rango_id = ' .$rid.' LIMIT 1'));
         if (!empty($dato['rango_id']) && intval($dato['r_type']) == 0) {
            if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE w_configuracion SET c_reg_rango = '.$rid.' WHERE phpost_id = 1')) return true;
         } else return 'El rango no existe o no es posible utilizarlo';
      } else return 'Petici&oacute;n inv&aacute;lida';
   }
   /**
    * ------------------------------
    * USUARIOS
    * getUsuarios() :: Obtenemos todos los usuarios
    * getUserPrivacidad() :: Obtenemos privacidad del usuario
    * setUserPrivacidad() :: Guardamos privacidad del usuario
    * getUserData() :: Obtenemos datos del usuario
    * setUserData() :: Guardamos datos del usuario
    * deleteContent() :: Eliminamos el contenido del usuario
    * getUserRango() :: Obtenemos el rango del usuario
    * setUserFirma() :: Guardamos nueva firma del usuario
    * setUserInActivo() :: Activar/Desactivar usuario (AJAX)
    * ------------------------------ 
   */
   public function getUsuarios() {
      global $tsCore;
      //
      $max = 20; // MAXIMO A MOSTRAR
      $limit = $tsCore->setPageLimit($max, true);
      //
      $order = ($_GET['o'] === 'e') ? 'activo, u.user_baneado' : ($_GET['o'] === 'c' ? 'email' : ($_GET['o'] == 'i' ? 'last_ip' : ($_GET['o'] == 'u' ? 'lastactive' : 'id')));
      //
      $data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT u.*, r.*, p.* FROM u_perfil AS p LEFT JOIN u_miembros AS u ON u.user_id = p.user_id LEFT JOIN u_rangos AS r ON r.rango_id = u.user_rango ORDER BY u.user_'.$order.' ' . ($_GET['m'] == 'a' ? 'ASC' : 'DESC') . ' LIMIT ' . $limit));
      # Paginamos
      list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(*) FROM u_miembros WHERE user_id > 0'));
      $data['pages'] = $tsCore->pageIndex($tsCore->settings['url'] . "/admin/users?o=" . $_GET['o'] . "&m=" . $_GET['m'] . "", $_GET['s'], $total, $max);
      # Retornamos
        return $data;
   }
   public function getUserPrivacidad() {
      # Obtenemos la ID del usuario
      $uid = intval($_GET['uid']);
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT p_configs FROM u_perfil WHERE user_id = '.$uid.' LIMIT 1'));
      $data['p_configs'] = unserialize($data['p_configs']);
      //
      return $data;
   }
   public function setUserPrivacidad() {
      global $tsCore;
      # ID del usuario
      $uid = intval($_GET['uid']);
      //
      $muro_firm = ($_POST['muro_firm'] > 4) ? 5 : $_POST['muro_firm'];
      $see_hits = ($_POST['last_hits'] == 1 || $_POST['last_hits'] == 2) ? 0 : $_POST['last_hits'];
      $perfilData['configs'] = serialize([
         'm' => $_POST['muro'],
         'mf' => $muro_firm,
         'rmp' => $_POST['rec_mps'],
         'hits' => $see_hits
      ]);
      //
      $updates = $tsCore->buildSqlSet($perfilData, 'p_');
      if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_perfil SET ' . $updates . ' WHERE user_id = ' . $uid)) return true;
   }
   public function getUserData() {
      global $tsCore;
      # ID del usuario
      $user_id = intval($_GET['uid']);
      //
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT u.*, r.*, p.* FROM u_perfil AS p LEFT JOIN u_miembros AS u ON u.user_id = p.user_id LEFT JOIN u_rangos AS r ON r.rango_id = u.user_rango WHERE u.user_id = '.$user_id.' LIMIT 1'));
      $data['p_configs'] = json_decode($data['p_configs'], true);
      # Retornamos
      return $data;
   }
   public function setUserData(int $user_id = 0) {
      global $tsCore;
      # DATA
      $data = db_exec('fetch_assoc',db_exec([__FILE__, __LINE__], 'query', 'SELECT `user_name`, `user_email`, `user_password` FROM u_miembros WHERE user_id = ' . $user_id));
      # LOCALS
      $email = $tsCore->setSecure(empty($_POST['email']) ? $data['user_email'] : $_POST['email']);
      $password = $_POST['pwd'];
      $cpassword = $_POST['cpwd'];
      $user_nick = empty($_POST['nick']) ? $data['user_name'] : $_POST['nick'];
      $user_points = empty($_POST['points']) ? $data['user_puntos'] : $_POST['points'];
      $pointsxdar = empty($_POST['pointsxdar']) ? $data['user_puntos'] : $_POST['pointsxdar'];
      $changenames = empty($_POST['changenicks']) ? $data['user_name_changes'] : $_POST['changenicks'];
      $up["user_email"] = $email;
      #
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Correo electr&oacute;nico incorrecto';
      if ($user_points >= 0) {
         $up["user_puntos"] = intval($user_points);
      } else return 'Los puntos del usuario no se reconocen';
      if ($changenames >= 0) {
         $up["user_name_changes"] = intval($changenames);
      } else return 'Las disponibilidades de cambios de nombre de usuario deben ser num&eacute;ricas.';
      if ($pointsxdar >= 0) {
         $up["user_puntosxdar"] = intval($pointsxdar);
      } else return 'Los puntos para dar no se reconocen';
      if (!empty($password) && !empty($cpassword)) {
         if (strlen($user_nick) < 3) return 'Nick demasiado corto.';
         if (!preg_match('/^([A-Za-z0-9]+)$/', $user_nick)) return 'Nick inv&aacute;lido';
         $up["user_name"] = $tsCore->setSecure($user_nick);
         # Pass
         if (strlen($password) < 6) return 'Contrase&ntilde;a no v&aacute;lida.';
         if ($password != $cpassword) return 'Las contrase&ntilde;as no coinciden';
         $up["user_password"] = $tsCore->setSecure(md5(md5($password) . strtolower($user_nick)));
      }
      # Guardamos los nuevos datos
      $update = $tsCore->buildSqlSet($up);
      if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE `u_miembros` SET '.$update.' WHERE user_id = ' . $user_id)) {
         if ($_POST['sendata']) {
            mail($email, "Nuevos datos de acceso", "Sus datos de acceso a {$tsCore->settings['titulo']} han sido cambiados por un administrador. Los nuevos datos son: usuario: {$user_nick}, contraseña: {$password}. Disculpe las molestias", "From: {$tsCore->settings['titulo']} <no-reply@{$tsCore->settings['domain']}>");
         }
         return true;
      }
   }
   public function deleteContent(int $user_id = 0){
      global $tsUser;
      #
      $pass = md5(md5($_POST['password']) . strtolower($tsUser->nick));
      if(db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_id FROM u_miembros WHERE user_id = \''.$tsUser->uid.'\' && user_password = \''.$pass.'\''))) {
         # Nuevo formato mejorado (entendible)
         $todo = isset($_POST['bocuenta']);
         # Creamos un arreglo que tenga las tablas y columnas con datos
         $arreglo = [
            'boposts' => ['tabla' => 'p_posts', 'columna' => 'post_user'],
            'bofotos' => ['tabla' => 'f_fotos', 'columna' => 'f_user'],
            'boestados' => ['tabla' => 'u_muro', 'columna' => 'p_user_pub'],
            'bocomposts' => ['tabla' => 'p_comentarios', 'columna' => 'c_user'],
            'bocomfotos' => ['tabla' => 'f_comentarios', 'columna' => 'c_user'],
            'bocomestados' => ['tabla' => 'u_muro_comentarios', 'columna' => 'c_user'],
            'bolikes' => ['tabla' => 'u_muro_likes', 'columna' => 'user_id'],
            'boseguidores' => ['tabla' => 'u_follows', 'columna' => 'f_type = 1 && f_id'],
            'bosiguiendo' => ['tabla' => 'u_follows', 'columna' => 'f_type = 1 && f_user'],
            'bofavoritos' => ['tabla' => 'p_favoritos', 'columna' => 'fav_user'],
            'bovotosposts' => ['tabla' => 'p_votos', 'columna' => 'tuser'],
            'bovotosfotos' => ['tabla' => 'f_votos', 'columna' => 'v_user'],
            'boactividad' => ['tabla' => 'u_actividad', 'columna' => 'user_id'],
            'boavisos' => ['tabla' => 'u_avisos', 'columna' => 'user_id'],
            'bobloqueos' => ['tabla' => 'u_bloqueos', 'columna' => 'b_user'],
            'bomensajes' => ['tabla' => ['u_mensajes', 'u_respuestas'], 'columna' => ['mp_from', 'mr_from']],
            'bosesiones' => ['tabla' => 'u_sessions', 'columna' => 'session_user_id'],
            'bovisitas' => ['tabla' => 'w_visitas', 'columna' => 'user']
         ];
         foreach($arreglo as $accion => $tipo) {
            if($_POST[$accion] === 'on') {
               if(is_array($tipo["tabla"]) OR is_array($tipo["columna"])) {
                  foreach ($tipo["tabla"] as $t => $tabla) {
                     db_exec([__FILE__, __LINE__], 'query', "DELETE FROM {$tipo["tabla"][$t]} WHERE {$tipo["columna"][$t]} = {$user_id}");
                  }
               } else {
                  db_exec([__FILE__, __LINE__], 'query', "DELETE FROM {$tipo["tabla"]} WHERE {$tipo["columna"]} = {$user_id}");
               }
            }
         }
         //
         if($todo && $tsUser->uid != $user_id){
            $array = [
               ['tabla' => 'u_miembros', 'columna' => 'user_id'],
               ['tabla' => 'u_perfil', 'columna' => 'user_id'],
               ['tabla' => 'u_portal', 'columna' => 'user_id'],
               ['tabla' => 'w_denuncias', 'columna' => 'd_user'],
               ['tabla' => 'u_bloqueos', 'columna' => 'b_auser'],
               ['tabla' => 'u_mensajes', 'columna' => 'b_auser'],
               ['tabla' => 'w_visitas', 'columna' => 'type = 1 && for']
            ];
            foreach($array as $item) {
               db_exec([__FILE__, __LINE__], 'query', "DELETE FROM {$item["tabla"]} WHERE {$item["columna"]} = {$user_id}");
            }
         }
         #
         $data = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_name FROM u_miembros WHERE user_id = '.$user_id));
         $admin = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_email FROM u_miembros WHERE user_id = 1'));
         # Insertamos el aviso
         db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO `u_avisos` (`user_id`, `av_subject`, `av_body`, `av_date`, `av_read`, `av_type`) VALUES (\'1\', \'Contenido eliminado\', \'Hola, le informamos que el administrador '.$tsUser->nick.' ('.$tsUser->uid.') ha eliminado '.($todo ? 'la cuenta' : 'varios contenidos').' de '.$data[0].'.\', \''.time().'\', \'0\', \'1\')');
         # Enviamos el email
         mail($admin[0], 'Contenido eliminado', '<html><head><title>Contenido de cierta cuenta han sido eliminados.</title></head><body><p>Hola, le informamos que el administrador '.$tsUser->nick.' ('.$tsUser->uid.') ha eliminado '.($todo ? 'la cuenta' : 'varios contenidos').' de '.$data[0].'</p></body></html>', 'Content-type: text/html; charset=iso-8859-15');
         # Retornamos OK
         return 'OK';
      } else return 'Credenciales incorrectas';
   }
   public function getUserRango(int $user_id = 0) {
      # CONSULTA
      $data['user'] = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_rango, r.rango_id, r.r_name, r.r_color FROM u_miembros AS u LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = '.intval($user_id).' LIMIT 1'));
      # RANGOS DISPONIBLES
      $data['rangos'] = self::getAllRangos();
      # Retornamos datos
      return $data;
   }
   public function setUserFirma(int $user_id = 0) {
      global $tsCore;
      if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE `u_perfil` SET user_firma = \'' . $tsCore->setSecure($_POST['firma']) . '\' WHERE user_id = ' . intval($user_id))) return true;
   }
   public function setUserInActivo() {
      global $tsUser;
      # Obtenemos la ID del usuair
      $usuario = intval($_POST['uid']);
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_activo FROM u_miembros WHERE user_id = ' . $usuario));
      # Hacemos comprobaciones
      $act = (intval($data['user_activo']) === 1) ? 0 : 1;
      $txt = (intval($data['user_activo']) === 1) ? '2: Cuenta desactivada' : '1: Cuenta activada.';
      //
      return (db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_activo = '.$act.' WHERE user_id = ' . $usuario)) ? $txt : '0: Ocurri&oacute, un error';
   }
   /**
    * ------------------------------
    * RANGOS
    * getAllRangos() :: Obtenemos todos los rangos
    * setUserRango() :: Cambiamos de rangos a usuarios
    * ------------------------------ 
   */
   public function getAllRangos() {
      # RANGOS DISPONIBLES
      $data = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT `rango_id`, `r_name`, `r_color` FROM `u_rangos`'));
      # Retornamos datos
      return $data;
   }
   public function setUserRango(int $user_id = 0) {
      global $tsUser;
      # SOLO EL PRIMER ADMIN PUEDE PONER A OTROS ADMINS
      $new_rango = intval($_POST['new_rango']);
      if ($user_id === $tsUser->uid) return 'No puedes cambiarte el rango a ti mismo';
      elseif ($tsUser->uid !== 1 && $new_rango === 1) return 'Solo el primer Administrador puede crear más administradores principales';
      else {
         if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_rango = '.$new_rango.' WHERE user_id = ' . intval($user_id))) return true;
      }
   }
   /**
    * ------------------------------
    * SESIONES
    * getSessions() :: Obtenemos todas las sesiones
    * delSession() :: Eliminamos la sesion por "session_id"
    * ------------------------------ 
   */
   public function getSessions() {
      global $tsCore;
      # Limite
      $limit = $tsCore->setPageLimit($this->max, true);
      # Datos
      $data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_id, u.user_name, s.* FROM u_sessions AS s LEFT JOIN u_miembros AS u ON s.session_user_id = u.user_id ORDER BY s.session_time DESC LIMIT ' . $limit));
      # Paginamos
      list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(*) FROM u_sessions'));
      $data['pages'] = $tsCore->pageIndex($tsCore->settings['url'] . "/admin/sesiones?", $_GET['s'], $total, $this->max);
      # Retornamos datos
      return $data;
   }
   public function delSession() {
      global $tsCore;
      # Obtenemos la session_id
      $session_id = $_POST['session_id'];
      if (db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT session_id FROM u_sessions WHERE session_id = \'' . $tsCore->setSecure($session_id) . '\' LIMIT 1'))) {
         if (db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM u_sessions WHERE session_id = \'' . $tsCore->setSecure($session_id) . '\'')) return '1: Eliminado';
      } else return '0: No existe esa sesi&oacute;n';
   }
   /**
    * ------------------------------
    * NICKS
    * getChangeNicks() :: Obtenemos todos los nicks / Cambios realizados
    * ChangeNick_o_no() :: Aprobar/Desaprobar cambio
    * ------------------------------ 
   */
   public function getChangeNicks(string $hecho = '') {
      global $tsCore;
      # Cambio realizado
      $hecho = ($hecho === 'realizados') ? ">" : "=";
      # Limite
      $limit = $tsCore->setPageLimit($this->max, true);
      # Datos
      $data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_id, u.user_name, n.* FROM u_nicks AS n LEFT JOIN u_miembros AS u ON n.user_id = u.user_id WHERE estado '.$hecho.' 0 ORDER BY n.time DESC LIMIT ' . $limit));
      # Paginacion
      list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(*) FROM u_nicks WHERE estado '.$hecho.' 0'));
      $data['pages'] = $tsCore->pageIndex($tsCore->settings['url'] . "/admin/nicks?", $_GET['s'], $total, $this->max);
      # Retornamos datos
      return $data;
   }
   public function ChangeNick_o_no() {
      global $tsMonitor;
      # ID del nick
      $nid = (int)($_POST['nid'] ?? 0);
      # Datos
      $result = db_exec([__FILE__, __LINE__], 'query', "SELECT user_id, user_email, name_1, name_2 FROM u_nicks WHERE id = $nid LIMIT 1");
      $user = db_exec('fetch_assoc', $result) ?? [];
      [ 'user_id' => $uid, 'user_email' => $email, 'name_1' => $name1, 'name_2' => $name2] = $user;
      $title = $this->Core->settings['titulo'];
      # Aprobamos
      if (isset($_POST['accion']) && trim($_POST['accion']) === 'aprobar') {
         db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_name = '$name2', user_name_changes = user_name_changes - 1 WHERE user_id = $uid");
         db_exec([__FILE__, __LINE__], 'query', "UPDATE u_nicks SET estado = 1 WHERE id = $nid");
         # Enviamos un aviso
         $aviso = "Hola <strong>$name1</strong>,\n\n Le informo que desde este momento su nombre de acceso ser&aacute; <strong>$name2</strong> . Hasta pronto.";
         $tsMonitor->setAviso($uid, 'Cambio realizado', $aviso, 4);
         //ENVIAMOS CORREO
         $subject = "$name1, su petici&oacute;n de cambio ha sido aceptada";
         $body = "Hola $name1:\nLe enviamos este email para informarle que su petici&oacute;n de cambio de nick ha sido aceptada.<br>Desde este momento, podr&aacute; acceder en $title con el nombre de usuario $name2. <br /><hr>El staff de <strong>$title</strong>";
      # Denegamos
      } elseif (isset($_POST['accion']) && trim($_POST['accion']) === 'denegar') {
         db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_name_changes = user_name_changes - 1 WHERE user_id = $uid");
         db_exec([__FILE__, __LINE__], 'query', "UPDATE u_nicks SET estado = 2 WHERE id = $nid");
         # Enviamos un aviso
         $aviso = "Hola <strong>$name1</strong>,\n\n Lamento informarle que su petici&oacute;n de cambio de nick a <strong>$name2</strong> , ha sido denegada.";
         $tsMonitor->setAviso($uid, 'Cambio realizado', $aviso, 3);
         //ENVIAMOS CORREO
         $subject = "$name1, su petici&oacute;n de cambio ha sido denegada";
         $body = "Hola $name1:\nLe enviamos este email para informarle que su petici&oacute;n de cambio de nick ha sido denegada.\n<hr>El staff de <strong>$title</strong>'";
      } else return '0: Mijo, ve de paseo';

      $email = new tsEmail($this->Core);
      $email->sendSignup($email, 'confirmar', $body) OR die('0: Hubo un error al intentar procesar lo solicitado');

      return "1: Hemos enviado un correo a <strong>$email</strong> con la decisi&oacute;n tomada. Tambi&eacute;n le hemos enviado un aviso al usuario.";
   }


    /****************** ADMINISTRACIÓN DE POSTS ******************/
   public function getAdmin(string $type = 'posts') {
      return match($type) {
         'posts' => $this->getAdminPosts(),
         'fotos' => $this->getAdminFotos(),
         default => null
      };
   }
   
   private function getAdminPosts() {
      $max = 20; // MAXIMO A MOSTRAR
      $limit = $this->Paginator->setPageLimit($max, true);
      $order = trim($_GET['order'] ?? '');
      $asc = trim($_GET['modo'] ?? '');
      $orden = match($order) {
         'estado' => 'p.post_status',
         'ip' => 'p.post_ip',
         default => 'p.post_id'
      };
      $upper = strtoupper($asc);
      $data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, c.c_nombre, c.c_seo, c.c_img, p.* FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_id > 0 ORDER BY $orden $upper LIMIT $limit"));

      // PAGINAS
      list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(*) FROM p_posts WHERE post_id > 0'));

      $this->Paginator->route = $this->Core->settings['url'];
      $data['pages'] = $this->Paginator->pageIndex("/admin/posts?order=$order&modo=$asc", (int)($_GET['s'] ?? 0), (int)$total, (int)$max);
      //
      return $data;
   }


   /****************** ADMINISTRACIÓN DE FOTOS ******************/
   public function getAdminFotos(): array {
      $max = 15; // MAXIMO A MOSTRAR
      $limit = $this->Paginator->setPageLimit($max, true);
      //
      $data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, f.* FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id > 0 ORDER BY f.foto_id DESC LIMIT $limit"));
      // PAGINAS
      list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(*) FROM f_fotos WHERE foto_id > 0"));
      $this->Paginator->route = $this->Core->settings['url'];
      $data['pages'] = $tsCore->pageIndex("/admin/fotos?", (int)($_GET['s']??0), (int)$total, (int)$max);
      //
      return $data;
   }

   public function DelFoto(): string {
      $foto = (int)($_POST['foto_id'] ?? 0);
      if (!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT foto_id FROM f_fotos WHERE foto_id = $foto"))) {
         return '0: La foto no existe';
      }
      if (!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM f_fotos WHERE foto_id = $foto")) {
         return '0: La foto no se pudo eliminar';
      }
      return '1: Foto eliminada';
   }

   public function setOpenClosedFoto(): string {
      $fid = (int)($_POST['fid'] ?? 0);
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT f_closed FROM f_fotos WHERE foto_id = $fid"));
      // COMPROBAMOS
      $active = ((int)$data['f_closed'] === 1) ? 0 : 1;
      if(!db_exec([__FILE__, __LINE__], 'query', "UPDATE f_fotos SET f_closed = $active WHERE foto_id = $fid")) {
         return '0: Ocurri&oacute, un error';
      }
      return ($active === 1) ? '2: Comentarios abiertos' : '1: Comentarios cerrados.';
   }

   public function setShowHideFoto(): string {
      $fid = (int)($_POST['fid'] ?? 0);
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT f_status FROM f_fotos WHERE foto_id = $fid"));
      // COMPROBAMOS
      $active = ((int)$data['f_status'] === 1) ? 0 : 1;
      if(!db_exec([__FILE__, __LINE__], 'query', "UPDATE f_fotos SET f_status = $active WHERE foto_id = $fid")) {
         return '0: Ocurri&oacute, un error';
      }
      return ($active === 1) ? '2: Foto rehabilitada' : '1: Foto deshabilitada.';
   }

}