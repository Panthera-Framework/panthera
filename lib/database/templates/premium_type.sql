CREATE TABLE IF NOT EXISTS `{$db_prefix}premium_type` (
  `premiumid` varchar(16) NOT NULL  PRIMARY KEY,
  `title` varchar(256) NOT NULL,
  `groupid` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;