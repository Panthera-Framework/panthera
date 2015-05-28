DROP TABLE IF EXISTS `{$db_prefix}custom_pages`;

CREATE TABLE `{$db_prefix}custom_pages` (
  `id` int(128) NOT NULL AUTO_INCREMENT,
  `unique` varchar(32) NOT NULL COMMENT 'Unique id for all languages',
  `url_id` varchar(128) NOT NULL COMMENT 'id for SEO urls',
  `title` varchar(128) NOT NULL,
  `meta_tags` varchar(256) NOT NULL,
  `html` text NULL,
  `author_name` varchar(64) NOT NULL,
  `author_id` int(6) NOT NULL,
  `language` varchar(16) NOT NULL DEFAULT 'polski',
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mod_author_name` varchar(64) NULL,
  `mod_author_id` int(6) NULL,
  `mod_time` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `admin_tpl` varchar(24) NULL COMMENT 'Template file name for admin panel',
  `description` varchar(1024) NULL,
  `image` varchar(256) NULL,
  `public` tinyint(1) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_id` (`url_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

