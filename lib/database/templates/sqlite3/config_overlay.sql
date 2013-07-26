DROP TABLE IF EXISTS `{$db_prefix}config_overlay`;

CREATE TABLE "{$db_prefix}config_overlay" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "key" VARCHAR,
  "value" VARCHAR DEFAULT '',
  "type" VARCHAR DEFAULT 'string',
  "section" VARCHAR DEFAULT ''
);

CREATE INDEX "{$db_prefix}config_overlay_key" ON "{$db_prefix}config_overlay" ("key");
