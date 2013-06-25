CREATE TABLE IF NOT EXISTS `{$db_prefix}newsletters` (
  `nid` smallint(5) NOT NULL AUTO_INCREMENT,
  `title` varchar(320) NOT NULL COMMENT 'Title or a category of a newsletter (unique, so we can use it to build a object) Note: remember to trim spaces',
  `users` int(8) NOT NULL COMMENT 'Cache of users count',
  `attributes` text NOT NULL COMMENT 'Extra attributes (eg. can be used by plugins to add extra functionality)',
  `created` datetime NOT NULL COMMENT 'Creation date',
  `default_type` varchar(8) NOT NULL DEFAULT 'mail' COMMENT 'Default contact type',
  PRIMARY KEY (`nid`),
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
