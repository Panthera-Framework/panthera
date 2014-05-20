DROP TABLE IF EXISTS `{$db_prefix}upload_categories`;

CREATE TABLE `{$db_prefix}upload_categories` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(32),
  `author_id` int(6) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 10:30:00',
  `mime_type` varchar(320) NOT NULL,
  `maxfilesize` bigint(32) NOT NULL,
  `protected` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
