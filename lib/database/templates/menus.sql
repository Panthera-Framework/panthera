CREATE TABLE IF NOT EXISTS `{$db_prefix}menus` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL COMMENT 'link belongs to grup of this name',
  `title` varchar(128) NOT NULL,
  `attributes` varchar(1024) NOT NULL COMMENT 'serialized array with attributes',
  `link` varchar(512) NOT NULL,
  `language` varchar(32) NOT NULL DEFAULT 'polski',
  `url_id` varchar(16) NOT NULL,
  `order` smallint(3) NOT NULL,
  `icon` varchar(256) NOT NULL COMMENT 'url adress to icon',
  `tooltip` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
