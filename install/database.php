<?php
/**
 * @name database.php
 * @author PHPost Team & Miguel92
 * @copyright 2011-2025
 */

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `f_comentarios` (
  `cid` INT AUTO_INCREMENT PRIMARY KEY,
  `c_foto_id` INT DEFAULT 0,
  `c_user` INT DEFAULT 0,
  `c_date` INT NOT NULL DEFAULT 0,
  `c_update` INT NOT NULL DEFAULT 0,
  `c_body` TEXT NULL,
  `c_ip` VARBINARY(45) DEFAULT NULL,
  INDEX (c_foto_id),
  INDEX (c_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `f_favoritos` (
  `fid` INT AUTO_INCREMENT PRIMARY KEY,
  `f_foto_id` INT DEFAULT 0,
  `f_user` INT DEFAULT 0,
  `f_date` INT NOT NULL DEFAULT 0,
  INDEX (f_foto_id),
  INDEX (f_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `f_fotos` (
  `foto_id` INT AUTO_INCREMENT PRIMARY KEY,
  `f_album` INT DEFAULT 0,
  `f_title` VARCHAR(80) NOT NULL DEFAULT '',
  `f_date` INT NOT NULL DEFAULT 0,
  `f_description` TEXT DEFAULT NULL,
  `f_url` VARCHAR(255) NOT NULL DEFAULT '',
  `f_user` INT DEFAULT 0,
  `f_closed` TINYINT NOT NULL DEFAULT 0,
  `f_visitas` BIGINT DEFAULT 0,
  `f_status` TINYINT NOT NULL DEFAULT 0,
  `f_last` INT NOT NULL DEFAULT 0,
  `f_hits` BIGINT NOT NULL DEFAULT 0,
  `f_ip` VARBINARY(45) DEFAULT NULL,
  INDEX (f_user),
  INDEX (f_album),
  INDEX (f_date),
  INDEX (f_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `f_votos` (
  `vid` INT AUTO_INCREMENT PRIMARY KEY,
  `v_foto_id` INT DEFAULT 0,
  `v_user` INT DEFAULT 0,
  `v_pos` BIGINT NOT NULL DEFAULT 0,
  `v_neg` BIGINT NOT NULL DEFAULT 0,
  `v_date` INT NOT NULL DEFAULT 0,
  INDEX (v_foto_id),
  INDEX (v_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `f_album` (
  `aid` INT AUTO_INCREMENT PRIMARY KEY,
  `a_name` VARCHAR(60) NOT NULL DEFAULT '',
  `a_cover` VARCHAR(255) NOT NULL DEFAULT '',
  `a_description` VARCHAR(255) DEFAULT NULL,
  `a_status` TINYINT NOT NULL DEFAULT 0,
  `a_date` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `p_borradores` (
  `bid` INT AUTO_INCREMENT PRIMARY KEY,
  `b_post_id` INT DEFAULT 0,
  `b_user` INT DEFAULT 0,
  `b_date` INT NOT NULL DEFAULT 0,
  `b_update` INT NOT NULL DEFAULT 0,
  `b_title` VARCHAR(120) DEFAULT '',
  `b_portada` VARCHAR(255) NOT NULL DEFAULT '',
  `b_body` TEXT NULL,
  `b_tags` VARCHAR(128) NOT NULL DEFAULT '',
  `b_category` INT DEFAULT 0,
  `b_private` TINYINT NOT NULL DEFAULT 0,
  `b_block_comments` TINYINT NOT NULL DEFAULT 0,
  `b_sponsored` TINYINT NOT NULL DEFAULT 0,
  `b_sticky` TINYINT NOT NULL DEFAULT 0,
  `b_smileys` TINYINT NOT NULL DEFAULT 0,
  `b_visitantes` TINYINT NOT NULL DEFAULT 0,
  `b_status` TINYINT NOT NULL DEFAULT 0,
  `b_causa` varchar(128) NOT NULL DEFAULT '',
  `b_fuentes` TEXT NULL,
  `b_ip` VARBINARY(45) DEFAULT NULL,
  FULLTEXT INDEX ft_index (b_tags),
  FULLTEXT INDEX ft_title (b_title),
  INDEX idx_category (b_category),
  INDEX idx_user (b_user),
  INDEX idx_status (b_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `p_categorias` (
  `cid` INT AUTO_INCREMENT PRIMARY KEY,
  `c_orden` INT NOT NULL,
  `c_nombre` VARCHAR(50) NOT NULL DEFAULT '',
  `c_seo` VARCHAR(50) NOT NULL DEFAULT '',
  `c_img` VARCHAR(50) NOT NULL DEFAULT '',
  `c_color` CHAR(12) NOT NULL DEFAULT '',
  `c_privada` TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "INSERT INTO `p_categorias` (`cid`, `c_orden`, `c_nombre`, `c_seo`, `c_img`) VALUES
(1, 1, 'Animaciones', 'animaciones', 'flash.png'),
(2, 2, 'Apuntes y Monografías', 'apuntesymonografias', 'report.png'),
(3, 3, 'Arte', 'arte', 'palette.png'),
(4, 4, 'Autos y Motos', 'autosymotos', 'car.png'),
(5, 5, 'Celulares', 'celulares', 'phone.png'),
(6, 6, 'Ciencia y Educación', 'cienciayeducacion', 'lab.png'),
(7, 7, 'Comics', 'comics', 'comic.png'),
(8, 8, 'Deportes', 'deportes', 'sport.png'),
(9, 9, 'Downloads', 'downloads', 'disk.png'),
(10, 10, 'E-books y Tutoriales', 'ebooksytutoriales', 'ebook.png'),
(11, 11, 'Ecología', 'ecologia', 'nature.png'),
(12, 12, 'Economía y Negocios', 'economiaynegocios', 'economy.png'),
(13, 13, 'Femme', 'femme', 'female.png'),
(14, 14, 'Hazlo tu mismo', 'hazlotumismo', 'escuadra.png'),
(15, 15, 'Humor', 'humor', 'humor.png'),
(16, 16, 'Imágenes', 'imagenes', 'photo.png'),
(17, 17, 'Info', 'info', 'book.png'),
(18, 18, 'Juegos', 'juegos', 'controller.png'),
(19, 19, 'Links', 'links', 'link.png'),
(20, 20, 'Linux', 'linux', 'tux.png'),
(21, 21, 'Mac', 'mac', 'mac.png'),
(22, 22, 'Manga y Anime', 'mangayanime', 'manga.png'),
(23, 23, 'Mascotas', 'mascotas', 'pet.png'),
(24, 24, 'Música', 'musica', 'music.png'),
(25, 25, 'Noticias', 'noticias', 'newspaper.png'),
(26, 26, 'Off Topic', 'offtopic', 'comments.png'),
(27, 27, 'Recetas y Cocina', 'recetasycocina', 'cake.png'),
(28, 28, 'Salud y Bienestar', 'saludybienestar', 'heart.png'),
(29, 29, 'Solidaridad', 'solidaridad', 'salva.png'),
(30, 30, 'Taringa!', 'taringa', 'tscript.png'),
(31, 31, 'Turismo', 'turismo', 'brujula.png'),
(32, 32, 'TV, Peliculas y series', 'tvpeliculasyseries', 'tv.png'),
(33, 33, 'Videos On-line', 'videosonline', 'film.png');";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `p_comentarios` (
  `cid` INT AUTO_INCREMENT PRIMARY KEY,
  `c_post_id` INT NOT NULL,
  `c_user` INT NOT NULL,
  `c_date` INT NOT NULL DEFAULT 0,
  `c_body` TEXT NULL,
  `c_votos_pos` INT NOT NULL DEFAULT 0,
  `c_votos_neg` INT NOT NULL DEFAULT 0,
  `c_status` INT NOT NULL DEFAULT 0,
  `c_level` TINYINT(1) NOT NULL DEFAULT 0,
  `c_answer_cid` INT NOT NULL DEFAULT 0,
  `c_ip` VARBINARY(45) DEFAULT NULL,
  INDEX idx_answer (c_answer_cid),
  INDEX idx_post (c_post_id),
  INDEX idx_user (c_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `p_favoritos` (
  `fav_id` INT AUTO_INCREMENT PRIMARY KEY,
  `fav_user` INT NOT NULL,
  `fav_post_id` INT NOT NULL,
  `fav_date` INT NOT NULL DEFAULT 0,
  INDEX idx_post (fav_post_id),
  INDEX idx_user (fav_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `p_posts` (
  `post_id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_category` INT DEFAULT 0,
  `post_title` VARCHAR(120) DEFAULT '',
  `post_body` TEXT NULL,
  `post_excerpt` VARCHAR(200) DEFAULT '',
  `post_user` INT DEFAULT 0,
  `post_cache` INT DEFAULT 0,
  `post_comments` BIGINT DEFAULT 0,
  `post_favoritos` INT NOT NULL DEFAULT 0,
  `post_hits` INT NOT NULL DEFAULT 0,
  `post_portada` VARCHAR(255) NOT NULL DEFAULT '',
  `post_private` TINYINT NOT NULL DEFAULT 0,
  `post_puntos` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `post_seguidores` BIGINT NOT NULL DEFAULT 0,
  `post_shared` BIGINT NOT NULL DEFAULT 0,
  `post_smileys` TINYINT NOT NULL DEFAULT 0,
  `post_sponsored` TINYINT NOT NULL DEFAULT 0,
  `post_draft` TINYINT NOT NULL DEFAULT 0,
  `post_status` TINYINT NOT NULL DEFAULT 0,
  `post_sticky` TINYINT NOT NULL DEFAULT 0,
  `post_tags` VARCHAR(128) NOT NULL DEFAULT '',
  `post_fuentes` TEXT NULL,
  `post_date` INT NOT NULL DEFAULT 0,
  `post_update` INT NOT NULL DEFAULT 0,
  `post_block_comments` TINYINT NOT NULL DEFAULT 0,
  `post_visitantes` TINYINT NOT NULL DEFAULT 0,
  `post_ip` VARBINARY(45) DEFAULT NULL,
  FULLTEXT INDEX ft_index (post_tags),
  FULLTEXT INDEX ft_post_title (post_title),
  INDEX idx_category (post_category),
  INDEX idx_user (post_user),
  INDEX idx_status (post_status),
  INDEX idx_draft (post_draft)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `p_votos` (
  `voto_id` INT AUTO_INCREMENT PRIMARY KEY,
  `cant` INT NOT NULL DEFAULT 0,
  `date` INT NOT NULL DEFAULT 0,
  `tid` INT NOT NULL,
  `tuser` INT NOT NULL,
  `type` TINYINT(1) NOT NULL DEFAULT 1,
  INDEX idx_post (tid),
  INDEX idx_user (tuser)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_actividad` (
    `ac_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ac_date` INT NOT NULL DEFAULT 0,
  `ac_type` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `obj_uno` INT UNSIGNED NOT NULL DEFAULT 0,
  `obj_dos` INT UNSIGNED NOT NULL DEFAULT 0,
  `user_id` INT UNSIGNED NOT NULL,
  INDEX (`ac_type`),
  INDEX (`user_id`),
  INDEX (`ac_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_avisos` (
  `av_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `av_subject` VARCHAR(42) DEFAULT NULL,
  `av_body` TEXT DEFAULT NULL,
  `av_date` INT NOT NULL DEFAULT 0,
  `av_read` TINYINT NOT NULL DEFAULT 0,
  `av_type` TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_bloqueos` (
  `bid` INT AUTO_INCREMENT PRIMARY KEY,
  `b_user` INT NOT NULL,
  `b_auser` INT NOT NULL,
  `b_date` INT NOT NULL DEFAULT 0,
  UNIQUE KEY unique_lock (b_user, b_auser),
  INDEX idx_user (b_user),
  INDEX idx_auser (b_auser) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_follows` (
  `follow_id` INT AUTO_INCREMENT PRIMARY KEY,
  `f_date` INT NOT NULL DEFAULT 0,
  `f_id` INT NOT NULL,
  `f_type` TINYINT NOT NULL DEFAULT 0,
  `f_user` INT NOT NULL,
  UNIQUE KEY unique_follow (f_user, f_id, f_type),
  INDEX idx_user (f_user),
  INDEX idx_target (f_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_mensajes` (
  `mp_id` INT AUTO_INCREMENT PRIMARY KEY,
  `mp_answer` TINYINT NOT NULL DEFAULT 0,
  `mp_date` INT NOT NULL DEFAULT 0,
  `mp_del_from` TINYINT NOT NULL DEFAULT 0,
  `mp_del_to` TINYINT NOT NULL DEFAULT 0,
  `mp_from` INT NOT NULL,
  `mp_preview` VARCHAR(100) DEFAULT NULL,
  `mp_read_from` TINYINT NOT NULL DEFAULT 1,
  `mp_read_mon_from` TINYINT NOT NULL DEFAULT 1,
  `mp_read_mon_to` TINYINT NOT NULL DEFAULT 0,
  `mp_read_to` TINYINT NOT NULL DEFAULT 0,
  `mp_subject` VARCHAR(100) DEFAULT NULL,
  `mp_to` INT NOT NULL,
  INDEX idx_to (mp_to),
  INDEX idx_from (mp_from),
  INDEX idx_read_to (mp_read_to),
  INDEX idx_read_from (mp_read_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_miembros` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_name` VARCHAR(50) UNIQUE NOT NULL,
  `user_email` VARCHAR(255) UNIQUE NOT NULL,
  `user_password` VARCHAR(255) NOT NULL,
  `user_rango` INT DEFAULT 3,
  `user_bad_hits` INT DEFAULT 0,
  `user_cache` INT NOT NULL DEFAULT 0,
  `user_comentarios` BIGINT DEFAULT 0,
  `user_posts` BIGINT DEFAULT 0,
  `user_puntos` BIGINT DEFAULT 0,
  `user_last_ip` VARBINARY(45) DEFAULT NULL,
  `user_lastactive` INT NOT NULL DEFAULT 0,
  `user_lastlogin` INT NOT NULL DEFAULT 0,
  `user_lastpost` INT NOT NULL DEFAULT 0,
  `user_name_changes` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `user_nextpuntos` INT NOT NULL DEFAULT 0,
  `user_puntosxdar` INT DEFAULT 0,
  `user_seguidores` BIGINT NOT NULL DEFAULT 0,
  `user_seguidos` BIGINT NOT NULL DEFAULT 0,
  `user_amigos` BIGINT NOT NULL DEFAULT 0,
  `user_registro` INT NOT NULL DEFAULT 0,
  `user_activo` TINYINT NOT NULL DEFAULT 0,
  `user_baneado` TINYINT NOT NULL DEFAULT 0,
  INDEX idx_name (user_name),
  INDEX idx_email (user_email),
  INDEX idx_activo (user_activo),
  INDEX idx_baneado (user_baneado),
  INDEX idx_status (user_activo, user_baneado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_miembros_sets` (
  `user_id` INT PRIMARY KEY,
  `user_avatares` TEXT NULL,
  `user_chat` INT NOT NULL DEFAULT 0,
  `user_theme` CHAR(40) NOT NULL DEFAULT 'default',
  `user_cover` TEXT NULL,
  `user_double_secret` TEXT NULL, /* user_secret_2fa */
  `user_portada` VARCHAR(255) DEFAULT NULL,
  `user_recovery` TEXT NULL,
  `user_socials` TEXT NULL,
  `user_vip` TINYINT NOT NULL DEFAULT 0,
  `user_verificado` TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_lockout` (
  user_id INT PRIMARY KEY,
  locked_until DATETIME NULL,
  INDEX (locked_until)
) ENGINE=InnoDB ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_login_attempts` (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  identifier VARCHAR(190) NOT NULL,
  ip VARBINARY(45) NOT NULL,
  user_agent VARCHAR(255),
  success TINYINT(1) NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX (user_id, created_at),
  INDEX (identifier, created_at),
  INDEX (ip, created_at)
) ENGINE=InnoDB ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_nicks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name_1` VARCHAR(50) UNIQUE NOT NULL,
  `name_2` VARCHAR(50) UNIQUE NOT NULL,
  `user_email` VARCHAR(255) UNIQUE NOT NULL,
  `estado` TINYINT NOT NULL DEFAULT 0,
  `hash` VARCHAR(200) NOT NULL DEFAULT '',
  `ip` VARBINARY(45) DEFAULT NULL,
  `time` INT NOT NULL DEFAULT 0,
  INDEX idx_user_id (user_id),
  INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_monitor` (
  `not_id` INT AUTO_INCREMENT PRIMARY KEY,
  `not_date` INT NOT NULL DEFAULT 0,
  `not_menubar` TINYINT NOT NULL DEFAULT 2,
  `not_monitor` TINYINT NOT NULL DEFAULT 1,
  `not_total` TINYINT NOT NULL DEFAULT 1,
  `not_type` TINYINT NOT NULL DEFAULT 0,
  `obj_uno` INT NOT NULL DEFAULT 0,
  `obj_dos` INT NOT NULL DEFAULT 0,
  `obj_tres` INT NOT NULL DEFAULT 0,
  `obj_user` INT DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  INDEX idx_user (user_id),
  INDEX idx_type (not_type),
  INDEX idx_date (not_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_muro` (
  `pub_id` INT AUTO_INCREMENT PRIMARY KEY,
  `p_body` TEXT DEFAULT NULL,
  `p_comments` INT DEFAULT 0,
  `p_date` INT NOT NULL DEFAULT 0,
  `p_ip` VARBINARY(45) DEFAULT NULL,
  `p_likes` INT DEFAULT 0,
  `p_nick` VARCHAR(24) NOT NULL DEFAULT '',
  `p_type` TINYINT DEFAULT 0,
  `p_update` INT NOT NULL DEFAULT 0,
  `p_user_pub` INT DEFAULT NULL,
  `p_user` INT DEFAULT NULL,
  `p_edit` INT NOT NULL DEFAULT 0,
  /* Posibilidad de usarlo */
  `p_visibility` ENUM('everyone','followers','friends','nobody') DEFAULT 'everyone',
  `p_adult` TINYINT DEFAULT 0,
  INDEX idx_user (p_user),
  INDEX idx_user_pub (p_user_pub),
  INDEX idx_date (p_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_muro_adjuntos` (
  `adj_id` INT AUTO_INCREMENT PRIMARY KEY,
  `adj_description` VARCHAR(255) NOT NULL DEFAULT '', # a_desc
  `adj_image` VARCHAR(255) NOT NULL DEFAULT '', # a_img
  `adj_title` VARCHAR(100) NOT NULL DEFAULT '',
  `adj_url` VARCHAR(255) NOT NULL DEFAULT '',
  `adj_date` VARCHAR(255) NOT NULL DEFAULT '',
  `pub_id` INT NOT NULL DEFAULT 0,
  INDEX idx_pub_id (pub_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_muro_comentarios` (
  `cid` INT AUTO_INCREMENT PRIMARY KEY,
  `pub_id` INT DEFAULT NULL,
  `c_user` INT DEFAULT NULL,
  `c_date` INT NOT NULL DEFAULT 0,
  `c_body` TEXT,
  `c_likes` INT DEFAULT 0,
  `c_ip` VARBINARY(45) DEFAULT NULL,
  INDEX idx_pub_id (pub_id),
  INDEX idx_c_user (c_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_muro_likes` (
  `like_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `obj_id` INT DEFAULT NULL,
  `obj_type` TINYINT NOT NULL,
  INDEX idx_obj (obj_type, obj_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$enum = "ENUM('everyone','registered','followers','following','friends_mutual','friends_any','nobody')";
$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_perfil` (
  `user_id` INT PRIMARY KEY,
  `user_dia` TINYINT DEFAULT 0,
  `user_mes` TINYINT DEFAULT 0,
  `user_ano` SMALLINT DEFAULT 0,
  `user_pais` CHAR(2) NOT NULL DEFAULT '',
  `user_estado` TINYINT NOT NULL DEFAULT 1,
  `user_sexo` CHAR(10) NOT NULL DEFAULT 'none',
  `user_firma` VARCHAR(255) NOT NULL DEFAULT '',
  `p_nombre` VARCHAR(100) DEFAULT NULL,
  `p_avatar` TINYINT NOT NULL DEFAULT 0,
  `p_mensaje` TEXT DEFAULT NULL,
  `p_sitio` VARCHAR(255) DEFAULT NULL,
  `p_socials` TEXT DEFAULT NULL,
  `p_privacidad` $enum DEFAULT 'everyone',
  `p_mensajes_privados` $enum DEFAULT 'everyone',
  `p_publicar_muro` $enum DEFAULT 'everyone',
  `p_muro_visitas` $enum DEFAULT 'everyone',
  `p_total` VARCHAR(54) NOT NULL DEFAULT 'a:6:{i:0;i:5;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_portal` (
  `user_id` INT PRIMARY KEY,
  `last_posts_visited` TEXT NULL,
  `last_posts_shared` TEXT NULL,
  `last_posts_cats` TEXT NULL,
  `c_monitor` VARCHAR(255) NOT NULL DEFAULT 'f1,f2,f3,f8,f9,f4,f5,f10,f6,f7,f11,f12,f13,f14,f18,f19,20,f21'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_rangos` (
  `rango_id` INT PRIMARY KEY AUTO_INCREMENT,
  `r_allows` JSON NOT NULL,
  `r_cant` INT NOT NULL DEFAULT 0,
  `r_color` CHAR(12) NOT NULL DEFAULT '171717',
  `r_image` VARCHAR(32) NOT NULL DEFAULT 'new.png',
  `r_name` VARCHAR(32) NOT NULL DEFAULT '',
  `r_type` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "
INSERT INTO `u_rangos` (`rango_id`, `r_allows`, `r_cant`, `r_color`, `r_image`, `r_name`, `r_type`) VALUES
(1, '{\"goaf\":5,\"godp\":false,\"gopf\":false,\"gopp\":false,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":true,\"sumo\":false,\"godpc\":false,\"goepc\":false,\"gopcf\":false,\"gopcp\":false,\"gopfd\":50,\"gopfp\":20,\"govpn\":false,\"govpp\":false,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 0, 'D6030B', 'rosette.png', 'Administrador', 0),
(2, '{\"goaf\":15,\"godp\":false,\"gopf\":false,\"gopp\":false,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":true,\"godpc\":false,\"goepc\":false,\"gopcf\":false,\"gopcp\":false,\"gopfd\":30,\"gopfp\":18,\"govpn\":false,\"govpp\":false,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 0, 'ff9900', 'shield.png', 'Moderador', 0),
(3, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":5,\"gopfp\":5,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 0, '171717', 'new.png', 'Novato', 0),
(4, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":10,\"gopfp\":10,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 50, '0198E7', 'star_bronze_3.png', 'New Full User', 1),
(5, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":20,\"gopfp\":12,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 70, '00ccff', 'star_silver_3.png', 'Full User', 1),
(6, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":15,\"gopfp\":11,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 0, '01A021', 'star_gold_3.png', 'Great User', 0),
(7, '{\"goaf\":20,\"godp\":true,\"gopf\":true,\"gopp\":true,\"mocc\":false,\"mocp\":false,\"modu\":false,\"moef\":false,\"moep\":false,\"moop\":false,\"morf\":false,\"morp\":false,\"most\":false,\"mosu\":false,\"moub\":false,\"suad\":false,\"sumo\":false,\"godpc\":true,\"goepc\":true,\"gopcf\":true,\"gopcp\":true,\"gopfd\":25,\"gopfp\":12,\"govpn\":true,\"govpp\":true,\"moacp\":false,\"moadf\":false,\"moadm\":false,\"mocdf\":false,\"mocdm\":false,\"mocdp\":false,\"mocdu\":false,\"moecf\":false,\"moecm\":false,\"moecp\":false,\"moepm\":false,\"movub\":false,\"moayca\":false,\"mocepc\":false,\"moedfo\":false,\"moedpo\":false,\"movcud\":false,\"movcus\":false,\"moaydcp\":false,\"moedcopo\":false}', 120, 'cc6600', 'asterisk_yellow.png', 'Gold User', 1);";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_respuestas` (
  `mr_id` INT AUTO_INCREMENT PRIMARY KEY,
  `mp_id` INT NOT NULL,
  `mr_from` INT NOT NULL,
  `mr_body` TEXT DEFAULT NULL,
  `mr_ip` VARBINARY(45) DEFAULT NULL,
  `mr_date` INT NOT NULL DEFAULT 0,
  INDEX idx_mp_id (mp_id),
  INDEX idx_mr_from (mr_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_sessions` (
  `session_id` CHAR(32) PRIMARY KEY DEFAULT '',
  `session_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `session_ip` VARBINARY(45) DEFAULT NULL,
  `session_token` CHAR(100) NOT NULL DEFAULT '',
  `session_time` INT NOT NULL DEFAULT 0,
  `session_autologin` TINYINT NOT NULL DEFAULT 0,
  KEY `session_user_id` (`session_user_id`),
  KEY `session_time` (`session_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `u_suspension` (
  `susp_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT 0,
  `susp_causa` TEXT DEFAULT NULL,
  `susp_date` INT NOT NULL DEFAULT 0,
  `susp_termina` INT NOT NULL DEFAULT 0,
  `susp_mod` INT DEFAULT 0,
  `susp_ip` VARBINARY(45) DEFAULT NULL,
  INDEX idx_user (user_id),
  INDEX idx_mod (susp_mod)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_afiliados` (
  `aid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `a_titulo` VARCHAR(80) NOT NULL,
  `a_url` VARCHAR(255) NOT NULL,
  `a_banner` VARCHAR(255) DEFAULT NULL,
  `a_descripcion` VARCHAR(255) DEFAULT NULL,
  `a_sid` VARCHAR(32) DEFAULT NULL,
  `a_hits_in` INT UNSIGNED DEFAULT 0,
  `a_hits_out` INT UNSIGNED DEFAULT 0,
  `a_date` INT NOT NULL DEFAULT 0,
  `a_active` TINYINT DEFAULT 0,
  INDEX idx_active (a_active),
  INDEX idx_code (a_sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_configuracion` (
  `phpost_id` INT PRIMARY KEY,
  `titulo` VARCHAR(64) UNIQUE NOT NULL DEFAULT '',
  `slogan` VARCHAR(128) UNIQUE NOT NULL DEFAULT '',
  `url` VARCHAR(255) NOT NULL DEFAULT '',
  `email` VARCHAR(255) UNIQUE NOT NULL DEFAULT '',
  `banner` VARCHAR(255) NOT NULL DEFAULT '',
  `tema` VARCHAR(30) NOT NULL DEFAULT 'default',
  `ads_300` TEXT DEFAULT NULL,
  `ads_468` TEXT DEFAULT NULL,
  `ads_160` TEXT DEFAULT NULL,
  `ads_728` TEXT DEFAULT NULL,
  `ads_search` VARCHAR(100) NOT NULL DEFAULT '',
  `c_ver_vistas_global` TINYINT NOT NULL DEFAULT 0,
  `c_quitar_vistas_global` TINYINT NOT NULL DEFAULT 0,
  `c_visitas_tiempo` TINYINT NOT NULL DEFAULT 5,
  `c_avatar` TINYINT NOT NULL DEFAULT 0,
  `c_last_active` TINYINT NOT NULL DEFAULT 3,
  `c_allow_sess_ip` TINYINT NOT NULL DEFAULT 1,
  `c_count_guests` TINYINT NOT NULL DEFAULT 0,
  `c_fotos_private` TINYINT NOT NULL DEFAULT 0,
  `c_hits_guest` TINYINT NOT NULL DEFAULT 0,
  `c_keep_points` TINYINT NOT NULL DEFAULT 0,
  `c_allow_points` TINYINT NOT NULL DEFAULT 0,
  `c_max_posts` INT NOT NULL DEFAULT 16,
  `c_max_com` INT NOT NULL DEFAULT 25,
  `c_max_nots` INT NOT NULL DEFAULT 99,
  `c_max_acts` INT NOT NULL DEFAULT 99,
  `c_newr_type` TINYINT NOT NULL DEFAULT 0,
  `c_allow_ticket` TINYINT NOT NULL DEFAULT 0,
  `c_allow_foro` TINYINT NOT NULL DEFAULT 0,
  `c_allow_fuentes` TINYINT NOT NULL DEFAULT 0,
  `c_allow_sump` TINYINT NOT NULL DEFAULT 0,
  `c_allow_firma` TINYINT NOT NULL DEFAULT 1,
  `c_allow_upload` TINYINT NOT NULL DEFAULT 0,
  `c_allow_portal` TINYINT NOT NULL DEFAULT 1,
  `c_allow_live` TINYINT NOT NULL DEFAULT 1,
  `c_see_mod` TINYINT NOT NULL DEFAULT 0,
  `c_stats_cache` TINYINT NOT NULL DEFAULT 15,
  `c_desapprove_post` TINYINT NOT NULL DEFAULT 0,
  `offline` TINYINT NOT NULL DEFAULT 0,
  `offline_message` VARCHAR(255) NOT NULL DEFAULT 'Estamos en mantenimiento',
  `version` VARCHAR(30) NOT NULL DEFAULT '',
  `version_code` VARCHAR(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql[] = "INSERT INTO `w_configuracion` (`phpost_id`) VALUES (1);";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_registro` (
  `reg_id` INT PRIMARY KEY,
  `c_reg_active` TINYINT NOT NULL DEFAULT 1,
  `c_reg_activate` TINYINT NOT NULL DEFAULT 1,
  `c_reg_rango` INT NOT NULL DEFAULT 3,
  `c_met_welcome` TINYINT NOT NULL DEFAULT 0,
  `c_message_welcome` varchar(500) NOT NULL DEFAULT 'Hola [usuario], [welcome] a [b][web][/b].',
  `c_allow_edad` TINYINT NOT NULL DEFAULT 16,
  `captcha_provider` ENUM('recaptcha', 'hcaptcha', 'recaptcha_enterprise') NOT NULL DEFAULT 'recaptcha',
  `g_project_id` VARCHAR(255) NOT NULL DEFAULT '',
  `g_credentials_json` VARCHAR(255) NOT NULL DEFAULT '/secure/google.json',
  `public_key` VARCHAR(72) NOT NULL DEFAULT '',
  `secret_key` VARCHAR(72) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$phpost_sql[] = "INSERT INTO `w_registro` (`reg_id`) VALUES (1);";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_sitemap` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `url` VARCHAR(255) NULL,
  `frecuencia` VARCHAR(15) NOT NULL DEFAULT '',
  `fecha` INT NOT NULL DEFAULT 0,
  `prioridad` DECIMAL(2,1) NOT NULL DEFAULT 0,
  INDEX idx_url (url)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_sitemap_control` (
  `sid` INT PRIMARY KEY DEFAULT 0,
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
  INDEX idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_denuncias` (
  `did` INT AUTO_INCREMENT PRIMARY KEY,
  `d_date` INT NOT NULL DEFAULT 0,
  `d_extra` TEXT DEFAULT NULL,
  `d_razon` TINYINT NOT NULL,
  `d_total` SMALLINT NOT NULL DEFAULT 1,
  `d_type` ENUM('post','mensaje','usuario','foto') NOT NULL DEFAULT 'post',
  `d_user` INT NOT NULL,
  `obj_id` INT NOT NULL,
  INDEX idx_type (d_type),
  INDEX idx_obj (obj_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `user_email` VARCHAR(255) UNIQUE NOT NULL,
  `time` INT NOT NULL DEFAULT 0,
  `type` TINYINT NOT NULL DEFAULT 0,
  `hash` CHAR(128) NOT NULL DEFAULT '',
  `ip` VARBINARY(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_activate` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `user_email` VARCHAR(255) UNIQUE NOT NULL,
  `code_hash` CHAR(128) NOT NULL DEFAULT '',
  `expire_at` INT NOT NULL DEFAULT 0,
  `type` ENUM('activation','reset') DEFAULT 'activation',
  `used` TINYINT NOT NULL DEFAULT 0,
  `ip` VARBINARY(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_medallas` (
  `medal_id` INT AUTO_INCREMENT PRIMARY KEY,
  `m_autor` INT NOT NULL,
  `m_cant` INT NOT NULL DEFAULT 0,
  `m_cond_foto` INT DEFAULT 0,
  `m_cond_post` INT DEFAULT 0,
  `m_cond_user` INT DEFAULT 0,
  `m_cond_user_rango` INT DEFAULT 0,
  `m_cond_comunidad` INT DEFAULT 0,
  `m_cond_video` INT DEFAULT 0,
  `m_date` INT NOT NULL DEFAULT 0,
  `m_description` VARCHAR(255) NOT NULL DEFAULT '',
  `m_image` VARCHAR(150) NOT NULL DEFAULT '',
  `m_title` VARCHAR(50) NOT NULL DEFAULT '',
  `m_total` INT NOT NULL DEFAULT 0,
  `m_type` TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_medallas_assign` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `medal_id` INT NOT NULL,
  `medal_for` INT NOT NULL,
  `medal_date` INT NOT NULL DEFAULT 0,
  `medal_ip` VARBINARY(45) DEFAULT NULL,
  UNIQUE KEY unique_award (medal_id, medal_for)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_historial` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pofid` INT DEFAULT NULL,
  `type` TINYINT NOT NULL DEFAULT 0,
  `action` TINYINT NOT NULL DEFAULT 0,
  `mod` INT DEFAULT NULL,
  `reason` TEXT NULL,
  `date` INT NOT NULL DEFAULT 0,
  `mod_ip` VARBINARY(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_noticias` (
  `not_id` INT AUTO_INCREMENT PRIMARY KEY,
  `not_title` VARCHAR(100) NOT NULL DEFAULT '',
  `not_body` TEXT NULL,
  `not_autor` INT DEFAULT 0,
  `not_date` INT NOT NULL DEFAULT 0,
  `not_expires` INT NOT NULL DEFAULT 0,
  `not_type` TINYINT NOT NULL DEFAULT 0, # 0 Normal | 1 Importante | 2 Cambios
  `not_color` ENUM('info','success','warning','danger','primary','secondary') DEFAULT 'info', 
  `not_active` TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_blacklist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `type` TINYINT NOT NULL DEFAULT 0,
  `value` VARCHAR(100) NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `author` INT DEFAULT NULL,
  `date` INT NOT NULL DEFAULT 0,
  INDEX idx_value (value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_badwords` (
  `wid` INT AUTO_INCREMENT PRIMARY KEY,
  `word` VARCHAR(255) DEFAULT NULL,
  `swop` VARCHAR(255) DEFAULT NULL,
  `method` TINYINT NOT NULL DEFAULT 0,
  `type` TINYINT NOT NULL DEFAULT 0,
  `author` INT DEFAULT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `date` INT NOT NULL DEFAULT 0,
  INDEX idx_word (word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_stats` (
  `stats_no` INT PRIMARY KEY DEFAULT 0,
  `stats_max_online` BIGINT NOT NULL DEFAULT 0,
  `stats_max_time` INT NOT NULL DEFAULT 0,
  `stats_time` INT NOT NULL DEFAULT 0,
  `stats_time_cache` INT NOT NULL DEFAULT 0,
  `stats_time_foundation` INT NOT NULL DEFAULT 0,
  `stats_time_upgrade` INT NOT NULL DEFAULT 0,
  `stats_miembros` BIGINT NOT NULL DEFAULT 0,
  `stats_posts` BIGINT NOT NULL DEFAULT 0,
  `stats_fotos` BIGINT NOT NULL DEFAULT 0,
  `stats_comments` BIGINT NOT NULL DEFAULT 0,
  `stats_foto_comments` BIGINT NOT NULL DEFAULT 0,
  `stats_comunidades` BIGINT NOT NULL DEFAULT 0,
  `stats_temas` BIGINT NOT NULL DEFAULT 0,
  `stats_respuestas` BIGINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$phpost_sql[] = "INSERT INTO `w_stats` (`stats_no`, `stats_max_online`) VALUES (1, 0);";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_temas` (
  `tid` INT AUTO_INCREMENT PRIMARY KEY,
  `t_name` VARCHAR(72) NOT NULL DEFAULT '',
  `t_path` VARCHAR(72) NOT NULL DEFAULT '',
  `t_copy` VARCHAR(72) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_visitas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user` INT NOT NULL,
  `for` INT NOT NULL,
  `type` TINYINT NOT NULL DEFAULT 0,
  `date` INT NOT NULL DEFAULT 0,
  `ip` VARBINARY(45) DEFAULT NULL,
  INDEX (`for`, `type`, `user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_social` (
  `social_id` INT AUTO_INCREMENT PRIMARY KEY,
  `social_name` CHAR(22) NOT NULL DEFAULT '',
  `social_client_id` VARCHAR(255) NOT NULL DEFAULT '',
  `social_client_secret` VARCHAR(255) NOT NULL DEFAULT '',
  `social_redirect_uri` VARCHAR(255) NOT NULL DEFAULT '',
  `social_status` TINYINT NOT NULL DEFAULT 0,
  `social_icon` VARCHAR(20) NOT NULL DEFAULT '',
  INDEX idx_status (social_status),
  INDEX idx_name (social_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(120) NOT NULL UNIQUE,
  `executed_at` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";

$phpost_sql[] = "INSERT INTO `w_migrations` (`id`, `migration`, `executed_at`) VALUES
(1, 'video_platforms', 1770957195),
(2, 'categorias', 1770825789),
(3, 'permisos', 1770409335),
(4, 'configuracion_registro', 1770409300),
(5, 'post_excerpt', 1772591038),
(6, 'miembro_tema', 1772591048),
(7, 'configuracion_puntos_ilimitados', 1772591095),
(8, 'comentarios_votos', 1772591095),
(9, 'denuncias', 1772601095),
(10, 'voto_type', 1772738395),
(11, 'comentarios_level', 1772747280);";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `w_video_platforms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL DEFAULT '',
  `domain` VARCHAR(255) NOT NULL DEFAULT '',
  `path_pattern` TEXT NOT NULL,
  `embed_template` TEXT NOT NULL,
  `enabled` TINYINT(1) DEFAULT 1,
  `created_at` INT NOT NULL DEFAULT 0,
  `updated_at` INT NOT NULL DEFAULT 0,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";

$phpost_sql[] = "INSERT INTO `w_video_platforms` (`id`, `name`, `domain`, `path_pattern`, `embed_template`, `enabled`, `created_at`, `updated_at`) VALUES
(1, 'youtube', 'youtube.com', '/watch?v={id}', '<iframe width=\"640\" height=\"360\" src=\"https://www.youtube.com/embed/{id}\" frameborder=\"0\" allowfullscreen></iframe>', 1, 1770957196, 0),
(2, 'youtube_short', 'youtu.be', '/{id}', '<iframe width=\"640\" height=\"360\" src=\"https://www.youtube.com/embed/{id}\" frameborder=\"0\" allowfullscreen></iframe>', 1, 1770957196, 0),
(3, 'vimeo', 'vimeo.com', '/{id}', '<iframe width=\"640\" height=\"360\" src=\"https://player.vimeo.com/video/{id}\" frameborder=\"0\" allowfullscreen></iframe>', 1, 1770957196, 0),
(4, 'dailymotion', 'dailymotion.com', '/video/{id}', '<iframe frameborder=\"0\" width=\"640\" height=\"360\" src=\"https://www.dailymotion.com/embed/video/{id}\" allowfullscreen></iframe>', 1, 1770957196, 0),
(5, 'tiktok', 'tiktok.com', '/@{username}/video/{id}', '<blockquote class=\"tiktok-embed\" cite=\"{url}\"><a href=\"{url}\"></a></blockquote><script async src=\"https://www.tiktok.com/embed.js\"></script>', 0, 1770957196, 0),
(6, 'twitch', 'twitch.com', '/videos/{id}', '<iframe src=\"https://player.twitch.tv/?video={id}&parent=localhost&autoplay=false\" width=\"640\" height=\"360\" frameborder=\"0\" allowfullscreen></iframe>', 0, 1770957196, 0),
(7, 'instagram', 'instagram.com', '/p/{id}', '<blockquote class=\"instagram-media\" data-instgrm-permalink=\"{url}\"><a href=\"{url}\"></a></blockquote><script async defer src=\"//www.instagram.com/embed.js\"></script>', 0, 1770957196, 0),
(8, 'streamable', 'streamable.com', '/p/{id}', '<iframe src=\"https://streamable.com/e/{id}\" width=\"640\" height=\"360\" frameborder=\"0\" allowfullscreen></iframe>', 0, 1770957196, 0);";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `post_collaborators` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `status` ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
  `invited_by` TINYINT NOT NULL DEFAULT 0,
  `created_at` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";

$phpost_sql[] = "CREATE TABLE IF NOT EXISTS `post_changes_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `change_note` TEXT NOT NULL,
  `changed_at` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";