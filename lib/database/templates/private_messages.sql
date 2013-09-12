SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `{$db_prefix}private_messages` (
  id int(8) NOT NULL AUTO_INCREMENT,
  title varchar(128) NOT NULL,
  sender varchar(32) NOT NULL,
  sender_id int(6) NOT NULL,
  recipient varchar(32) NOT NULL,
  recipient_id int(6) NOT NULL,
  content varchar(2048) NOT NULL,
  `directory` varchar(32) NOT NULL DEFAULT 'inbox' COMMENT 'directory/folder where message will be shown (default: inbox)',
  sent timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  visibility_sender tinyint(1) NOT NULL,
  visibility_recipient tinyint(1) NOT NULL,
  seen tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
SET FOREIGN_KEY_CHECKS=1;
