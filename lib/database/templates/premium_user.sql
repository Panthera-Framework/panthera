CREATE TABLE IF NOT EXISTS `{$db_prefix}premium_user` (
`id` int(8) NOT NULL PRIMARY KEY,
  `userid` int(8) NOT NULL,
  `premiumid` int(8) NOT NULL,
  `expires` datetime NOT NULL,
  `data` text NOT NULL,
  `additionalfield1` varchar(64) NOT NULL,
  `additionalfield2` varchar(64) NOT NULL,
  `paymentstatus` varchar(16) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `paymentdetails` varchar(1024) NOT NULL,
  `activationdate` datetime NOT NULL,
  `requestdate` datetime NOT NULL,
  `starts` datetime NOT NULL,
  `requiresstart` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;