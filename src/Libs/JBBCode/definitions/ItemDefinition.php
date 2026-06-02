<?php

class ItemDefinition extends JBBCode\CodeDefinition {

	public function __construct(bool $withOption = false) {
		$this->setTagName('item');
		$this->parseContent = true;
		$this->useOption = $withOption;
		$this->nestLimit = -1;
	}

	public function asHtml(JBBCode\ElementNode $el): string {
		$option = $el->getAttribute(); // Esto puede ser string o array

		$attrsHtml = [];
		$iconHtml = '';

		// Interpretar el atributo como clave=valor si es string
		$parsedAttrs = match(true) {
			is_string($option) => $this->parseOptionString($option),
			is_array($option) => $option,
			default => []
		};

		foreach ($parsedAttrs as $attr => $value) {
			if (!in_array($attr, ['class','icon','value','id'])) {
				continue;
			}
			$safeValue = $this->sanitizeAttribute($attr, $value);
			if ($safeValue === null) {
				continue;
			}
			if ($attr === 'icon') {
				$iconHtml = "<span class=\"bbc-icon icon-$safeValue\" aria-hidden=\"true\"></span> ";
				continue;
			}
			$attrsHtml[] = "$attr=\"$safeValue\"";
		}

		$content = '';
		foreach ($el->getChildren() as $child) {
			$content .= $child->getAsHtml();
		}
		$li = (count($attrsHtml) > 0 || !empty($attrsHtml)) ? ' '.implode(' ', $attrsHtml) : "";

		return trim("<li{$li}>$iconHtml$content</li>");
	}

	private function parseOptionString(string $option): array {
		$result = [];
		// Buscar patrones como: class="x" icon="y"
		preg_match_all('/([a-z]+)="([^"]+)"/i', $option, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$result[$match[1]] = $match[2];
		}
		return $result;
	}

	private function sanitizeAttribute(string $attr, string $value): ?string {
		return match($attr) {
			'class' => preg_match('/^[a-zA-Z0-9_\-\s]+$/', $value) ? htmlspecialchars($value, ENT_QUOTES) : null,
			'icon', 'id' => preg_match('/^[a-zA-Z0-9_\-]+$/', $value) ? htmlspecialchars($value, ENT_QUOTES) : null,
			'value' => ctype_digit($value) ? $value : null,
			default => null
		};
	}
}
