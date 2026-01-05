<?php

use Smarty\Extension\Base;

require __DIR__ . '/getUrlModifierCompiler.php';
require __DIR__ . '/HumanModifierCompiler.php';
require __DIR__ . '/Nl2brModifierCompiler.php';
require __DIR__ . '/QuotModifierCompiler.php';
require __DIR__ . '/SeoModifierCompiler.php';
require __DIR__ . '/TrimModifierCompiler.php';

class SmartyExtensiones extends Base {

	public function getModifierCompiler(string $modifier): ?\Smarty\Compile\Modifier\ModifierCompilerInterface {

		return match ($modifier) {
			'getUrl' => new getUrlModifierCompiler(),
			'human' => new HumanModifierCompiler(),
			'nl2br' => new Nl2brModifierCompiler(),
			'quot' => new QuotModifierCompiler(),
			'seo' => new SeoModifierCompiler(),
			'trim' => new TrimModifierCompiler(),
			default => null
		};

	}

}