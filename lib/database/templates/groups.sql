DROP TABLE IF EXISTS `{$db_prefix}groups`;

CREATE TABLE `{$db_prefix}groups` (
  `group_id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL COMMENT 'Group name (identifier)',
  `description` varchar(128) NOT NULL,
  `attributes` text NOT NULL COMMENT 'ACL attributes of a group',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

