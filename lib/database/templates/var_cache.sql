DROP TABLE IF EXISTS `{$db_prefix}var_cache`;

CREATE TABLE `{$db_prefix}var_cache` (
  `var` varchar(128) NOT NULL,
  `value` varchar(2048) NOT NULL,
  `expire` int(20) NOT NULL,
  UNIQUE KEY `var` (`var`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

