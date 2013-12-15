DROP TABLE IF EXISTS `{$db_prefix}gallery_items`;

CREATE TABLE `{$db_prefix}gallery_items` (
  `id` mediumint(6) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(256) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `url_id` varchar(64) NOT NULL COMMENT 'unique SEO url id',
  `link` varchar(256) NOT NULL,
  `thumbnail` varchar(256) NOT NULL,
  `gallery_id` mediumint(4) NOT NULL,
  `visibility` tinyint(1) NOT NULL,
  `upload_id` int(8) NOT NULL,
  `author_id` varchar(32) NOT NULL,
  `author_login` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_id` (`url_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

