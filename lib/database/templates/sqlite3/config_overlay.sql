CREATE TABLE "{$db_prefix}config_overlay" (
  "id" INTEGER PRIMARY KEY,
  "key" varchar(128),
  "value" varchar(8096),
  "type" varchar(16) DEFAULT 'string'
);

CREATE INDEX "{$db_prefix}config_overlay_key" ON "{$db_prefix}config_overlay" ("key");
