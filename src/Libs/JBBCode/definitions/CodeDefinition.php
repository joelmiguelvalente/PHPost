<?php

class CodeDefinition extends JBBCode\CodeDefinition {

	public function __construct(bool $withOption) {
		$this->setTagName('code');
		$this->parseContent = true;
		$this->useOption = $withOption; // Permite [codeblock=php]
		$this->nestLimit = -1;
	}

	public function asHtml(JBBCode\ElementNode $el): string {
		$option = $el->getAttribute();
		// Convertir a string si es array
		if (is_array($option)) {
		  	$option = count($option) > 0 ? reset($option) : '';
		}
		$option = is_string($option) ? $option : '';
		// Validar
		if (!preg_match('/^[a-zA-Z0-9_\\-]*$/', $option)) {
		  	$option = '';
		}
		// Lenguaje: php, js, sql, etc
		$content = '';
		foreach ($el->getChildren() as $child) {
			$content .= $child->getAsHtml();
		}
		$escaped = htmlspecialchars($content, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
		// Limpiar lenguaje
		$lang = '';
		if (is_string($option) && preg_match('/^[a-zA-Z0-9_\-]+$/', $option)) {
			$lang = ' language-' . htmlspecialchars($option, ENT_QUOTES);
		} elseif (is_array($option) && !empty($option)) {
			$first = reset($option);
			if (is_string($first) && preg_match('/^[a-zA-Z0-9_\-]+$/', $first)) {
				$lang = ' language-' . htmlspecialchars($first, ENT_QUOTES);
			}
		}
		// atributo personalizado para controlar carga dinámica
		return "<pre><code class=\"hljs$lang\" data-bbcode-code=\"1\">$escaped</code></pre>";
	}
}
