<?php

declare(strict_types=1);

/**
 * @package    PHPost/Extras
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once TS_LIBS . '/JBBCode/Parser.php';
require_once TS_LIBS . '/JBBCode/InputValidator.php';
$folders = ['validators', 'definitions'];
foreach($folders as $folder) {
	$folder = TS_LIBS . '/JBBCode/' . $folder . '/';
	$iterator = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
	foreach ($iterator as $item) {
		if ($item->isFile()) {
			require_once $folder . $item->getFilename();
		}
	}
}

/**
 * Clase responsable de la conversión de texto en formato
 * de marcado BBCode a XHTML para creración de contenido
 * usado en posts, fotos, comentarios, etc.
 *
 * Extiende de la clase jBBCode para facilitar el uso de
 * todas las herramientas disponibles para la conversión
 * además de su excelente seguridad para el script.
 *
 * @author PHPost.
 */
class BBCode {

	public $id;

	public $route;

	/**
	 * String texto BBcode
	 */
	private $text;

	/**
	 * BBCodes permitidos
	 */
	private $restriction;

	/**
	 * jBBCode
	 */
	private $parser;

	public function __construct() {
		$this->restriction = [];
		$this->parser = new JBBCode\Parser();
	}

	/**
	 * Prepara el texto con el que se trabajará
	 *
	 * @param string $text  texto a parsear
	 */
	public function setText($text) {
		$this->text = $text;
		$this->unclosedTags();
	}

	public function bbcodeAllow(): array {
		return [
			'url', 'b', 'i', 'u', 's', 'font', 'size', 'color', 
			'align', 'spoiler', 'code', 'quote', 'video', 
			'sub', 'sup', 'image', 'item', 'list', 'kbd',
			'table', 'thead', 'tbody', 'th', 'td', 'tr',  
			'notice', 'info', 'warning', 'error', 'success', 
			'highlight', 'mention', 'badge', 'diff', 'divider'
		];
	}

	/**
	 * Modificar restricciones de BBCode
	 *
	 * @param Array string $restriccion  lista de tags permitidos
	 */
	public function setRestriction(array $bbcodes = []) {
		$this->restriction = $bbcodes;
		
		$this->addBBcodes();
	}

	/**
	 * Elimina etiquetas BBcode y deja el texto plano
	 *
	 * @return string
	 */
	public function getAsText() {
		$this->parser->parse($this->text);
		$this->text = $this->parser->getAsText();
		$this->delExtraTags();
		return htmlspecialchars_decode(strip_tags($this->text));
	}

	/**
	 * Obtiene el texto en HTML
	 *
	 * @return string
	 */
	public function getAsHtml() {
		$this->parser->parse($this->text);
		$this->text = $this->parser->getAsHtml();
		$this->setExtraTags();
		return nl2br($this->text);
		#return $this->text;
	}

	/**
	 * Fix para tags que no tienen etiqueta de cierre
	 * y para tags de YouTube de la versión anterior
	 */
	private function unclosedTags() {
		$this->text = preg_replace("/[\.com]+\/v\//i", ".com/watch?v=", $this->text);
		$this->text = preg_replace("/\[swf=(http|https)?(\:\/\/)?www\.youtube\.com\/watch\?v([A-z0-9=\-]+?)\]/i", "[video]$1$2www.youtube.com/watch?v$3[/video]", $this->text);

		$this->text = preg_replace("/\[swf\=(.+?)\]/i", "[swf]$1[/swf]", $this->text);
      $this->text = preg_replace("/\[hr\]/i", "[divider]", $this->text);
      $this->text = preg_replace("/\[img\=(.+?)\]/i", "[image]$1[/image]", $this->text);
      $this->text = preg_replace("/\[img\](.+?)\[\/img\]/i", "[image]$1[/image]", $this->text);

		$this->text = str_replace('&#039;', '\'', $this->text);
	}

	/**
	 * Parsea tag de línea de división
	 * saltos de línea
	 */
	private function setExtraTags() {
		if (in_array('divider', $this->restriction)) {
			$this->text = str_replace('[divider]', '<hr />', $this->text);
		}
		$this->text = str_replace('\n', '<br />', $this->text);
	}

	/**
	 * Elimina tag de línea de división
	 * saltos de línea
	 * espacios vacíos
	 */
	private function delExtraTags() {
		$this->text = str_replace(array('[divider]', '\n', '\r'), ' ', $this->text);
		$this->text = preg_replace('!\s+!', ' ', $this->text);
		$this->text = preg_replace('/((http|https|www)[^\s]+)/', '', $this->text);
	}

	private function registerBBCodes() {
		$args = [[true], [false]];
		$codeConfigs = [
   	   'item' => [ 'class' => ItemDefinition::class, 'args' => $args ],
   	   'list' => [ 'class' => ListDefinition::class, 'args' => $args ],
   	   'code' => [ 'class' => CodeDefinition::class, 'args' => $args ],
   	   'image' => [
   	      'class' => ImageDefinition::class,
   	      'args' => [ [true,  $this->route, $this->id], [false, $this->route, $this->id] ],
   	   ],
   	];

   	foreach ($codeConfigs as $codeName => $config) {
   	   if (in_array($codeName, $this->restriction) || !$this->restriction) {
   	      foreach ($config['args'] as $args) {
   	         $this->parser->addCodeDefinition(new $config['class'](...$args));
   	      }
   	   }
   	}
		if (in_array('video', $this->restriction) || !$this->restriction) {
			$this->parser->addCodeDefinition(new VideoDefinition());
		}
	}

	private function getBasicTags(): array {
	   return [
	      'b'      => '<strong>{param}</strong>',
	      'i'      => '<em>{param}</em>',
	      's'      => '<s>{param}</s>',
	      'sub'    => '<sub>{param}</sub>',
	      'sup'    => '<sup>{param}</sup>',
	      'u'      => '<u>{param}</u>',
	      'table'  => '<table class="bbc-table">{param}</table>',
	      'thead'  => '<thead class="bbc-thead">{param}</thead>',
	      'tbody'  => '<tbody class="bbc-tbody">{param}</tbody>',
	      'th'     => '<th class="bbc-th">{param}</th>',
	      'td'     => '<td class="bbc-td">{param}</td>',
	      'tr'     => '<tr class="bbc-tr">{param}</tr>',
	      'error'  => '<div class="bbc-note error">{param}</div>',
	      'info'   => '<div class="bbc-note info">{param}</div>',
	      'notice' => '<div class="bbc-note notice">{param}</div>',
	      'success'=> '<div class="bbc-note success">{param}</div>',
	      'warning'=> '<div class="bbc-note warning">{param}</div>',
	      'mention'=> '<span class="bbc-mention">{param}</span>',
	      'diff'	=> '<div class="bbc-diff">{param}</div>',
	      'quote'  => '<blockquote><div class="cita">Cita:</div><div class="citacuerpo">{param}</div></blockquote>',
	      'spoiler'=> '<div class="spoiler"><div class="title"><button onclick="spoiler($(this))">Spoiler:</button></div><div class="body">{param}</div></div>',
	   ];
	}
	private function getAdvancedTags(array $customValidators): array {
	   return [
	      // Spoiler
	      [
	         'tag' => 'spoiler',
	         'replace' => '<div class="spoiler"><div class="title" contenteditable="false"><a href="#" onclick="spoiler($(this)); return false;">{option}</a></div><div class="body">{param}</div></div>',
	         'option' => true,
	      ], [
	         'tag' => 'spoiler',
	         'replace' => '<div class="spoiler"><div class="title" contenteditable="false"><a href="#" onclick="spoiler($(this)); return false;">Spoiler:</a></div><div class="body">{param}</div></div>',
	         'parse' => true,
	      ],
	      // TD con colspan
	      [
	         'tag' => 'td',
	         'replace' => '<td colspan="{option}" class="bbc-td">{param}</td>',
	         'option' => true,
	      ],
	      // KBD sin parse
	      [
	         'tag' => 'kbd',
	         'replace' => '<kbd class="slug">{param}</kbd>',
	         'parse' => false,
	         'limit' => 1,
	      ],
	      // Highlight
	      [
	         'tag' => 'highlight',
	         'replace' => '<mark class="bbc-highlight">{param}</mark>',
	         'parse' => false,
	         'limit' => 1,
	      ],
	      [
	         'tag' => 'highlight',
	         'replace' => '<mark class="bbc-highlight" data-color="{option}">{param}</mark>',
	         'option' => true,
	      ],
	      // Quote con opción
	      [
	         'tag' => 'quote',
	         'replace' => '<blockquote><div class="cita">{option} dijo:</div><div class="citacuerpo">{param}</div></blockquote>',
	         'option' => true,
	      ],
	      // URL con validación
	      [
	         'tag' => 'url',
	         'replace' => '<a href="{param}" target="_blank">{param}</a>',
	         'parse' => false,
	         'validParam' => $customValidators['url']
	      ],
	      [
	         'tag' => 'url',
	         'replace' => '<a href="{option}" target="_blank">{param}</a>',
	         'option' => true,
	         'validOption' => $customValidators['url']
	      ],
	      // Tags con validadores
	      [
	      	'tag' => 'color', 
	      	'replace' => '<span style="color: {option}">{param}</span>', 
	      	'option' => true, 
	      	'validOption' => $customValidators['color']
	      ], [
	         'tag' => 'size',
	         'replace' => '<span style="font-size: {option}pt; line-height: {option}pt">{param}</span>',
	         'option' => true,
	         'validOption' => $customValidators['size'],
	      ],
	      [
	         'tag' => 'align',
	         'replace' => '<div style="text-align: {option}">{param}</div>',
	         'option' => true,
	         'validOption' => $customValidators['align'],
	      ],
	      [
	         'tag' => 'font',
	         'replace' => '<span style="font-family: {option}">{param}</span>',
	         'option' => true,
	         'validOption' => $customValidators['font'],
	      ],
	   ];
	}

	/**
	 * Agrega y valida los BBcodes a parsear.
	 *
	 * Si el bbcode se encuentra en el array de la restricción, será permitido.
	 * Si no es válido lo que se pasa por parametro o contenido se verá el bbcode
	 * sin ser parseado. Ejemplo: [a]no es link[/a] => [a]no es link[/a]
	 *
	 * Cada bbcode tiene su configuración de:
	 *
	 * TagName: Nombre del tag de bbcode.
	 * Replace: En qué formato HTML se reemplazará.
	 *          Usar como variables de referencia {option} y {param}.
	 * UseOption: Si el tag usa parámetro ({option}).
	 * ParseContent: Si el contenido del tag también será parseado.
	 * NestLimit: Límite de cuantas veces se repite este tag en su contenido (incluyendose).
	 * OptionValidator: Clase con la cual se valida lo que se pasa por parámetro.
	 * BodyValidator: Clase con la cual se valida lo que se pasa como contenido del tag.
	 */
	public function addBBcodes() {
		$this->registerBBCodes();

		// Validadores únicos
	   $customValidators = [
	      'size'  => new \JBBCode\validators\SizeValidator(),
	      'align' => new \JBBCode\validators\AlignValidator(),
	      'font'  => new \JBBCode\validators\FontValidator(),
	      'color'  => new \JBBCode\validators\CssColorValidator(),
	      'url'  => new \JBBCode\validators\UrlValidator(),
	   ];
	   // Combinar todos los tags
	   $allTags = [];

		// Añadir básicos como array de definiciones
	   foreach ($this->getBasicTags() as $tag => $replace) {
	      $allTags[] = ['tag' => $tag, 'replace' => $replace];
	   }
	   // Añadir avanzados
	   $allTags = array_merge($allTags, $this->getAdvancedTags($customValidators));

	   // Registrar solo los permitidos
	   foreach ($allTags as $bbcode) {
	      if ($this->isAllowed($bbcode['tag'])) {
	         $this->addSingleBBCode($bbcode, $customValidators);
	      }
	   }
	}

	/**
	 * Verifica si un BBCode está permitido según restricciones
	 */
	private function isAllowed(string $tag): bool {
	   return !$this->restriction || in_array($tag, $this->restriction, true);
	}

	/**
	 * Añade un único BBCode al parser con sus configuraciones
	 */
	private function addSingleBBCode(array $config, array $validators): void {
	   $defaults = [
	      'option' => false,
	      'parse' => true,
	      'limit' => -1,
	      'validOption' => null,
	      'validParam' => null,
	   ];
	   $config = array_merge($defaults, $config);
	   $this->parser->addBBCode(
	      $config['tag'],
	      $config['replace'],
	      $config['option'],
	      $config['parse'],
	      $config['limit'],
	      $config['validOption'],
	      $config['validParam']
	   );
	}
	
	/**
	 * @name parseMentions
	 * @access public
	 * @param string
	 * @return string
	 * @info PONE LOS LINKS A LOS MENCIONADOS
	 */
	public function parseMentions() {
	   global $tsUser;
	   $founds = [];
	   $this->text .= ' ';
	   preg_match_all('/\B@([a-zA-Z0-9_-]{4,16}+)\b/', $this->text, $users);
	   foreach ($users[1] as $user) {
	      if (!in_array($user, $founds)) {
	         $uid = $tsUser->getUserID($user);
	         if (!empty($uid)) {
	            $find = '@' . $user . ' ';
	            $replace = '@<a href="' . $this->route . '/perfil/' . $user . '" class="hovercard" uid="' . $uid . '">' . $user . '</a> ';
	            $this->text = str_replace($find, $replace, $this->text);
	         }
	         $founds[] = $user;
	      }
	   }
	   $this->text = substr($this->text, 0, -1);
	}
	
   /**
	* @name parseSmiles()
	* @access public
	* @description Convierte los Smiles
	*/
	public function parseSmiles() {
		global $tsCore;
		// SMILEYS
		$bbcode = [];
		$html = [];
	  	$smiles = [
			":poop:" => "1f4a9",
			":goblin:" => "1f47a",
			":ghost:" => "1f47b",
			":alien:" => "1f47d",
			":imp:" => "1f47f",
			":blush:" => "1f60a",
			":yum:" => "1f60b",
			":relieved:" => "1f60c",
			":heart_eyes:" => "1f60d",
			":sunglasses:" => "1f60e",
			":smirk:" => "1f60f",
			":kissing_closed_eyes:" => "1f61a",
			":stuck_out_tongue:" => "1f61b",
			":stuck_out_tongue_winking_eye:" => "1f61c",
			":stuck_out_tongue_closed_eyes:" => "1f61d",
			":pensive:" => "1f61e",
			":worried:" => "1f61f",
			":sleepy:" => "1f62a",
			":tired_face:" => "1f62b",
			":grimacing:" => "1f62c",
			":sob:" => "1f62d",
			":open_mouth:" => "1f62e",
			":hushed:" => "1f62f",
			":smiley_cat:" => "1f63a",
			":smile_cat:" => "1f63b",
			":heart_eyes_cat:" => "1f63c",
			":kissing_cat:" => "1f63d",
			":pouting_cat:" => "1f63e",
			":crying_cat:" => "1f63f",
			":ogre:" => "1f479",
			":skull:" => "1f480",
			":grinning:" => "1f600",
			":grin:" => "1f601",
			":joy:" => "1f602",
			":smiley:" => "1f603",
			":smile:" => "1f604",
			":sweat_smile:" => "1f605",
			":laughing:" => "1f606",
			":innocent:" => "1f607",
			":smiling_imp:" => "1f608",
			":wink:" => "1f609",
			":neutral_face:" => "1f610",
			":expressionless:" => "1f611",
			":unamused:" => "1f612",
			":sweat:" => "1f613",
			":pensive:" => "1f614",
			":confused:" => "1f615",
			":confounded:" => "1f616",
			":kissing:" => "1f617",
			":kissing_heart:" => "1f618",
			":kissing_smiling_eyes:" => "1f619",
			":angry:" => "1f620",
			":rage:" => "1f621",
			":cry:" => "1f622",
			":persevere:" => "1f623",
			":triumph:" => "1f624",
			":disappointed_relieved:" => "1f625",
			":frowning:" => "1f626",
			":anguished:" => "1f627",
			":fearful:" => "1f628",
			":weary:" => "1f629",
			":cold_sweat:" => "1f630",
			":scream:" => "1f631",
			":astonished:" => "1f632",
			":flushed:" => "1f633",
			":sleeping:" => "1f634",
			":dizzy_face:" => "1f635",
			":no_mouth:" => "1f636",
			":mask:" => "1f637",
			":smile_cat:" => "1f638",
			":joy_cat:" => "1f639",
			":scream_cat:" => "1f640",
			":slight_frown:" => "1f641",
			":slight_smile:" => "1f642",
			":upside_down_face:" => "1f643",
			":rolling_eyes:" => "1f644",
			":zipper_mouth_face:" => "1f910",
			":money_mouth_face:" => "1f911",
			":face_with_thermometer:" => "1f912",
			":nerd_face:" => "1f913",
			":thinking_face:" => "1f914",
			":face_with_head_bandage:" => "1f915",
			":robot_face:" => "1f916",
			":hugging_face:" => "1f917",
			":cowboy_hat_face:" => "1f920",
			":clown_face:" => "1f921",
			":nauseated_face:" => "1f922",
			":rofl:" => "1f923",
			":drooling_face:" => "1f924",
			":lying_face:" => "1f925",
			":face_palm:" => "1f926",
			":sneezing_face:" => "1f927",
			":star_struck:" => "1f929",
			":smiling_face_with_3_hearts:" => "1f970",
			":relaxed:" => "263a",
			":frowning_face:" => "2639"
	  	];
	  	foreach($smiles as $name => $image) {
			$bbcode[] = $name; 
			$html[] = "<img src=\"{$this->route}/smiles/$image.png\" alt=\"Smile {$name}\" width=\"16\" height=\"16\" />";
	  	}
		// REEMPLAZAMOS SMILEYS
	  	$this->text = str_replace($bbcode, $html, $this->text);
	}    
}
