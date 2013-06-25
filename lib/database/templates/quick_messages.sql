CREATE TABLE IF NOT EXISTS `{$db_prefix}quick_messages` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `unique` varchar(16) NOT NULL,
  `title` varchar(128) NOT NULL,
  `message` text NOT NULL,
  `author_login` varchar(32) NOT NULL,
  `author_full_name` varchar(64) NOT NULL,
  `mod_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `visibility` tinyint(1) NOT NULL,
  `mod_author_login` varchar(32) NOT NULL,
  `mod_author_full_name` varchar(64) NOT NULL,
  `url_id` varchar(128) NOT NULL COMMENT 'SEO url',
  `language` varchar(16) NOT NULL,
  `category_name` varchar(16) NOT NULL,
  `icon` varchar(256) NOT NULL COMMENT 'optional - link to image to set as icon',
  `viewcount` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_id` (`url_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
