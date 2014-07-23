CREATE TABLE IF NOT EXISTS `{$db_prefix}groups_users_history` (
  `joinid` varchar(32) NOT NULL UNIQUE KEY,
  `userid` int(8) NOT NULL,
  `groupid` int(8) NOT NULL,
  `joined` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;