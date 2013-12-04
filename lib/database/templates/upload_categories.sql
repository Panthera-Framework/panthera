DROP TABLE IF EXISTS `{$db_prefix}upload_categories`;

CREATE TABLE `{$db_prefix}upload_categories` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `author_id` int(6) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mime_type` varchar(240) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
