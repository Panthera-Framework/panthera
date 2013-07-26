DROP TABLE IF EXISTS `{$db_prefix}gallery_categories`;

CREATE TABLE "{$db_prefix}gallery_categories" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT ,
  "unique" VARCHAR NOT NULL,
  "title" VARCHAR NOT NULL,
  "author_login" VARCHAR NOT NULL,
  "author_id" INTEGER NOT NULL,
  "language" VARCHAR NOT NULL,
  "created" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "modified" timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  "visibility" INTEGER NOT NULL,
  "author_full_name" VARCHAR NOT NULL,
  "thumb_id" VARCHAR NOT NULL,
  "thumb_url" VARCHAR NOT NULL
);
