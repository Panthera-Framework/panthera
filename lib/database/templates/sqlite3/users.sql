DROP TABLE IF EXISTS `{$db_prefix}users`;

CREATE TABLE "{$db_prefix}users" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "login" TEXT NOT NULL,
  "passwd" TEXT NOT NULL,
  "full_name" TEXT NOT NULL,
  "primary_group" TEXT DEFAULT 'users',
  "joined" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "attributes" TEXT NOT NULL,
  "language" TEXT NOT NULL DEFAULT 'polski',
  "mail" TEXT NOT NULL,
  "jabber" TEXT NOT NULL,
  "profile_picture" TEXT NOT NULL,
  "lastlogin" datetime NOT NULL,
  "lastip" TEXT NOT NULL
);

CREATE INDEX "{$db_prefix}users_login" ON "{$db_prefix}users" ("login");
