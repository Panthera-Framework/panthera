CREATE TABLE IF NOT EXISTS `{$db_prefix}gallery_categories` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `author_login` varchar(32) NOT NULL,
  `author_id` int(6) NOT NULL,
  `language` varchar(16) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `visibility` tinyint(1) NOT NULL,
  `author_full_name` varchar(30) NOT NULL,
  `thumb_id` varchar(64) NOT NULL,
  `thumb_url` varchar(196) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
