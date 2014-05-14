DROP TABLE IF EXISTS `{$db_prefix}mails`;

CREATE TABLE IF NOT EXISTS `{$db_prefix}mails` (
  `template` varchar(32) NOT NULL COMMENT 'Mail template name (eg. passwordRecovery for /templates/_mails/pl/passwordRecovery.tpl)',
  `enabled` tinyint(1) NOT NULL,
  `default_subject` text NOT NULL COMMENT 'Serialized array with string for every language',
  `fallback_language` varchar(16) NOT NULL,
  UNIQUE KEY `template` (`template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;