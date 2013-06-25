CREATE TABLE IF NOT EXISTS `{$db_prefix}config_overlay` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `key` varchar(128) NOT NULL,
  `value` varchar(8096) NOT NULL,
  `type` varchar(16) NOT NULL DEFAULT 'string',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
