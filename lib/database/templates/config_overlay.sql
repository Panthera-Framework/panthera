DROP TABLE IF EXISTS `{$db_prefix}config_overlay`;

CREATE TABLE `{$db_prefix}config_overlay` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `key` varchar(128) NOT NULL,
  `value` varchar(8096) NOT NULL,
  `type` varchar(16) NOT NULL DEFAULT 'string',
  `section` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;

