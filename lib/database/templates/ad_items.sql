CREATE TABLE IF NOT EXISTS `{$db_prefix}ad_items` (
`adid` int(8) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `placename` varchar(32),
  `name` varchar(128),
  `htmlcode` text,
  `position` int(6),
  `created` datetime,
  `expires` datetime,
  `authorid` int(6),
  `modified` datetime
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;