<?php
/**
 * @name c.buscador.php
 * @author PHPost Team
 * @copyright 2026
 */
declare(strict_types=1);

if (!defined('TS_HEADER')) {
    exit('No se permite el acceso directo al script');
}

class tsBuscador {

   private const POSTS_PER_PAGE = 12;
   private const VALID_ENGINES  = ['web', 'tags'];
   private const SELECT_FIELDS  = 'p.post_id, p.post_user, p.post_category, p.post_title, p.post_date, p.post_comments, p.post_favoritos, p.post_puntos, u.user_name, c.c_seo, c.c_nombre, c.c_img';

   public function __construct(
      protected tsCore $Core,
      protected tsUser $User
   ) {}

   private function getParamsSearching(): array {
      $engine = trim($_GET['engine'] ?? 'web');

      return [
         'query'  => trim($_GET['query'] ?? ''),
         'category'    => max(0, (int)($_GET['category'] ?? 0)),
         'autor'  => trim($_GET['autor'] ?? ''),
         'engine' => in_array($engine, self::VALID_ENGINES, true) ? $engine : 'web',
      ];
   }

   public function getTypeFilter(): array {
      $params = $this->getParamsSearching();

      return $params + [
         'where' => $params['category'] > 0 ? 'AND p.post_category = ' . $params['category'] : '',
         'on'    => $params['engine'] === 'tags' ? 'p.post_tags' : 'p.post_title',
      ];
   }

   public function getQuery(): array {
      $filter  = $this->getTypeFilter();
      $query   = $filter['query'];
      $wSearch = '';
      $wAutor  = '';

      // Manejo del autor
      if (!empty($filter['autor'])) {
         $aid = (int) $this->User->getUserID($filter['autor']);

         if ($aid > 0) {
            if (empty($query)) {
               $wSearch = 'AND p.post_user = ' . $aid;
            } else {
               $wAutor  = 'AND p.post_user = ' . $aid;
            }
         }
      }
      // FULLTEXT solo si hay término de búsqueda y no ya asignado a wSearch
      if (!empty($query) && empty($wSearch)) {
         $safeQuery = DB::escape($query); // ← usa el escape de tu capa DB
         $wSearch   = "AND MATCH({$filter['on']}) AGAINST('{$safeQuery}' IN BOOLEAN MODE)";
      }
      $baseWhere = "WHERE p.post_status = 0 {$filter['where']} {$wAutor} {$wSearch}";
      $orderBy   = 'ORDER BY p.post_date DESC';
      // Total para paginación
      $total     = (int) DB::value("SELECT COUNT(p.post_id) FROM p_posts AS p {$baseWhere}");
      $pages     = (new Paginator)->getPagination($total, self::POSTS_PER_PAGE);

      // Resultados paginados
      $rows = DB::fetchAll("SELECT " . self::SELECT_FIELDS . " FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id  = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category {$baseWhere} {$orderBy} LIMIT {$pages['limit']}");
      // Número de resultados en la página actual
      [$offset] = explode(',', $pages['limit']);
      $pageCount = (int)$offset + count($rows);
      return [
         'pages' => $pages,
         'data'  => $rows,
         'total' => $pageCount,
      ];
    }
}