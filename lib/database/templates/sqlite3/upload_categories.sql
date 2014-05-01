DROP TABLE IF EXISTS `{$db_prefix}upload_categories`;

CREATE TABLE "{$db_prefix}upload_categories" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "name" TEXT NOT NULL DEFAULT,
  "author_id" INTEGER NOT NULL,
  "created" TIMESTAMP NOT NULL,
  "modified" TIMESTAMP NOT NULL,
  "mime_type" TEXT NOT NULL,
  "title" TEXT NOT NULL,
  "maxfilesize" TEXT NOT NULL,
  "protected" INTEGER NOT NULL
);