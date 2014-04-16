DROP TABLE IF EXISTS `{$db_prefix}users_lastlogin_history`;

CREATE TABLE `{$db_prefix}users_lastlogin_history` (
  `hashid` varchar(64) NOT NULL COMMENT 'This is an unique identifier (used instead of integer)',
  `uid` int(6) NOT NULL COMMENT 'User ID',
  `date` datetime NOT NULL COMMENT 'Event date',
  `useragent` varchar(128) NOT NULL COMMENT 'User Agent string',
  `retries` smallint(3) NOT NULL COMMENT 'Count of retries before success',
  `location` varchar(64) NOT NULL COMMENT 'Optional location info (if avaliable)',
  `system` varchar(16) NOT NULL COMMENT 'Operating system',
  `browser` varchar(16) NOT NULL COMMENT 'Browser',
  PRIMARY KEY (`hashid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8