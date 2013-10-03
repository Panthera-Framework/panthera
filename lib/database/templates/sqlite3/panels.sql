DROP TABLE IF EXISTS `{$db_prefix}panels`;

CREATE TABLE `{$db_prefix}panels` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `placement` VARCHAR,
  `order` INTEGER,
  `module` VARCHAR,
  `template` VARCHAR,
  `title` VARCHAR DEFAULT '',
  `enabled` INTEGER DEFAULT '',
  `storage` VARCHAR DEFAULT '',
);

CREATE INDEX "{$db_prefix}panels_id" ON "{$db_prefix}panels" ("id");
