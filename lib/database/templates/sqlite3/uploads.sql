DROP TABLE IF EXISTS `{$db_prefix}uploads`;

CREATE TABLE "{$db_prefix}uploads" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "category" TEXT NOT NULL,
  "location" TEXT NOT NULL,
  "description" TEXT NOT NULL,
  "icon" TEXT NOT NULL,
  "mime" TEXT NOT NULL,
  "uploader_id" INTEGER NOT NULL,
  "uploader_login" TEXT NOT NULL,
  "protected" INTEGER NOT NULL,
  "public" INTEGER NOT NULL,
  "title" TEXT NOT NULL,
  "maxfilesize" INTEGER NOT NULL
);
