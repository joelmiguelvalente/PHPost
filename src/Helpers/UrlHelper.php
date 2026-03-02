<?php

/**
 * @name UrlHelper.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

final class UrlHelper {

	protected tsCore $Core;

	public function __construct(tsCore $Core) {
		$this->Core = $Core;
	}

	public function buildPostUrl(array $data, string $anchor = ''): string {
		$anchor = $anchor ? "/{$anchor}" : '';
		$title = (new Extras)->slugify($data['post_title']);
		return "{$this->Core->settings['url']}/posts/{$data['c_seo']}/{$data['post_id']}/{$title}.html{$anchor}";
	}

	public function buildFotoUrl(array $data, string $anchor = ''): string {
		$anchor = $anchor ? "/{$anchor}" : '';
		$title = (new Extras)->slugify($data['f_title']);
		return "{$this->Core->settings['url']}/fotos/{$data['user_name']}/{$data['foto_id']}/{$title}.html{$anchor}";
	}

	public function buildPerfilUrl(string $data, string $anchor = ''): string {
		$anchor = $anchor ? "/$anchor" : '';
		return "{$this->Core->settings['url']}/@{$data}{$anchor}";
	}
}