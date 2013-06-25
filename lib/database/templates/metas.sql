CREATE TABLE IF NOT EXISTS `{$db_prefix}metas` (
  `metaid` int(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `value` varchar(128) NOT NULL,
  `type` varchar(32) NOT NULL,
  `userid` varchar(16) NOT NULL,
  PRIMARY KEY (`metaid`),
  KEY `metaid` (`metaid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;
