CREATE TABLE "{$db_prefix}newsletter_users" (
  "id" mediumint(8) NOT NULL  COMMENT 'Entry id',
  "nid" smallint(5) NOT NULL COMMENT 'Newsletter category id',
  "address" varchar(60) NOT NULL COMMENT 'E-mail or jabber or other adress',
  "type" varchar(8) NOT NULL DEFAULT 'mail' COMMENT 'Type of `adress` eg. jabber, mail or other if supported',
  "added" datetime NOT NULL,
  "cookieid" varchar(256) NOT NULL DEFAULT '' COMMENT 'Cookie id to identify user (if not logged in, optional)',
  "userid" int(6) NOT NULL DEFAULT '-1' COMMENT 'User id (if logged in, so its optional)',
  "unsubscribe_id" varchar(32) DEFAULT '' COMMENT 'Can be used to allow users removing their subscriptions',
  "activate_id" varchar(64) DEFAULT '' COMMENT 'Used to confirm subscription',
  PRIMARY KEY ("id")
);
