CREATE TABLE IF NOT EXISTS `{$db_prefix}users` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `login` varchar(32) NOT NULL,
  `passwd` varchar(128) NOT NULL,
  `full_name` varchar(64) NOT NULL,
  `primary_group` varchar(32) NOT NULL,
  `joined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attributes` varchar(2048) NOT NULL,
  `language` varchar(16) NOT NULL DEFAULT 'polski',
  `mail` varchar(32) NOT NULL,
  `jabber` varchar(32) NOT NULL,
  `profile_picture` varchar(196) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
