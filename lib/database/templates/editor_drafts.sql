DROP TABLE IF EXISTS `{$db_prefix}editor_drafts`;

CREATE TABLE IF NOT EXISTS `{$db_prefix}editor_drafts` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `directory` varchar(64) NOT NULL COMMENT 'Category/directory',
  `author_id` int(8) NOT NULL,
  `content` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `textid` varchar(64) NOT NULL COMMENT 'Hash of cleaned up content to avoid duplications',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;
