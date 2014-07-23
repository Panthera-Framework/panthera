CREATE TABLE IF NOT EXISTS `{$db_prefix}categories` (
`categoryid` int(8) NOT NULL PRIMARY KEY,
  `categoryType` varchar(32) NOT NULL,
  `parentid` int(8) DEFAULT NULL,
  `title` varchar(32) DEFAULT NULL,
  `priority` int(6) NOT NULL DEFAULT '100',
  `optionalfield_1` varchar(32) NOT NULL,
  `optionalfield_2` varchar(32) NOT NULL,
  `__public` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;