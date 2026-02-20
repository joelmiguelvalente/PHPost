<?php

class VideoDefinition extends JBBCode\CodeDefinition {

	private $platforms = [
		'youtube' => [
			'pattern' => '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/',
			'template' => '<lite-youtube loading="lazy" videoid="%s" style="width:"640px;height:390px;background-image: url(\'https://i.ytimg.com/vi/%s/maxresdefault.jpg\');"></lite-youtube>'
		],
		'vimeo' => [
			'pattern' => '/vimeo\.com\/(\d{6,10})/',
			'template' => '<div class="video-container"><iframe src="https://player.vimeo.com/video/%s" width="640" height="360" frameborder="0" allowfullscreen></iframe></div>',
		],
		'dailymotion' => [
			'pattern' => '/dailymotion\.com\/video\/([a-zA-Z0-9]+)/',
			'template' => '<div class="video-container"><iframe frameborder="0" width="640" height="360" src="https://www.dailymotion.com/embed/video/%s?autoplay=0" allow="autoplay" allowfullscreen></iframe></div>',
		],
		'tiktok' => [
			'pattern' => '/tiktok\.com\/@.*\/video\/(\d+)/',
			'template' => '<blockquote class="tiktok-embed" cite="%s"><a href="%s"></a></blockquote><script async src="https://www.tiktok.com/embed.js"></script>',
		]
	];

	public function __construct() {
		$this->setTagName("video");
      $this->parseContent = true;
		$this->useOption = false;
      $this->nestLimit = -1;
	}

	public function asHtml(JBBCode\ElementNode $el) {
		$content = "";
		foreach ($el->getChildren() as $child) {
			$content .= $child->getAsBBCode();
		}
		
		// Limpiar la URL
		$content = trim($content);
		
		// Verificar cada plataforma
		foreach ($this->platforms as $platform => $config) {
			if (preg_match($config['pattern'], $content, $matches)) {
				$videoId = $matches[1];
				
				// Para TikTok usamos la URL completa en lugar del ID
				if ($platform === 'tiktok') {
					return sprintf($config['template'], $content, $content);
				} elseif ($platform === 'youtube') {
					return sprintf($config['template'], $videoId, $videoId);
				} else {
					return sprintf($config['template'], $videoId);
				}
			}
		}
		
		// Si no coincide con ninguna plataforma, devolver el contenido original
		return $el->getAsBBCode();
	}
}