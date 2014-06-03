DROP TABLE IF EXISTS `{$db_prefix}users_comments`;

CREATE TABLE `{$db_prefix}users_comments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `content` varchar(256) NOT NULL,
  `author_id` int(6) NOT NULL,
  `group` varchar(64) NOT NULL,
  `object_id` int(6) NOT NULL,
  `posted` date NOT NULL,
  `modified` date NOT NULL,
  `allowed` smallint(1) NOT NULL,
  `mod_author_id` int(6) NOT NULL DEFAULT '0',
  `votes_up` int(6) NOT NULL DEFAULT '0',
  `votes_down` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

