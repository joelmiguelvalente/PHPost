<?php

declare(strict_types=1);

/**
 * @package    PHPost/Utils
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class Permissions {
	
	public const TREE = [
		'admin' => [
         'superadministrador' => ['code' => 'suad', 'type' => 'bool', 'field' => 'superadmin'],
         'supermoderador' => ['code' => 'sumo', 'type' => 'bool', 'field' => 'supermod'],
      ],
      'moderacion' => [
         'panel' => [
            'acceso' => ['code' => 'moacp', 'type' => 'bool', 'field' => 'mod-accesopanel'],
         ],
         'denuncias' => [
            'cancelar' => [
               'fotos' => ['code' => 'mocdf', 'type' => 'bool', 'field' => 'mod-cancelardenunciasfotos'],
               'mensajes' => ['code' => 'mocdm', 'type' => 'bool', 'field' => 'mod-cancelardenunciasmensajes'],
               'posts' => ['code' => 'mocdp', 'type' => 'bool', 'field' => 'mod-cancelardenunciasposts'],
               'usuarios' => ['code' => 'mocdu', 'type' => 'bool', 'field' => 'mod-cancelardenunciasusuarios'],
            ],
            'aceptar_mensajes' => ['code' => 'moadm', 'type' => 'bool', 'field' => 'mod-aceptardenunciasmensajes'],
         ],
         'usuarios' => [
            'buscador' => ['code' => 'moub',   'type' => 'bool', 'field' => 'mod-usarbuscador'],
            'desbanear' => ['code' => 'modu',   'type' => 'bool', 'field' => 'mod-desbanearusuarios'],
            'suspender' => ['code' => 'mosu',   'type' => 'bool', 'field' => 'mod-suspenderusuarios'],
            'ver_baneados' => ['code' => 'movub',  'type' => 'bool', 'field' => 'mod-verusuariosbaneados'],
            'ver_desactivados' => ['code' => 'movcud', 'type' => 'bool', 'field' => 'mod-vercuentasdesactivadas'],
            'ver_suspendidos' => ['code' => 'movcus', 'type' => 'bool', 'field' => 'mod-vercuentassuspendidas'],
         ],
         'comentarios' => [
            'desaprobados' => ['code' => 'mocc', 'type' => 'bool', 'field' => 'mod-contenidocomentarios'],
         ],
         'posts' => [
            'abrir_cerrar' => ['code' => 'moayca',   'type' => 'bool', 'field' => 'mod-abrirycerrarajax'],
            'comentarios_cerrado' => ['code' => 'mocepc',   'type' => 'bool', 'field' => 'mod-comentarpostcerrado'],
            'desaprobados' => ['code' => 'mocp',     'type' => 'bool', 'field' => 'mod-contenidoposts'],
            'editar' => ['code' => 'moedpo',   'type' => 'bool', 'field' => 'mod-editarposts'],
            'editar_comentarios' => ['code' => 'moedcopo', 'type' => 'bool', 'field' => 'mod-editarcomposts'],
            'eliminar' => ['code' => 'moep',     'type' => 'bool', 'field' => 'mod-eliminarposts'],
            'eliminar_comentarios' => ['code' => 'moecp',    'type' => 'bool', 'field' => 'mod-eliminarcomposts'],
            'fijar' => ['code' => 'most',     'type' => 'bool', 'field' => 'mod-sticky'],
            'ocultar' => ['code' => 'moop',     'type' => 'bool', 'field' => 'mod-ocultarposts'],
            'papelera' => ['code' => 'morp',     'type' => 'bool', 'field' => 'mod-reciclajeposts'],
            'revision' => ['code' => 'moaydcp',  'type' => 'bool', 'field' => 'mod-desyaprobarcomposts'],
         ],
         'fotos' => [
         	'editar' => ['code' => 'moedfo', 'type' => 'bool', 'field' => 'mod-editarfotos'],
         	'eliminar' => ['code' => 'moef',   'type' => 'bool', 'field' => 'mod-eliminarfotos'],
         	'eliminar_comentarios' => ['code' => 'moecf',  'type' => 'bool', 'field' => 'mod-eliminarcomfotos'],
         	'papelera' => ['code' => 'morf',   'type' => 'bool', 'field' => 'mod-reficlajefotos'],
         ],
         'muros' => [
            'eliminar_comentarios' => ['code' => 'moecm', 'type' => 'bool', 'field' => 'mod-eliminarcommuro'],
            'eliminar_publicaciones' => ['code' => 'moepm', 'type' => 'bool', 'field' => 'mod-eliminarpubmuro'],
         ],
      ],
      'global' => [
         'posts' => [
            'comentar' => ['code' => 'gopcp',  'type' => 'bool', 'field' => 'global-publicarcomposts'],
            'publicar' => ['code' => 'gopp',   'type' => 'bool', 'field' => 'global-publicarposts'],
            'puntuar' => ['code' => 'godp',   'type' => 'bool', 'field' => 'global-darpuntos'],
            'revisar' => ['code' => 'gorpap', 'type' => 'bool', 'field' => 'global-revisarposts'],
            'votar_negativo' => ['code' => 'govpn',  'type' => 'bool', 'field' => 'global-votarnegapost'],
            'votar_positivo' => ['code' => 'govpp',  'type' => 'bool', 'field' => 'global-votarposipost'],
         ],
         'comentarios' => [
            'editar_propios' => ['code' => 'goepc', 'type' => 'bool', 'field' => 'global-editarpropioscomentarios'],
            'eliminar_propios' => ['code' => 'godpc', 'type' => 'bool', 'field' => 'global-eliminarpropioscomentarios'],
         ],
         'fotos' => [
            'publicar' => ['code' => 'gopf',  'type' => 'bool', 'field' => 'global-publicarfotos'],
            'comentar' => ['code' => 'gopcf', 'type' => 'bool', 'field' => 'global-publicarcomfotos'],
         ],
         'sistema' => [
            'modo_mantenimiento' => ['code' => 'govwm', 'type' => 'bool', 'field' => 'global-vermantenimiento'],
         ],
      ],
      'limites' => [
         'antiflood' => ['code' => 'goaf',  'type' => 'int', 'field' => 'global-antiflood'],
         'puntos_por_post' => ['code' => 'gopfp', 'type' => 'int', 'field' => 'global-pointsforposts'],
         'puntos_por_dia'  => ['code' => 'gopfd', 'type' => 'int', 'field' => 'global-pointsforday'],
      ],
   ];

	public const DEFINITIONS = [
		// Admin / mod
		'suad' => 1, // superadministrador
		'sumo' => 2, // supermoderador

		// permisos para rangos (no full)
		'moacp' => false, 	// Acceso al Panel de Moderacion
		'moadm' => false, 	// Aceptar denuncias de mensajes
		'moayca'=> false, 	// Abrir/Cerrar Posts Ajax
		'moaydcp'=> false, 	// Acciones de revision
		'mocc'  => false,		// Comentarios desaprobados
		'mocdf' => false,		// Cancelar denuncias de fotos
		'mocdm' => false,		// Cancelar denuncias de mensajes
		'mocdp' => false,		// Cancelar denuncias de posts
		'mocdu' => false,		// Cancelar denuncias de usuarios
		'mocepc'=> false,		// Comentarios en Post Cerrado
		'mocp'  => false,		// Posts desaprobados
		'modu'  => false,		// Desbanear Usuarios
		'moecf'=> false, 		// Eliminar Comentarios de Fotos
		'moecm'=> false, 		// Eliminar Comentarios de Muros
		'moecp'=> false, 		// Eliminar Comentarios de Posts
		'moedcopo'=> false, 	// Editar Comentarios de Posts
		'moedfo'=> false,		// Editar Fotos
		'moedpo'=> false,		// Editar Posts
		'moef' => false, 		// Eliminar Fotos
		'moep'  => false, 	// Eliminar Posts
		'moepm'=> false, 		// Eliminar Publicaciones de Muros
		'moop'  => false, 	// Ocultar Posts
		'morf'  => false, 	// Papelera de fotos
		'morp'  => false, 	// Papelera de posts
		'most'  => false, 	// Fijar Posts
		'mosu'  => false, 	// Suspender Usuarios
		'moub'  => false, 	// Usar el buscador
		'movcud'=> false, 	// Ver cuentas desactivadas
		'movcus'=> false, 	// Ver cuentas baneadas
		'movub' => false, 	// Usuarios baneados

		// Globales
		'godp' => false, 	// Puntuar Posts
		'godpc'=> false, 	// Eliminar comentarios propios
		'goepc'=> false, 	// Editar comentarios propios
		'gopcf'=> false, 	// Publicar Comentarios en Fotos
		'gopcp'=> false, 	// Publicar Comentarios en Posts
		'gopf' => false, 	// Publicar Fotos
		'gopp' => false, 	// Publicar Posts
		'gorpap'=> false, // Revisar Posts
		'govpn'=> false, 	// Votar negativo
		'govpp'=> false, 	// Votar postivo
		'govwm'=> false, 	// Acceso en mantenimiento

		// Valores numéricos
		'goaf'  => 0, // Anti-flood
		'gopfd' => 0, // Puntos por dia
		'gopfp' => 0, // Puntos por post
	];

	public static function definitions(): array {
	   $out = [];
	   $walk = function(array $tree) use (&$out, &$walk) {
	      foreach ($tree as $node) {
	        	if (isset($node['code'])) {
	         	$out[$node['code']] = $node['type'] === 'bool' ? false : 0;
	            continue;
	         }
	         $walk($node);
	      }
	   };
	   $walk(self::TREE);
	   return $out;
	}


	public static function resolve(string $path): ?array {
	   // Primero intenta resolver por ruta (como '{categoria}.{subcategoria}.{item}')
	   $node = self::TREE;
	   foreach (explode('.', $path) as $segment) {
	      if (!isset($node[$segment])) {
	         break;
	      }
	      $node = $node[$segment];
	   }
	   // Si encontró el nodo con código, devuélvelo
	   if (isset($node['code'])) {
	      return $node;
	   }
	   // Si no encontró por ruta, busca directamente por código corto (como '{categoria_corta} = gopp')
	   foreach (self::TREE as $category) {
	      $found = self::searchByCode($category, $path);
	      if ($found) {
	         return $found;
	      }
	   }
	   
	   return null;
	}

	private static function searchByCode(array $node, string $code): ?array {
	   foreach ($node as $key => $value) {
	      if (isset($value['code'])) {
	         if ($value['code'] === $code) {
	            return $value;
	         }
	      } else {
	         $result = self::searchByCode($value, $code);
	         if ($result) {
	            return $result;
	         }
	      }
	   }
	   return null;
	}

	/**
	 * Construye el mapa field => [code, type] recorriendo el TREE
	 */
	public static function fieldMap(): array {
	   $map = [];
	   $walk = function(array $tree) use (&$walk, &$map) {
	      foreach ($tree as $node) {
	         if (isset($node['code'], $node['field'])) {
	            $map[$node['field']] = ['code' => $node['code'], 'type' => $node['type']];
	            continue;
	         }
	         if (is_array($node)) $walk($node);
	      }
	   };
	   $walk(self::TREE);
	   return $map;
	}

}
