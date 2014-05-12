DROP TABLE IF EXISTS `{$db_prefix}users_lastlogin_history`;

CREATE TABLE "{$db_prefix}users_lastlogin_history" (
  "hashid" VARCHAR PRIMARY KEY,
  "uid" INTEGER NOT NULL,
  "date" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "useragent" VARCHAR NOT NULL,
  "retries" INTEGER NOT NULL,
  "location" VARCHAR NOT NULL,
  "system" VARCHAR NOT NULL,
  "browser" VARCHAR NOT NULL,
  "ip" VARCHAR NOT NULL
);