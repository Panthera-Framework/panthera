DROP TABLE IF EXISTS `{$db_prefix}quick_messages`;

CREATE TABLE "{$db_prefix}quick_messages" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "unique" TEXT NOT NULL,
  "title" TEXT NOT NULL,
  "message" text NOT NULL,
  "author_login" TEXT NOT NULL,
  "author_full_name" TEXT NOT NULL,
  "mod_time" timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  "visibility" INTEGER NOT NULL,
  "mod_author_login" TEXT NOT NULL,
  "mod_author_full_name" TEXT NOT NULL,
  "url_id" TEXT NOT NULL,
  "language" TEXT NOT NULL,
  "category_name" TEXT NOT NULL,
  "icon" TEXT NOT NULL,
  "viewcount" INTEGER NOT NULL DEFAULT '0'
);

CREATE INDEX "{$db_prefix}quick_messages_url_id" ON "{$db_prefix}quick_messages" ("url_id");
