DROP TABLE IF EXISTS `{$db_prefix}qmsg_categories`;

CREATE TABLE `{$db_prefix}qmsg_categories` (
  `category_id` mediumint(4) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(256) NOT NULL,
  `category_name` varchar(16) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

