DROP TABLE IF EXISTS `{$db_prefix}panels`;

CREATE TABLE `{$db_prefix}panels` (
  `id` int(6) NOT NULL AUTO_INCREMENT COMMENT 'Unique ID',
  `placement` varchar(32) NOT NULL COMMENT 'ID/name of a placement on template',
  `order` int(3) NOT NULL COMMENT 'Item order in placement',
  `module` varchar(64) NOT NULL COMMENT 'Module name (can be optional if panel is just a static template file)',
  `template` varchar(64) NOT NULL COMMENT 'Template file to load instead of module',
  `title` varchar(128) NOT NULL COMMENT 'Panel title',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `storage` varchar(8192) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;
