<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install
 * @author     Miguel92
 * @copyright  2026
*/

$phpost_sql["Create Table: f_comentarios"] = "CREATE TABLE IF NOT EXISTS `f_comentarios` (
    `cid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `c_foto_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_user` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_update` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_body` TEXT,
    `c_ip` VARBINARY(16) DEFAULT NULL,
    `c_status` TINYINT NOT NULL DEFAULT 1,
    INDEX `idx_foto_user` (`c_foto_id`, `c_user`),
    INDEX `idx_date` (`c_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: f_favoritos"] = "CREATE TABLE IF NOT EXISTS `f_favoritos` (
    `fid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `f_foto_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_user` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_date` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_fav` (`f_foto_id`, `f_user`),
    INDEX `idx_user` (`f_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: f_fotos"] = "CREATE TABLE IF NOT EXISTS `f_fotos` (
    `foto_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `f_album` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_title` VARCHAR(120) NOT NULL DEFAULT '',
    `f_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_description` TEXT,
    `f_url` VARCHAR(255) NOT NULL DEFAULT '',
    `f_user` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_closed` TINYINT NOT NULL DEFAULT 0,
    `f_visitas` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_status` TINYINT NOT NULL DEFAULT 0,
    `f_last` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_hits` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_ip` VARBINARY(16) DEFAULT NULL,
    `f_created_at` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_user_status` (`f_user`, `f_status`),
    INDEX `idx_album_date` (`f_album`, `f_date`),
    INDEX `idx_date_status` (`f_date`, `f_status`),
    FULLTEXT INDEX `ft_title_description` (`f_title`, `f_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: f_votos"] = "CREATE TABLE IF NOT EXISTS `f_votos` (
    `vid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `v_foto_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `v_user` INT UNSIGNED NOT NULL DEFAULT 0,
    `v_vote` TINYINT NOT NULL DEFAULT 0 COMMENT '1=positivo, -1=negativo',
    `v_date` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_vote` (`v_foto_id`, `v_user`),
    INDEX `idx_user_date` (`v_user`, `v_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: f_album"] = "CREATE TABLE IF NOT EXISTS `f_album` (
    `aid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `a_name` VARCHAR(80) NOT NULL DEFAULT '',
    `a_cover` VARCHAR(255) NOT NULL DEFAULT '',
    `a_description` VARCHAR(255) DEFAULT NULL,
    `a_status` TINYINT NOT NULL DEFAULT 0,
    `a_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `a_user` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_user_status` (`a_user`, `a_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: p_borradores"] = "CREATE TABLE IF NOT EXISTS `p_borradores` (
    `bid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `b_post_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `b_user` INT UNSIGNED NOT NULL DEFAULT 0,
    `b_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `b_update` INT UNSIGNED NOT NULL DEFAULT 0,
    `b_title` VARCHAR(200) DEFAULT '',
    `b_portada` VARCHAR(255) NOT NULL DEFAULT '',
    `b_body` LONGTEXT,
    `b_tags` VARCHAR(200) NOT NULL DEFAULT '',
    `b_category` INT UNSIGNED NOT NULL DEFAULT 0,
    `b_private` TINYINT NOT NULL DEFAULT 0,
    `b_block_comments` TINYINT NOT NULL DEFAULT 0,
    `b_sponsored` TINYINT NOT NULL DEFAULT 0,
    `b_sticky` TINYINT NOT NULL DEFAULT 0,
    `b_smileys` TINYINT NOT NULL DEFAULT 0,
    `b_visitantes` TINYINT NOT NULL DEFAULT 0,
    `b_status` TINYINT NOT NULL DEFAULT 0,
    `b_causa` VARCHAR(128) NOT NULL DEFAULT '',
    `b_fuentes` TEXT,
    `b_ip` VARBINARY(16) DEFAULT NULL,
    FULLTEXT INDEX `ft_title_body` (`b_title`, `b_body`),
    INDEX `idx_user_category` (`b_user`, `b_category`),
    INDEX `idx_status_date` (`b_status`, `b_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: p_categorias"] = "CREATE TABLE IF NOT EXISTS `p_categorias` (
    `cid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `c_orden` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_nombre` VARCHAR(90) NOT NULL DEFAULT '',
    `c_seo` VARCHAR(90) NOT NULL DEFAULT '',
    `c_img` VARCHAR(60) NOT NULL DEFAULT '',
    `c_color` CHAR(12) NOT NULL DEFAULT '',
    `c_privada` TINYINT NOT NULL DEFAULT 0,
    `c_posts_count` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_orden` (`c_orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Insert Data: p_categorias"] = "INSERT INTO `p_categorias` (`cid`, `c_orden`, `c_nombre`, `c_seo`, `c_img`) VALUES
(1, 1, 'Inteligencia Artificial', 'inteligencia-artificial', 'ai.png'),
(2, 2, 'Ciberseguridad', 'ciberseguridad', 'cyber.png'),
(3, 3, 'Criptomonedas y Web3', 'criptomonedas-web3', 'crypto.png'),
(4, 4, 'Videojuegos', 'videojuegos', 'gaming.png'),
(5, 5, 'Streaming y Contenido', 'streaming-contenido', 'stream.png'),
(6, 6, 'Programación y Desarrollo', 'programacion-desarrollo', 'code.png'),
(7, 7, 'Ciencia y Tecnología', 'ciencia-tecnologia', 'science.png'),
(8, 8, 'Marketing Digital', 'marketing-digital', 'marketing.png'),
(9, 9, 'Emprendimiento', 'emprendimiento', 'rocket.png'),
(10, 10, 'Finanzas Personales', 'finanzas-personales', 'finance.png'),
(11, 11, 'Salud y Bienestar', 'salud-bienestar', 'health.png'),
(12, 12, 'Nutrición y Fitness', 'nutricion-fitness', 'fitness.png'),
(13, 13, 'Desarrollo Personal', 'desarrollo-personal', 'growth.png'),
(14, 14, 'Psicología y Mente', 'psicologia-mente', 'brain.png'),
(15, 15, 'Viajes y Aventura', 'viajes-aventura', 'travel.png'),
(16, 16, 'Gastronomía', 'gastronomia', 'food.png'),
(17, 17, 'Cine y Series', 'cine-series', 'cinema.png'),
(18, 18, 'Música', 'musica', 'music.png'),
(19, 19, 'Arte y Creatividad', 'arte-creatividad', 'art.png'),
(20, 20, 'Diseño y UX', 'diseno-ux', 'design.png'),
(21, 21, 'Fotografía', 'fotografia', 'camera.png'),
(22, 22, 'Moda y Estilo', 'moda-estilo', 'fashion.png'),
(23, 23, 'Cultura Pop', 'cultura-pop', 'pop.png'),
(24, 24, 'Anime y Manga', 'anime-manga', 'anime.png'),
(25, 25, 'Literatura', 'literatura', 'books.png'),
(26, 26, 'Educación', 'educacion', 'education.png'),
(27, 27, 'Medio Ambiente', 'medio-ambiente', 'green.png'),
(28, 28, 'Filosofía y Pensamiento', 'filosofia-pensamiento', 'philosophy.png'),
(29, 29, 'Política y Sociedad', 'politica-sociedad', 'politics.png'),
(30, 30, 'Deportes', 'deportes', 'sports.png'),
(31, 31, 'eSports', 'esports', 'esports.png'),
(32, 32, 'Tecnología Wearable', 'tecnologia-wearable', 'wearable.png'),
(33, 33, 'Robótica y Automatización', 'robotica-automatizacion', 'robot.png'),
(34, 34, 'Realidad Virtual y AR', 'realidad-virtual-ar', 'vr.png'),
(35, 35, 'Domótica y Smart Home', 'domotica-smart-home', 'smarthome.png'),
(36, 36, 'Drones y Aviación', 'drones-aviacion', 'drone.png'),
(37, 37, 'Energías Renovables', 'energias-renovables', 'solar.png'),
(38, 38, 'Autos Eléctricos', 'autos-electricos', 'ev.png'),
(39, 39, 'Space Tech', 'space-tech', 'space.png'),
(40, 40, 'Ciencia Ficción', 'ciencia-ficcion', 'scifi.png'),
(41, 41, 'xxx', 'xxx', 'xx.png');";

$phpost_sql["Create Table: p_comentarios"] = "CREATE TABLE IF NOT EXISTS `p_comentarios` (
    `cid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `c_post_id` INT UNSIGNED NOT NULL,
    `c_user` INT UNSIGNED NOT NULL,
    `c_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_body` TEXT,
    `c_votos_pos` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_votos_neg` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_status` TINYINT NOT NULL DEFAULT 0,
    `c_level` TINYINT NOT NULL DEFAULT 0,
    `c_answer_cid` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_ip` VARBINARY(16) DEFAULT NULL,
    INDEX `idx_post_status` (`c_post_id`, `c_status`),
    INDEX `idx_user_date` (`c_user`, `c_date`),
    INDEX `idx_answer` (`c_answer_cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: p_favoritos"] = "CREATE TABLE IF NOT EXISTS `p_favoritos` (
    `fav_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `fav_user` INT UNSIGNED NOT NULL,
    `fav_post_id` INT UNSIGNED NOT NULL,
    `fav_date` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_fav` (`fav_user`, `fav_post_id`),
    INDEX `idx_post` (`fav_post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: p_posts"] = "CREATE TABLE IF NOT EXISTS `p_posts` (
    `post_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_category` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_title` VARCHAR(200) NOT NULL DEFAULT '',
    `post_body` LONGTEXT,
    `post_excerpt` VARCHAR(300) NOT NULL DEFAULT '',
    `post_user` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_cache` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_comments` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_favoritos` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_hits` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_portada` VARCHAR(255) NOT NULL DEFAULT '',
    `post_private` TINYINT NOT NULL DEFAULT 0,
    `post_puntos` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_seguidores` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_shared` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_smileys` TINYINT NOT NULL DEFAULT 0,
    `post_sponsored` TINYINT NOT NULL DEFAULT 0,
    `post_status` ENUM('publicado','oculto','eliminado','revision','borrador') NOT NULL DEFAULT 'publicado',
    `post_sticky` TINYINT NOT NULL DEFAULT 0,
    `post_tags` VARCHAR(200) NOT NULL DEFAULT '',
    `post_fuentes` TEXT,
    `post_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_update` INT UNSIGNED NOT NULL DEFAULT 0,
    `post_block_comments` TINYINT NOT NULL DEFAULT 0,
    `post_visitantes` TINYINT NOT NULL DEFAULT 0,
    `post_ip` VARBINARY(16) DEFAULT NULL,
    FULLTEXT INDEX `ft_title_body_tags` (`post_title`, `post_body`, `post_tags`),
    INDEX `idx_category_status_date` (`post_category`, `post_status`, `post_date`),
    INDEX `idx_user_date` (`post_user`, `post_date`),
    INDEX `idx_status_date` (`post_status`, `post_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$version = Config::app('app.version');
$phpost_sql["Insert Data: p_posts_welcome"] = "INSERT INTO `p_posts` (`post_id`, `post_category`, `post_title`, `post_body`, `post_excerpt`, `post_user`, `post_sticky`, `post_tags`, `post_fuentes`) VALUES (1, 41, '🚀 Bienvenido a PHPost v{$version}', '[center][b][size=32]¡Bienvenido a PHPost![/size][/b][/center]\n\n[center][img]https://placehold.jp/3d4070/ffffff/800x200.png?text=PHPost%20{$version}[/img][/center]\n\nPHPost es un [url=https://github.com/joelmiguelvalente/PHPost]sistema de publicación de contenido[/url] moderno, rápido y seguro.\n[b][u]Características:[/u][/b]\n[list][item][b]Rendimiento optimizado[/b] - Carga rápida incluso con millones de posts[/item][item][item][b]Seguridad avanzada[/b] - Protección contra amenazas modernas[/item][item][b]Diseño responsive[/b] - Se ve perfecto en cualquier dispositivo[/item][item][b]Sistema de puntos y rangos[/b] - Motiva a tu comunidad[/item][item][b]Editor de BBCodes[/b] - Formato de texto enriquecido[/item][/list]\n\n[center][b][size=30][color=#ff6b6b]¡Comienza a crear contenido hoy mismo![/color][/size][/b][/center]^\n[hr]\n[center][size=20][color=#8b949e]Publicado con ♥ usando PHPost v{$version}[/color][/size][/center]', 'Primer post de bienvenida a PHPost 2026', 1, 1, 'phpost,bienvenida,comunidad,publicaciones,contenido,bbcodes', 'Publicación inicial del sistema');";

$phpost_sql["Create Table: p_votos"] = "CREATE TABLE IF NOT EXISTS `p_votos` (
    `voto_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tid` INT UNSIGNED NOT NULL,
    `tuser` INT UNSIGNED NOT NULL,
    `type` TINYINT NOT NULL DEFAULT 1 COMMENT '1=post,2=comentario',
    `type_vote` TINYINT NOT NULL DEFAULT 0 COMMENT '0=neutral,1=positivo,-1=negativo',
    `cant` INT NOT NULL DEFAULT 0,
    `date` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_vote` (`tid`, `tuser`, `type`),
    INDEX `idx_user_type` (`tuser`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_actividad"] = "CREATE TABLE IF NOT EXISTS `u_actividad` (
    `ac_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ac_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `ac_type` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `obj_uno` INT UNSIGNED NOT NULL DEFAULT 0,
    `obj_dos` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_id` INT UNSIGNED NOT NULL,
    INDEX `idx_user_date` (`user_id`, `ac_date`),
    INDEX `idx_type_obj` (`ac_type`, `obj_uno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_avisos"] = "CREATE TABLE IF NOT EXISTS `u_avisos` (
    `av_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `av_subject` VARCHAR(100) NOT NULL DEFAULT '',
    `av_body` TEXT,
    `av_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `av_read` TINYINT NOT NULL DEFAULT 0,
    `av_type` TINYINT NOT NULL DEFAULT 0,
    INDEX `idx_user_read` (`user_id`, `av_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_bloqueos"] = "CREATE TABLE IF NOT EXISTS `u_bloqueos` (
    `bid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `b_user` INT UNSIGNED NOT NULL,
    `b_auser` INT UNSIGNED NOT NULL,
    `b_date` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_lock` (`b_user`, `b_auser`),
    INDEX `idx_auser` (`b_auser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_follows"] = "CREATE TABLE IF NOT EXISTS `u_follows` (
    `follow_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `f_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `f_id` INT UNSIGNED NOT NULL,
    `f_type` TINYINT NOT NULL DEFAULT 0,
    `f_user` INT UNSIGNED NOT NULL,
    UNIQUE KEY `unique_follow` (`f_user`, `f_id`, `f_type`),
    INDEX `idx_target_type` (`f_id`, `f_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_mensajes"] = "CREATE TABLE IF NOT EXISTS `u_mensajes` (
    `mp_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mp_answer` TINYINT NOT NULL DEFAULT 0,
    `mp_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `mp_del_from` TINYINT NOT NULL DEFAULT 0,
    `mp_del_to` TINYINT NOT NULL DEFAULT 0,
    `mp_from` INT UNSIGNED NOT NULL,
    `mp_preview` VARCHAR(100) DEFAULT NULL,
    `mp_read_from` TINYINT NOT NULL DEFAULT 1,
    `mp_read_mon_from` TINYINT NOT NULL DEFAULT 1,
    `mp_read_mon_to` TINYINT NOT NULL DEFAULT 0,
    `mp_read_to` TINYINT NOT NULL DEFAULT 0,
    `mp_subject` VARCHAR(100) DEFAULT NULL,
    `mp_to` INT UNSIGNED NOT NULL,
    INDEX `idx_to_read` (`mp_to`, `mp_read_to`),
    INDEX `idx_from_read` (`mp_from`, `mp_read_from`),
    INDEX `idx_date` (`mp_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_miembros"] = "CREATE TABLE IF NOT EXISTS `u_miembros` (
    `user_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_name` VARCHAR(50) NOT NULL,
    `user_email` VARCHAR(255) NOT NULL,
    `user_password` VARCHAR(255) NOT NULL,
    `user_rango` INT UNSIGNED NOT NULL DEFAULT 3,
    `user_bad_hits` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_cache` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_comentarios` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_posts` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_puntos` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `user_last_ip` VARBINARY(16) DEFAULT NULL,
    `user_lastactive` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_lastlogin` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_lastpost` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_name_changes` TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `user_nextpuntos` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_puntosxdar` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_seguidores` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_seguidos` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_amigos` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_registro` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_activo` TINYINT NOT NULL DEFAULT 0,
    `user_baneado` TINYINT NOT NULL DEFAULT 0,
    `user_email_verified` TINYINT NOT NULL DEFAULT 0,
    `user_created_at` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_name` (`user_name`),
    UNIQUE KEY `unique_email` (`user_email`),
    INDEX `idx_name` (`user_name`),
    INDEX `idx_email` (`user_email`),
    INDEX `idx_status` (`user_activo`, `user_baneado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_miembros_sets"] = "CREATE TABLE IF NOT EXISTS `u_miembros_sets` (
    `user_id` INT UNSIGNED PRIMARY KEY,
    `user_avatares` TEXT,
    `user_chat` INT UNSIGNED NOT NULL DEFAULT 0,
    `user_theme` VARCHAR(40) NOT NULL DEFAULT 'default',
    `user_cover` TEXT,
    `user_double_secret` TEXT COMMENT '2FA secret',
    `user_portada` VARCHAR(255) DEFAULT NULL,
    `user_recovery` TEXT,
    `user_socials` TEXT,
    `user_vip` TINYINT NOT NULL DEFAULT 0,
    `user_verificado` TINYINT NOT NULL DEFAULT 0,
    `user_updated_at` INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_lockout"] = "CREATE TABLE IF NOT EXISTS `u_lockout` (
    `user_id` INT UNSIGNED PRIMARY KEY,
    `locked_until` INT UNSIGNED NOT NULL DEFAULT 0,
    `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_login_attempts"] = "CREATE TABLE IF NOT EXISTS `u_login_attempts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `identifier` VARCHAR(190) NOT NULL,
    `ip` VARBINARY(16) NOT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `success` TINYINT NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    INDEX `idx_user_created` (`user_id`, `created_at`),
    INDEX `idx_identifier_created` (`identifier`, `created_at`),
    INDEX `idx_ip_created` (`ip`, `created_at`),
    INDEX `idx_user_success_created` (`user_id`, `success`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_nicks"] = "CREATE TABLE IF NOT EXISTS `u_nicks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name_1` VARCHAR(50) NOT NULL,
    `name_2` VARCHAR(50) NOT NULL,
    `user_email` VARCHAR(255) NOT NULL,
    `estado` ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    `hash` VARCHAR(200) NOT NULL DEFAULT '',
    `ip` VARBINARY(16) DEFAULT NULL,
    `time` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_name1` (`name_1`),
    UNIQUE KEY `unique_name2` (`name_2`),
    UNIQUE KEY `unique_email` (`user_email`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_monitor"] = "CREATE TABLE IF NOT EXISTS `u_monitor` (
    `not_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `not_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `not_menubar` TINYINT NOT NULL DEFAULT 2,
    `not_monitor` TINYINT NOT NULL DEFAULT 1,
    `not_total` TINYINT NOT NULL DEFAULT 1,
    `not_type` TINYINT NOT NULL DEFAULT 0,
    `obj_uno` INT UNSIGNED NOT NULL DEFAULT 0,
    `obj_dos` INT UNSIGNED NOT NULL DEFAULT 0,
    `obj_tres` INT UNSIGNED NOT NULL DEFAULT 0,
    `obj_user` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `not_read` TINYINT NOT NULL DEFAULT 0,
    INDEX `idx_user_type_read` (`user_id`, `not_type`, `not_read`),
    INDEX `idx_date` (`not_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_muro"] = "CREATE TABLE IF NOT EXISTS `u_muro` (
    `pub_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `p_body` TEXT,
    `p_comments` INT UNSIGNED NOT NULL DEFAULT 0,
    `p_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `p_ip` VARBINARY(16) DEFAULT NULL,
    `p_likes` INT UNSIGNED NOT NULL DEFAULT 0,
    `p_nick` VARCHAR(24) NOT NULL DEFAULT '',
    `p_type` TINYINT NOT NULL DEFAULT 0,
    `p_update` INT UNSIGNED NOT NULL DEFAULT 0,
    `p_user_pub` INT UNSIGNED DEFAULT NULL,
    `p_user` INT UNSIGNED DEFAULT NULL,
    `p_edit` INT UNSIGNED NOT NULL DEFAULT 0,
    `p_visibility` ENUM('everyone','followers','friends','nobody') NOT NULL DEFAULT 'everyone',
    `p_adult` TINYINT NOT NULL DEFAULT 0,
    INDEX `idx_user_date` (`p_user`, `p_date`),
    INDEX `idx_user_pub_date` (`p_user_pub`, `p_date`),
    INDEX `idx_visibility` (`p_visibility`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_muro_adjuntos"] = "CREATE TABLE IF NOT EXISTS `u_muro_adjuntos` (
    `adj_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `adj_description` VARCHAR(255) NOT NULL DEFAULT '',
    `adj_image` VARCHAR(255) NOT NULL DEFAULT '',
    `adj_images` TEXT,
    `adj_title` VARCHAR(100) NOT NULL DEFAULT '',
    `adj_url` VARCHAR(255) NOT NULL DEFAULT '',
    `adj_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `adj_type` ENUM('video','enlace','imagen','fotos') NOT NULL DEFAULT 'enlace',
    `pub_id` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_pub_id` (`pub_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_muro_comentarios"] = "CREATE TABLE IF NOT EXISTS `u_muro_comentarios` (
    `cid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pub_id` INT UNSIGNED DEFAULT NULL,
    `c_user` INT UNSIGNED DEFAULT NULL,
    `c_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_body` TEXT,
    `c_likes` INT UNSIGNED NOT NULL DEFAULT 0,
    `c_ip` VARBINARY(16) DEFAULT NULL,
    INDEX `idx_pub_date` (`pub_id`, `c_date`),
    INDEX `idx_user` (`c_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_muro_likes"] = "CREATE TABLE IF NOT EXISTS `u_muro_likes` (
    `like_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `obj_id` INT UNSIGNED DEFAULT NULL,
    `obj_type` TINYINT NOT NULL,
    UNIQUE KEY `unique_like` (`user_id`, `obj_id`, `obj_type`),
    INDEX `idx_obj` (`obj_type`, `obj_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_perfil"] = "CREATE TABLE IF NOT EXISTS `u_perfil` (
    `user_id` INT UNSIGNED PRIMARY KEY,
    `user_dia` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `user_mes` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `user_ano` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `user_pais` CHAR(2) NOT NULL DEFAULT '',
    `user_estado` TINYINT NOT NULL DEFAULT 1,
    `user_sexo` VARCHAR(10) NOT NULL DEFAULT 'none',
    `user_firma` VARCHAR(500) NOT NULL DEFAULT '',
    `p_nombre` VARCHAR(100) DEFAULT NULL,
    `p_avatar` TINYINT NOT NULL DEFAULT 0,
    `p_mensaje` TEXT,
    `p_sitio` VARCHAR(255) DEFAULT NULL,
    `p_socials` TEXT,
    `p_privacidad` ENUM('everyone','registered','followers','following','friends_mutual','friends_any','nobody','') NOT NULL DEFAULT 'everyone',
    `p_mensajes_privados` ENUM('everyone','registered','followers','following','friends_mutual','friends_any','nobody','off') NOT NULL DEFAULT 'everyone',
    `p_publicar_muro` ENUM('everyone','registered','followers','following','friends_mutual','friends_any','nobody','') NOT NULL DEFAULT 'everyone',
    `p_muro_visitas` ENUM('everyone','registered','followers','following','friends_mutual','friends_any','nobody','') NOT NULL DEFAULT 'everyone',
    `p_updated_at` INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_portal"] = "CREATE TABLE IF NOT EXISTS `u_portal` (
    `user_id` INT UNSIGNED PRIMARY KEY,
    `last_posts_visited` TEXT,
    `last_posts_shared` TEXT,
    `last_posts_cats` TEXT,
    `c_monitor` LONGTEXT NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_rangos"] = "CREATE TABLE IF NOT EXISTS `u_rangos` (
    `rango_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `r_allows` JSON NOT NULL,
    `r_cant` INT UNSIGNED NOT NULL DEFAULT 0,
    `r_color` CHAR(12) NOT NULL DEFAULT '171717',
    `r_image` VARCHAR(32) NOT NULL DEFAULT 'new.png',
    `r_name` VARCHAR(32) NOT NULL DEFAULT '',
    `r_type` INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Insert Data: u_rangos"] = "INSERT INTO `u_rangos` (`rango_id`, `r_allows`, `r_cant`, `r_color`, `r_image`, `r_name`, `r_type`) VALUES
(1, '{\"goaf\":5,\"godp\":false,\"gopf\":false,\"gopp\":false,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":true,\"sumo\":false,\"godpc\":false,\"goepc\":false,\"gopcf\":false,\"gopcp\":false,\"gopfd\":50,\"gopfp\":20,\"govpn\":false,\"govpp\":false,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 0, 'D6030B', 'rosette.png', 'Administrador', 0),
(2, '{\"goaf\":15,\"godp\":false,\"gopf\":false,\"gopp\":false,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":true,\"godpc\":false,\"goepc\":false,\"gopcf\":false,\"gopcp\":false,\"gopfd\":30,\"gopfp\":18,\"govpn\":false,\"govpp\":false,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 0, 'ff9900', 'shield.png', 'Moderador', 0),
(3, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":5,\"gopfp\":5,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 0, '171717', 'new.png', 'Novato', 0),
(4, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":10,\"gopfp\":10,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 50, '0198E7', 'star_bronze_3.png', 'New Full User', 1),
(5, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":20,\"gopfp\":12,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 70, '00ccff', 'star_silver_3.png', 'Full User', 1),
(6, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":15,\"gopfp\":11,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 0, '01A021', 'star_gold_3.png', 'Great User', 0),
(7, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":25,\"gopfp\":12,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 120, 'cc6600', 'asterisk_yellow.png', 'Gold User', 1);";

$phpost_sql["Create Table: u_respuestas"] = "CREATE TABLE IF NOT EXISTS `u_respuestas` (
    `mr_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mp_id` INT UNSIGNED NOT NULL,
    `mr_from` INT UNSIGNED NOT NULL,
    `mr_body` TEXT,
    `mr_ip` VARBINARY(16) DEFAULT NULL,
    `mr_date` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_mp_id` (`mp_id`),
    INDEX `idx_from_date` (`mr_from`, `mr_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_sessions"] = "CREATE TABLE IF NOT EXISTS `u_sessions` (
    `session_id` CHAR(64) PRIMARY KEY,
    `session_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `session_ip` VARBINARY(16) DEFAULT NULL,
    `session_token` CHAR(100) NOT NULL DEFAULT '',
    `session_time` INT UNSIGNED NOT NULL DEFAULT 0,
    `session_autologin` TINYINT NOT NULL DEFAULT 0,
    `session_ua` VARCHAR(255) DEFAULT '',
    `session_created_at` INT UNSIGNED NOT NULL DEFAULT 0,
    `session_last_activity` INT UNSIGNED NOT NULL DEFAULT 0,
    `session_regenerated_at` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_user_id` (`session_user_id`),
    INDEX `idx_last_activity` (`session_last_activity`),
    INDEX `idx_token` (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: u_suspension"] = "CREATE TABLE IF NOT EXISTS `u_suspension` (
    `susp_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `susp_causa` TEXT,
    `susp_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `susp_termina` INT UNSIGNED NOT NULL DEFAULT 0,
    `susp_mod` INT UNSIGNED NOT NULL DEFAULT 0,
    `susp_ip` VARBINARY(16) DEFAULT NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_mod` (`susp_mod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_afiliados"] = "CREATE TABLE IF NOT EXISTS `w_afiliados` (
    `aid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `a_titulo` VARCHAR(80) NOT NULL,
    `a_url` VARCHAR(255) NOT NULL,
    `a_banner` VARCHAR(255) DEFAULT NULL,
    `a_descripcion` VARCHAR(255) DEFAULT NULL,
    `a_sid` VARCHAR(32) DEFAULT NULL,
    `a_hits_in` INT UNSIGNED NOT NULL DEFAULT 0,
    `a_hits_out` INT UNSIGNED NOT NULL DEFAULT 0,
    `a_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `a_active` TINYINT NOT NULL DEFAULT 0,
    INDEX `idx_active` (`a_active`),
    INDEX `idx_code` (`a_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_publicidad"] = "CREATE TABLE IF NOT EXISTS `w_publicidad` (
    `ads_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL UNIQUE,
    `titulo` VARCHAR(100) NOT NULL DEFAULT '',
    `codigo` LONGTEXT,
    `activo` TINYINT(1) NOT NULL DEFAULT 0,
    `orden` SMALLINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Insert Data: w_publicidad"] = "INSERT INTO `w_publicidad` (`nombre`, `titulo`, `codigo`, `activo`, `orden`) VALUES
('header', 'Cabecera del sitio', '', 0, 10),
('sidebar', 'Barra lateral', '', 0, 20),
('home_top', 'Inicio - Parte superior', '', 0, 30),
('home_bottom', 'Inicio - Parte inferior', '', 0, 40),
('post_top', 'Post - Encima del contenido', '', 0, 50),
('post_middle', 'Post - Mitad del contenido', '', 0, 60),
('post_bottom', 'Post - Debajo del contenido', '', 0, 70),
('comments', 'Entre los comentarios', '', 0, 80),
('footer', 'Pie de página', '', 0, 90),
('mobile', 'Dispositivos móviles', '', 0, 100);";

$phpost_sql["Create Table: w_configuracion"] = "CREATE TABLE IF NOT EXISTS `w_configuracion` (
    `phpost_id` INT UNSIGNED PRIMARY KEY DEFAULT 1,
    `titulo` VARCHAR(64) NOT NULL DEFAULT '',
    `slogan` VARCHAR(128) NOT NULL DEFAULT '',
    `url` VARCHAR(255) NOT NULL DEFAULT '',
    `email` VARCHAR(255) NOT NULL DEFAULT '',
    `banner` VARCHAR(255) NOT NULL DEFAULT '',
    `tema` VARCHAR(30) NOT NULL DEFAULT 'default',
    `google_search` VARCHAR(100) NOT NULL DEFAULT '',
    `c_ver_vistas_global` TINYINT NOT NULL DEFAULT 0,
    `c_quitar_vistas_global` TINYINT NOT NULL DEFAULT 0,
    `c_visitas_tiempo` TINYINT NOT NULL DEFAULT 5,
    `c_last_active` TINYINT NOT NULL DEFAULT 3,
    `c_count_guests` TINYINT NOT NULL DEFAULT 0,
    `c_fotos_private` TINYINT NOT NULL DEFAULT 0,
    `c_hits_guest` TINYINT NOT NULL DEFAULT 0,
    `c_keep_points` TINYINT NOT NULL DEFAULT 0,
    `c_max_posts` INT UNSIGNED NOT NULL DEFAULT 16,
    `c_max_com` INT UNSIGNED NOT NULL DEFAULT 25,
    `c_max_nots` INT UNSIGNED NOT NULL DEFAULT 99,
    `c_max_acts` INT UNSIGNED NOT NULL DEFAULT 99,
    `c_max_points_unlimited` INT UNSIGNED NOT NULL DEFAULT 99999,
    `c_newr_type` TINYINT NOT NULL DEFAULT 0,
    `c_allow_firma` TINYINT NOT NULL DEFAULT 1,
    `c_allow_foro` TINYINT NOT NULL DEFAULT 0,
    `c_allow_fuentes` TINYINT NOT NULL DEFAULT 0,
    `c_allow_live` TINYINT NOT NULL DEFAULT 1,
    `c_allow_points` TINYINT NOT NULL DEFAULT 0,
    `c_allow_portal` TINYINT NOT NULL DEFAULT 1,
    `c_allow_sess_ip` TINYINT NOT NULL DEFAULT 1,
    `c_allow_sump` TINYINT NOT NULL DEFAULT 0,
    `c_allow_ticket` TINYINT NOT NULL DEFAULT 0,
    `c_allow_upload` TINYINT NOT NULL DEFAULT 0,
    `c_see_mod` TINYINT NOT NULL DEFAULT 0,
    `c_stats_cache` TINYINT NOT NULL DEFAULT 15,
    `c_desapprove_post` TINYINT NOT NULL DEFAULT 0,
    `offline` TINYINT NOT NULL DEFAULT 0,
    `offline_message` VARCHAR(255) NOT NULL DEFAULT 'Estamos en mantenimiento',
    `version` VARCHAR(30) NOT NULL DEFAULT '',
    `version_code` VARCHAR(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Insert Data: w_configuracion"] = "INSERT INTO `w_configuracion` (`phpost_id`) VALUES (1) ON DUPLICATE KEY UPDATE `phpost_id` = `phpost_id`;";

$phpost_sql["Create Table: w_registro"] = "CREATE TABLE IF NOT EXISTS `w_registro` (
    `reg_id` INT UNSIGNED PRIMARY KEY DEFAULT 1,
    `c_reg_active` TINYINT NOT NULL DEFAULT 1,
    `c_reg_activate` TINYINT NOT NULL DEFAULT 1,
    `c_reg_rango` INT UNSIGNED NOT NULL DEFAULT 3,
    `c_met_welcome` TINYINT NOT NULL DEFAULT 0,
    `c_message_welcome` VARCHAR(500) NOT NULL DEFAULT 'Hola [usuario], [welcome] a [b][web][/b].',
    `c_allow_edad` TINYINT NOT NULL DEFAULT 16,
    `captcha_provider` ENUM('recaptcha', 'hcaptcha') NOT NULL DEFAULT 'recaptcha',
    `public_key` VARCHAR(72) NOT NULL DEFAULT '',
    `secret_key` VARCHAR(72) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Insert Data: w_registro"] = "INSERT INTO `w_registro` (`reg_id`) VALUES (1) ON DUPLICATE KEY UPDATE `reg_id` = `reg_id`;";

$phpost_sql["Create Table: w_sitemap"] = "CREATE TABLE IF NOT EXISTS `w_sitemap` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `url` VARCHAR(255) NOT NULL,
    `frecuencia` VARCHAR(15) NOT NULL DEFAULT '',
    `fecha` INT UNSIGNED NOT NULL DEFAULT 0,
    `prioridad` DECIMAL(2,1) NOT NULL DEFAULT 0.5,
    `lastmod` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_url` (`url`),
    INDEX `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_sitemap_control"] = "CREATE TABLE IF NOT EXISTS `w_sitemap_control` (
    `sid` INT UNSIGNED PRIMARY KEY DEFAULT 0,
    `register_post` TINYINT NOT NULL DEFAULT 0,
    `register_foto` TINYINT NOT NULL DEFAULT 0,
    `register_comunidades` TINYINT NOT NULL DEFAULT 0,
    `register_temas` TINYINT NOT NULL DEFAULT 0,
    `register_respuestas` TINYINT NOT NULL DEFAULT 0,
    `update_post` TINYINT NOT NULL DEFAULT 0,
    `update_foto` TINYINT NOT NULL DEFAULT 0,
    `update_comunidades` TINYINT NOT NULL DEFAULT 0,
    `update_temas` TINYINT NOT NULL DEFAULT 0,
    `update_respuestas` TINYINT NOT NULL DEFAULT 0,
    `last_update` INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_denuncias"] = "CREATE TABLE IF NOT EXISTS `w_denuncias` (
    `did` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `d_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `d_extra` TEXT,
    `d_razon` TINYINT NOT NULL,
    `d_total` SMALLINT NOT NULL DEFAULT 1,
    `d_type` ENUM('post','mensaje','usuario','foto') NOT NULL DEFAULT 'post',
    `d_user` INT UNSIGNED NOT NULL,
    `obj_id` INT UNSIGNED NOT NULL,
    `d_status` TINYINT NOT NULL DEFAULT 0 COMMENT '0=pending,1=resolved,2=dismissed',
    INDEX `idx_type_obj` (`d_type`, `obj_id`),
    INDEX `idx_user_date` (`d_user`, `d_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_contacts"] = "CREATE TABLE IF NOT EXISTS `w_contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `user_email` VARCHAR(255) NOT NULL,
    `time` INT UNSIGNED NOT NULL DEFAULT 0,
    `type` TINYINT NOT NULL DEFAULT 0,
    `hash` CHAR(128) NOT NULL DEFAULT '',
    `ip` VARBINARY(16) DEFAULT NULL,
    UNIQUE KEY `unique_email` (`user_email`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_activate"] = "CREATE TABLE IF NOT EXISTS `w_activate` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `user_email` VARCHAR(255) NOT NULL,
    `code_hash` CHAR(128) NOT NULL DEFAULT '',
    `expire_at` INT UNSIGNED NOT NULL DEFAULT 0,
    `type` ENUM('activation','reset','email_change') NOT NULL DEFAULT 'activation',
    `used` TINYINT NOT NULL DEFAULT 0,
    `ip` VARBINARY(16) DEFAULT NULL,
    UNIQUE KEY `unique_email_type` (`user_email`, `type`),
    INDEX `idx_code` (`code_hash`),
    INDEX `idx_expire` (`expire_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_medallas"] = "CREATE TABLE IF NOT EXISTS `w_medallas` (
    `medal_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `m_autor` INT UNSIGNED NOT NULL,
    `m_cant` INT UNSIGNED NOT NULL DEFAULT 0,
    `m_cond_foto` INT UNSIGNED NOT NULL DEFAULT 0,
    `m_cond_post` INT UNSIGNED NOT NULL DEFAULT 0,
    `m_cond_user` INT UNSIGNED NOT NULL DEFAULT 0,
    `m_cond_user_rango` INT UNSIGNED NOT NULL DEFAULT 0,
    `m_cond_comunidad` INT UNSIGNED NOT NULL DEFAULT 0,
    `m_cond_video` INT UNSIGNED NOT NULL DEFAULT 0,
    `m_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `m_description` VARCHAR(255) NOT NULL DEFAULT '',
    `m_image` VARCHAR(150) NOT NULL DEFAULT '',
    `m_title` VARCHAR(50) NOT NULL DEFAULT '',
    `m_total` INT UNSIGNED NOT NULL DEFAULT 0,
    `m_type` TINYINT NOT NULL DEFAULT 0,
    INDEX `idx_type` (`m_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_medallas_assign"] = "CREATE TABLE IF NOT EXISTS `w_medallas_assign` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medal_id` INT UNSIGNED NOT NULL,
    `medal_for` INT UNSIGNED NOT NULL,
    `medal_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `medal_ip` VARBINARY(16) DEFAULT NULL,
    UNIQUE KEY `unique_award` (`medal_id`, `medal_for`),
    INDEX `idx_medal_for` (`medal_for`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_historial"] = "CREATE TABLE IF NOT EXISTS `w_historial` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pofid` INT UNSIGNED DEFAULT NULL,
    `type` TINYINT NOT NULL DEFAULT 0,
    `action` TINYINT NOT NULL DEFAULT 0,
    `mod` INT UNSIGNED DEFAULT NULL,
    `reason` TEXT,
    `date` INT UNSIGNED NOT NULL DEFAULT 0,
    `mod_ip` VARBINARY(16) DEFAULT NULL,
    INDEX `idx_pofid_type` (`pofid`, `type`),
    INDEX `idx_mod_date` (`mod`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_noticias"] = "CREATE TABLE IF NOT EXISTS `w_noticias` (
    `not_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `not_title` VARCHAR(120) NOT NULL DEFAULT '',
    `not_body` TEXT,
    `not_autor` INT UNSIGNED NOT NULL DEFAULT 0,
    `not_date` INT UNSIGNED NOT NULL DEFAULT 0,
    `not_expires` INT UNSIGNED NOT NULL DEFAULT 0,
    `not_type` TINYINT NOT NULL DEFAULT 0,
    `not_color` ENUM('info','success','warning','danger','primary','secondary') NOT NULL DEFAULT 'info',
    `not_active` TINYINT NOT NULL DEFAULT 0,
    INDEX `idx_active_date` (`not_active`, `not_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_blacklist"] = "CREATE TABLE IF NOT EXISTS `w_blacklist` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` TINYINT NOT NULL DEFAULT 0,
    `value` VARCHAR(100) NOT NULL,
    `reason` VARCHAR(255) DEFAULT NULL,
    `author` INT UNSIGNED DEFAULT NULL,
    `date` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_type_value` (`type`, `value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_badwords"] = "CREATE TABLE IF NOT EXISTS `w_badwords` (
    `wid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `word` VARCHAR(255) DEFAULT NULL,
    `swop` VARCHAR(255) DEFAULT NULL,
    `method` TINYINT NOT NULL DEFAULT 0,
    `type` TINYINT NOT NULL DEFAULT 0,
    `author` INT UNSIGNED DEFAULT NULL,
    `reason` VARCHAR(255) DEFAULT NULL,
    `date` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_word_type` (`word`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_stats"] = "CREATE TABLE IF NOT EXISTS `w_stats` (
    `stats_no` INT UNSIGNED PRIMARY KEY DEFAULT 0,
    `stats_max_online` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_max_time` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_time` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_time_cache` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_time_foundation` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_time_upgrade` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_miembros` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_posts` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_fotos` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_comments` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_foto_comments` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_comunidades` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_temas` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_respuestas` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_updated_at` INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Insert Data: w_stats"] = "INSERT INTO `w_stats` (`stats_no`, `stats_max_online`) VALUES (1, 0) ON DUPLICATE KEY UPDATE `stats_no` = `stats_no`;";

$phpost_sql["Create Table: w_visitas"] = "CREATE TABLE IF NOT EXISTS `w_visitas` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user` INT UNSIGNED NOT NULL,
    `target_id` INT UNSIGNED NOT NULL,
    `type` TINYINT NOT NULL DEFAULT 0,
    `date` INT UNSIGNED NOT NULL DEFAULT 0,
    `ip` VARBINARY(16) DEFAULT NULL,
    INDEX `idx_target_type_date` (`target_id`, `type`, `date`),
    INDEX `idx_user_date` (`user`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_social"] = "CREATE TABLE IF NOT EXISTS `w_social` (
    `social_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `social_name` VARCHAR(22) NOT NULL DEFAULT '',
    `social_client_id` VARCHAR(255) NOT NULL DEFAULT '',
    `social_client_secret` VARCHAR(255) NOT NULL DEFAULT '',
    `social_redirect_uri` VARCHAR(255) NOT NULL DEFAULT '',
    `social_status` TINYINT NOT NULL DEFAULT 0,
    `social_icon` VARCHAR(20) NOT NULL DEFAULT '',
    `social_created_at` INT UNSIGNED NOT NULL DEFAULT 0,
    `social_updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_status` (`social_status`),
    INDEX `idx_name` (`social_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_migrations"] = "CREATE TABLE IF NOT EXISTS `w_migrations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(120) NOT NULL UNIQUE,
    `executed_at` INT UNSIGNED NOT NULL,
    `batch` INT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$time = time();
$phpost_sql["Insert Data: w_migrations"] = "INSERT INTO `w_migrations` (`id`, `migration`, `executed_at`, `batch`) VALUES
(1, 'varbinary', $time, 1),
(2, 'sessions', $time, 1);";

$phpost_sql["Create Table: w_video_platforms"] = "CREATE TABLE IF NOT EXISTS `w_video_platforms` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL DEFAULT '',
    `domain` VARCHAR(255) NOT NULL DEFAULT '',
    `path_pattern` TEXT NOT NULL,
    `embed_template` TEXT NOT NULL,
    `enabled` TINYINT NOT NULL DEFAULT 1,
    `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
    `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Insert Data: w_video_platforms"] = "INSERT INTO `w_video_platforms` (`id`, `name`, `domain`, `path_pattern`, `embed_template`, `enabled`, `created_at`, `updated_at`) VALUES
(1, 'youtube', 'youtube.com', '/watch?v={id}', '<iframe width=\"640\" height=\"360\" src=\"https://www.youtube.com/embed/{id}\" frameborder=\"0\" allowfullscreen></iframe>', 1, $time, 0),
(2, 'youtube_short', 'youtu.be', '/{id}', '<iframe width=\"640\" height=\"360\" src=\"https://www.youtube.com/embed/{id}\" frameborder=\"0\" allowfullscreen></iframe>', 1, $time, 0),
(3, 'vimeo', 'vimeo.com', '/{id}', '<iframe width=\"640\" height=\"360\" src=\"https://player.vimeo.com/video/{id}\" frameborder=\"0\" allowfullscreen></iframe>', 1, $time, 0),
(4, 'dailymotion', 'dailymotion.com', '/video/{id}', '<iframe frameborder=\"0\" width=\"640\" height=\"360\" src=\"https://www.dailymotion.com/embed/video/{id}\" allowfullscreen></iframe>', 1, $time, 0),
(5, 'tiktok', 'tiktok.com', '/@{username}/video/{id}', '<blockquote class=\"tiktok-embed\" cite=\"{url}\"><a href=\"{url}\"></a></blockquote><script async src=\"https://www.tiktok.com/embed.js\"></script>', 0, $time, 0),
(6, 'twitch', 'twitch.tv', '/videos/{id}', '<iframe src=\"https://player.twitch.tv/?video={id}&parent=localhost&autoplay=false\" width=\"640\" height=\"360\" frameborder=\"0\" allowfullscreen></iframe>', 0, $time, 0),
(7, 'instagram', 'instagram.com', '/p/{id}', '<blockquote class=\"instagram-media\" data-instgrm-permalink=\"{url}\"><a href=\"{url}\"></a></blockquote><script async defer src=\"//www.instagram.com/embed.js\"></script>', 0, $time, 0),
(8, 'streamable', 'streamable.com', '/e/{id}', '<iframe src=\"https://streamable.com/e/{id}\" width=\"640\" height=\"360\" frameborder=\"0\" allowfullscreen></iframe>', 0, $time, 0);";

$phpost_sql["Create Table: p_post_collaborators"] = "CREATE TABLE IF NOT EXISTS `p_post_collaborators` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    `invited_by` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
    `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_collab` (`post_id`, `user_id`),
    INDEX `idx_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: p_post_changes"] = "CREATE TABLE IF NOT EXISTS `p_post_changes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `change_note` TEXT NOT NULL,
    `changed_at` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX `idx_post` (`post_id`),
    INDEX `idx_user_date` (`user_id`, `changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Create Table: w_image_providers"] = "CREATE TABLE IF NOT EXISTS `w_image_providers` (
    `provider_id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `provider_slug` VARCHAR(32) NOT NULL UNIQUE,
    `provider_name` VARCHAR(64) NOT NULL,
    `api_key` VARCHAR(255) NOT NULL DEFAULT '',
    `extra_config` JSON NULL,
    `is_active` TINYINT NOT NULL DEFAULT 0,
    `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
    `updated_at` INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql["Insert Data: w_image_providers"] = "INSERT INTO `w_image_providers` (`provider_slug`, `provider_name`, `api_key`, `is_active`) VALUES ('imgur', 'Imgur', 'b2fddcb704b44a5', 1) ON DUPLICATE KEY UPDATE `provider_slug` = `provider_slug`;";
