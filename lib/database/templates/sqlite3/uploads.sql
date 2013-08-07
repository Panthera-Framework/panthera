DROP TABLE IF EXISTS `{$db_prefix}uploads`;

CREATE TABLE "{$db_prefix}uploads" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "category" TEXT NOT NULL DEFAULT 'default',
  "location" TEXT NOT NULL,
  "description" TEXT NOT NULL,
  "icon" TEXT NOT NULL,
  "mime" TEXT NOT NULL,
  "uploader_id" INTEGER NOT NULL,
  "uploader_login" TEXT NOT NULL,
  "protected" INTEGER NOT NULL DEFAULT '0',
  "public" INTEGER NOT NULL DEFAULT '1'
);
