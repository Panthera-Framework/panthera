CREATE TABLE "{$db_prefix}var_cache" (
  "var" varchar(128) NOT NULL,
  "value" varchar(2048) NOT NULL,
  "expire" int(20) NOT NULL
);

CREATE INDEX "{$db_prefix}var_cache_var" ON "{$db_prefix}var_cache" ("var")
