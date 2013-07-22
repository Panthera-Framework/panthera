DROP TABLE IF EXISTS `{$db_prefix}password_recovery`;

CREATE TABLE `{$db_prefix}password_recovery` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `recovery_key` varchar(128) NOT NULL,
  `user_login` varchar(32) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `new_passwd` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

