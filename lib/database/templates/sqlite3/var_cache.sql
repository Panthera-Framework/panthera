DROP TABLE IF EXISTS `{$db_prefix}var_cache`;

CREATE TABLE "{$db_prefix}var_cache" (
  "var" TEXT,
  "value" TEXT,
  "expire" INTEGER NOT NULL
);

CREATE INDEX "{$db_prefix}var_cache_var" ON "{$db_prefix}var_cache" ("var")
