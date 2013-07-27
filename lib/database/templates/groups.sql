DROP TABLE IF EXISTS `{$db_prefix}groups`;

CREATE TABLE `{$db_prefix}groups` (
  `group_id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL COMMENT 'Group name (identifier)',
  `description` varchar(128) DEFAULT ''
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

