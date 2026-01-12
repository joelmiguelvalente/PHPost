<?php

/**
 * @package     PHPost
 * @author      Miguel92
 * @copyright   2026
 * @version     2.0.0
 */

declare(strict_types=1);

class Paginator {

	protected int $page;
	protected int $start;

	public function __construct() {
		$this->page  = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
		$this->start = isset($_GET['s']) ? max(0, (int) $_GET['s']) : 0;
	}

	/*
		setPageLimit($tsLimit, $start = false, $tsMax = 0)
	*/
	public function setPageLimit(int $tsLimit, bool $start = false, int $tsMax = 0): string {
		if ($start === false) {
			$offset = ($this->page - 1) * $tsLimit;
		} else {
			$offset = $this->start;
			if ($this->setMaximos($tsLimit, $tsMax)) {
				$offset = 0;
			}
		}

		return $offset . ',' . $tsLimit;
	}

	/*
		setMaximos() :: MAXIMOS EN LAS PAGINAS
	*/
	public function setMaximos(int $tsLimit, int $tsMax): bool {
		if ($tsMax <= 0) {
			return false;
		}

		$current = $this->page * $tsLimit;
		$previous = $current - $tsLimit;

		return ($tsMax < $current && $tsMax < $previous);
	}

	/*
		getPages($tsTotal, $tsLimit)
	*/
	public function getPages(int $tsTotal, int $tsLimit): array {
		$totalPages = (int) ceil($tsTotal / $tsLimit);

		return [
			'current' => $this->page,
			'pages'   => $totalPages,
			'section' => $totalPages + 1,
			'prev'    => max(1, $this->page - 1),
			'next'    => ($this->page < $totalPages) ? $this->page + 1 : $totalPages,
			'max'     => $this->setMaximos($tsLimit, $tsTotal),
		];
	}

	/*
		getPagination($total, $per_page)
	*/
	public function getPagination(int $total, int $per_page = 10): array {
		$numPages = (int) ceil($total / $per_page);

		return [
			'prev'  => ($this->page > 1) ? $this->page - 1 : 0,
			'next'  => ($this->page < $numPages) ? $this->page + 1 : 0,
			'limit' => (($this->page - 1) * $per_page) . ',' . $per_page,
			'total' => $total,
		];
	}

	/**
	 * @param string $base_url
	 * @param int    $start
	 * @param int    $max_value
	 * @param int    $num_per_page
	 * @param bool   $flexible_start
	 *
	 * @return string
	 */
	public function pageIndex(string $base_url, int &$start, int $max_value, int $num_per_page, bool $flexible_start = false) {
	   // Limpieza de la URL base
	   $base_url = explode('&s=', $base_url, 2)[0];
	   // Normalización del start
	   $startInvalid = $start < 0;
	   $start = max(0, $start);
	   if ($start >= $max_value) {
	      $start = max(0, $max_value - ($max_value % $num_per_page ?: $num_per_page));
	   }
	   // Asegurar múltiplo del límite
	   $start -= $start % $num_per_page;
	   // Configuración visual
	   $range = 5;
	   $halfRange = intdiv($range, 2);
	   // Template de link
	   $linkTpl = '<a class="navPages" href="%s">%s</a> ';
	   $buildLink = function (int $offset, int $page) use ($base_url, $flexible_start, $linkTpl) {
	      $url = $flexible_start ? $base_url : sprintf('%s&s=%d', $base_url, $offset);
	      return sprintf($linkTpl, htmlspecialchars($url), $page);
	   };
	   $html = '';
	   $currentPage = (int) ($start / $num_per_page) + 1;
	   $lastOffset  = (int) (floor(($max_value - 1) / $num_per_page) * $num_per_page);
	   // Primera página
	   if ($start > $num_per_page * $halfRange) {
	      $html .= $buildLink(0, 1);
	   }
	   // Ellipsis inicial
	   if ($start > $num_per_page * ($halfRange + 1)) {
	      $html .= '<b> ... </b>';
	   }
	   // Páginas anteriores
	   for ($i = $halfRange; $i >= 1; $i--) {
	      $offset = $start - $num_per_page * $i;
	      if ($offset >= 0) {
	         $html .= $buildLink($offset, ($offset / $num_per_page) + 1);
	      }
	   }
	   // Página actual
	   if (!$startInvalid) {
	      $html .= '[<b>' . $currentPage . '</b>] ';
	   } else {
	      $html .= $buildLink($start, $currentPage);
	   }
	   // Páginas siguientes
	   for ($i = 1; $i <= $halfRange; $i++) {
	      $offset = $start + $num_per_page * $i;
	      if ($offset <= $lastOffset) {
	         $html .= $buildLink($offset, ($offset / $num_per_page) + 1);
	      }
	   }
	   // Ellipsis final
	   if ($start + $num_per_page * ($halfRange + 1) < $lastOffset) {
	      $html .= '<b> ... </b>';
	   }
	   // Última página
	   if ($start + $num_per_page * $halfRange < $lastOffset) {
	      $html .= $buildLink($lastOffset, ($lastOffset / $num_per_page) + 1);
	   }
	   return $html;
	}

}