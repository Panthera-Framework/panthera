DROP TABLE IF EXISTS `{$db_prefix}newsletter_users`;

CREATE TABLE "{$db_prefix}newsletter_users" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "nid" INTEGER NOT NULL,
  "address" TEXT NOT NULL,
  "type" TEXT NOT NULL DEFAULT 'mail',
  "added" datetime NOT NULL,
  "cookieid" TEXT NOT NULL DEFAULT '',
  "userid" INTEGER NOT NULL DEFAULT '-1',
  "unsubscribe_id" TEXT DEFAULT '',
  "activate_id" TEXT DEFAULT ''
);
