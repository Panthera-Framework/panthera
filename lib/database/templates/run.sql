DROP TABLE IF EXISTS `{$db_prefix}run`;

CREATE TABLE `{$db_prefix}run` (
  `rid` int(16) NOT NULL AUTO_INCREMENT,
  `pid` bigint(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `storage` varchar(8192) NOT NULL,
  `started` double NOT NULL,
  `expired` double NOT NULL,
  PRIMARY KEY (`rid`),
  KEY `pid` (`pid`),
  KEY `rid` (`rid`),
  KEY `rid_2` (`rid`)
) ENGINE=MEMORY AUTO_INCREMENT=1987 DEFAULT CHARSET=utf8;

