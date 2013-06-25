CREATE TABLE IF NOT EXISTS `{$db_prefix}menu_categories` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(32) NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` varchar(128) NOT NULL,
  `parent` smallint(3) NOT NULL DEFAULT '0',
  `elements` int(5) NOT NULL COMMENT 'cache',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;
