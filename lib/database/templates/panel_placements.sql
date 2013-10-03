DROP TABLE IF EXISTS `{$db_prefix}panels_placements`;

CREATE TABLE `{$db_prefix}panels_placements` (
  `placementid` smallint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL COMMENT 'ID that is passed to template and used to identify its panels',
  `title` varchar(128) NOT NULL COMMENT 'User friendly title',
  PRIMARY KEY (`placementid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin2 AUTO_INCREMENT=0 ;
