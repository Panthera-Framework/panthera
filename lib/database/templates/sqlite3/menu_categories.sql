DROP TABLE IF EXISTS `{$db_prefix}menu_categories`;

CREATE TABLE "{$db_prefix}menu_categories" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "type_name" TEXT NOT NULL,
  "title" TEXT NOT NULL,
  "description" TEXT NOT NULL,
  "parent" INTEGER NOT NULL DEFAULT '0',
  "elements" INTEGER NOT NULL
);
