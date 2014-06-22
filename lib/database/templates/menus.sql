DROP TABLE IF EXISTS `{$db_prefix}menus`;

CREATE TABLE `{$db_prefix}menus` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL COMMENT 'link belongs to grup of this name',
  `title` varchar(128) NOT NULL,
  `attributes` varchar(1024) NOT NULL COMMENT 'serialized array with attributes',
  `link` varchar(512) NULL,
  `language` varchar(32) NOT NULL DEFAULT 'polski',
  `url_id` varchar(16) NULL,
  `order` smallint(4) NOT NULL,
  `icon` varchar(256) NULL COMMENT 'url adress to icon',
  `tooltip` varchar(128) NULL,
  `route` varchar(32) NULL,
  `routedata` varchar(512) NULL,
  `routeget` varchar(512) NULL,
  `enabled` tinyint(4) NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;