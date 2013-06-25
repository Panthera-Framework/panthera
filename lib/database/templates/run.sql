CREATE TABLE IF NOT EXISTS `{$db_prefix}run` (
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
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
