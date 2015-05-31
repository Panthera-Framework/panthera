DROP TABLE IF EXISTS `{$db_prefix}uploads`;

CREATE TABLE "{$db_prefix}uploads" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "category" TEXT,
  "location" TEXT,
  "description" TEXT,
  "icon" TEXT,
  "mime" TEXT,
  "uploader_id" INTEGER NOT NULL,
  "uploader_login" TEXT,
  "protected" INTEGER,
  "public" INTEGER,
  "title" TEXT,
  "maxfilesize" INTEGER,
  "filename" TEXT,
  "created" datetime NOT NULL
);
