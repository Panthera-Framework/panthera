DROP TABLE IF EXISTS `{$db_prefix}custom_pages`;

CREATE TABLE "{$db_prefix}custom_pages" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "unique" VARCHAR NOT NULL,
  "url_id" VARCHAR NOT NULL,
  "title" VARCHAR NOT NULL,
  "meta_tags" VARCHAR NULL,
  "html" text NULL,
  "author_name" VARCHAR NOT NULL,
  "author_id" INTEGER NOT NULL,
  "language" VARCHAR NOT NULL DEFAULT 'polski',
  "created" timestamp DEFAULT CURRENT_TIMESTAMP,
  "mod_author_name" VARCHAR NULL,
  "mod_author_id" INTEGER NULL,
  "mod_time" timestamp DEFAULT '0000-00-00 00:00:00',
  "admin_tpl" VARCHAR NULL,
  "description" VARCHAR NULL,
  "image" VARCHAR NULL,
  "public" INT DEFAULT '0'
);

CREATE INDEX "{$db_prefix}custom_pages_url_id" ON "{$db_prefix}custom_pages" ("url_id");
