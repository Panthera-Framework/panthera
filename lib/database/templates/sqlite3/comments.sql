DROP TABLE IF EXISTS `{$db_prefix}comments`;

CREATE TABLE "{$db_prefix}comments" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT ,
  "title" INTEGER NOT NULL,
  "content" VARCHAR NOT NULL,
  "date" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "modified" timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  "mod_author_id" INTEGER NOT NULL,
  "mod_author_login" VARCHAR NOT NULL,
  "author_id" INTEGER NOT NULL,
  "author_login" VARCHAR NOT NULL,
  "votes_up" INTEGER NOT NULL DEFAULT '0',
  "votes_down" INTEGER NOT NULL DEFAULT '0',
  "votes_rank" INTEGER NOT NULL DEFAULT '0',
  "content_id" VARCHAR NOT NULL
);
