<?php

require_once dirname(__DIR__, 1) . '/app.php';
header('Content-Type: application/json; charset=UTF-8');
$name = "video_platforms";

// Sentencia
$sentence = DB::exists("SHOW TABLES LIKE 'w_video_platforms'");

$plataformas = [
	[
		'name' => 'youtube', 
		'domain' => 'youtube.com', 
		'path_pattern' => '/watch?v={id}',
		'embed_template' => '<iframe width="640" height="360" src="https://www.youtube.com/embed/{id}" frameborder="0" allowfullscreen></iframe>', 
		'enabled' => 1
	],[
		'name' => 'youtube_short', 
		'domain' => 'youtu.be', 
		'path_pattern' => '/{id}',
		'embed_template' => '<iframe width="640" height="360" src="https://www.youtube.com/embed/{id}" frameborder="0" allowfullscreen></iframe>', 
		'enabled' => 1
	],[
		'name' => 'vimeo', 
		'domain' => 'vimeo.com', 
		'path_pattern' => '/{id}',
		'embed_template' => '<iframe width="640" height="360" src="https://player.vimeo.com/video/{id}" frameborder="0" allowfullscreen></iframe>', 
		'enabled' => 1
	],[
		'name' => 'dailymotion', 
		'domain' => 'dailymotion.com', 
		'path_pattern' => '/video/{id}',
		'embed_template' => '<iframe frameborder="0" width="640" height="360" src="https://www.dailymotion.com/embed/video/{id}" allowfullscreen></iframe>', 
		'enabled' => 1
	],[
		'name' => 'tiktok', 
		'domain' => 'tiktok.com', 
		'path_pattern' => '/@{username}/video/{id}',
		'embed_template' => '<blockquote class="tiktok-embed" cite="{url}"><a href="{url}"></a></blockquote><script async src="https://www.tiktok.com/embed.js"></script>', 
		'enabled' => 0
	],[
		'name' => 'twitch', 
		'domain' => 'twitch.com', 
		'path_pattern' => '/videos/{id}',
		'embed_template' => '<iframe src="https://player.twitch.tv/?video={id}&parent=localhost&autoplay=false" width="640" height="360" frameborder="0" allowfullscreen></iframe>', 
		'enabled' => 0
	],[
		'name' => 'instagram', 
		'domain' => 'instagram.com', 
		'path_pattern' => '/p/{id}',
		'embed_template' => '<blockquote class="instagram-media" data-instgrm-permalink="{url}"><a href="{url}"></a></blockquote><script async defer src="//www.instagram.com/embed.js"></script>', 
		'enabled' => 0
	],[
		'name' => 'streamable', 
		'domain' => 'streamable.com', 
		'path_pattern' => '/p/{id}',
		'embed_template' => '<iframe src="https://streamable.com/e/{id}" width="640" height="360" frameborder="0" allowfullscreen></iframe>', 
		'enabled' => 0
	]
];

$id = 1;
$continue = true;

if($sentence) {
   echo json_encode([
		'success' => false, 
		'message' => $name . ' ya fue migrado'
   ]);
   exit;
} else {
	$create = "CREATE TABLE w_video_platforms (
		id INT AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(100) NOT NULL UNIQUE,
		domain VARCHAR(255) NOT NULL,
		path_pattern TEXT NOT NULL,
		embed_template TEXT NOT NULL,
		enabled BOOLEAN DEFAULT TRUE,
		created_at INT NOT NULL DEFAULT 0,
		updated_at INT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	
	if(!DB::query($create)) {
		echo json_encode([
		   'success' => false, 
		   'message' => 'No se pudo crear la tabla'
		]);
		exit;
	}

	foreach($plataformas as $pid => $plataforma) {
		$plataforma = ['id' => $id, ...$plataforma, 'created_at' => time()];
		if(!DB::insert('w_video_platforms', $plataforma)) {
			$continue = false;
		}
		$id++;
	}

	if(!$continue) {
		echo json_encode([
		   'success' => false, 
		   'message' => 'Hubo un error al insertar los datos'
		]);
		exit;
	}
	if(DB::insert('w_migrations', [ 'migration' => $name,  'executed_at' => $time ])) {
	   echo json_encode([
	      'success' => true, 
	      'message' => $name . ' migrado'
	   ]);
	   exit;
	}
}