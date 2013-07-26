DROP TABLE IF EXISTS `{$db_prefix}leopard_files`;

CREATE TABLE "{$db_prefix}leopard_files" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "path" VARCHAR NOT NULL,
  "md5" VARCHAR NOT NULL,
  "package" VARCHAR NOT NULL,
  "created" datetime NOT NULL,
  "dependencies" VARCHAR NOT NULL
);
