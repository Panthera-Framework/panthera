CREATE TABLE "{$db_prefix}users" (
  "id" int(6) NOT NULL ,
  "login" varchar(32) NOT NULL,
  "passwd" varchar(128) NOT NULL,
  "full_name" varchar(64) NOT NULL,
  "primary_group" varchar(32) NOT NULL,
  "joined" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "attributes" varchar(2048) NOT NULL,
  "language" varchar(16) NOT NULL DEFAULT 'polski',
  "mail" varchar(32) NOT NULL,
  "jabber" varchar(32) NOT NULL,
  "profile_picture" varchar(196) NOT NULL,
  "lastlogin" datetime NOT NULL,
  "lastip" varchar(16) NOT NULL,
  PRIMARY KEY ("id")
);

CREATE INDEX "{$db_prefix}users_login" ON "{$db_prefix}users" ("login");
