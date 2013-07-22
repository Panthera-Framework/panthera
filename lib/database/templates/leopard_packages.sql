DROP TABLE IF EXISTS `{$db_prefix}leopard_packages`;

CREATE TABLE `{$db_prefix}leopard_packages` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT 'For easy database indexing',
  `name` varchar(64) NOT NULL COMMENT 'Package name',
  `manifest` text NOT NULL COMMENT 'Manifest contents (serialized array)',
  `installed_as` varchar(8) NOT NULL DEFAULT 'manual' COMMENT 'Installed manual or as a dependency',
  `version` float NOT NULL DEFAULT '0.1' COMMENT 'eg. 1.01',
  `release` int(3) NOT NULL DEFAULT '1' COMMENT 'Release number',
  `status` varchar(12) NOT NULL DEFAULT 'broken' COMMENT 'Package status: installed/broken',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

