DROP TABLE IF EXISTS `{$db_prefix}cronjobs`;

CREATE TABLE `{$db_prefix}cronjobs` (
  `jobid` bigint(16) NOT NULL AUTO_INCREMENT,
  `jobname` varchar(64) NOT NULL,
  `data` text NOT NULL,
  `minute` varchar(6) NOT NULL DEFAULT '*',
  `hour` varchar(6) NOT NULL DEFAULT '*',
  `day` varchar(6) NOT NULL DEFAULT '*',
  `month` varchar(6) NOT NULL DEFAULT '*',
  `year` varchar(6) NOT NULL DEFAULT '*',
  `weekday` varchar(6) NOT NULL DEFAULT '*',
  `next_interation` bigint(25) NOT NULL COMMENT 'Unix timestamp of next execution time',
  `lock` bigint(25) NOT NULL DEFAULT '0' COMMENT 'Lock time, 0 if not locked',
  `count_left` int(6) NOT NULL DEFAULT '-1' COMMENT 'how much executions left to delete this job',
  `count_executed` bigint(16) NOT NULL DEFAULT '0' COMMENT 'execution times',
  `created` datetime NOT NULL,
  `start_time` int(64) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`jobid`),
  UNIQUE KEY `jobname` (`jobname`)
) ENGINE=InnoDB AUTO_INCREMENT=4884 DEFAULT CHARSET=utf8;

