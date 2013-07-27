DROP TABLE IF EXISTS `{$db_prefix}groups`;

CREATE TABLE "{$db_prefix}groups" (
  "group_id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "name" VARCHAR NOT NULL,
  "description" VARCHAR DEFAULT ''
);
