CREATE TABLE IF NOT EXISTS `{$db_prefix}ad_places` (
  `placename` varchar(32) UNIQUE KEY,
  `title` varchar(256),
  `description` varchar(1024)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;