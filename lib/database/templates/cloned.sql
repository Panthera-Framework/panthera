DROP TABLE IF EXISTS `{$db_prefix}cloned`;

CREATE TABLE IF NOT EXISTS `{$db_prefix}cloned` (
  `id` mediumint(10) NOT NULL AUTO_INCREMENT,
  `hash` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
