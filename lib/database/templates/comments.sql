DROP TABLE IF EXISTS `{$db_prefix}comments`;

CREATE TABLE `{$db_prefix}comments` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `content` varchar(256) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mod_author_id` int(6) NOT NULL,
  `mod_author_login` varchar(32) NOT NULL,
  `author_id` int(6) NOT NULL,
  `author_login` varchar(32) NOT NULL,
  `votes_up` int(6) NOT NULL DEFAULT '0',
  `votes_down` int(11) NOT NULL DEFAULT '0',
  `votes_rank` smallint(3) NOT NULL DEFAULT '0',
  `content_id` varchar(64) NOT NULL COMMENT 'Content this comment belong to, eg. gallery item "gallery_item_1" or "quickmessage_1"',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

