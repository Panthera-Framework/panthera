DROP TABLE IF EXISTS `{$db_prefix}metas`;

CREATE TABLE "{$db_prefix}metas" (
  "metaid" INTEGER PRIMARY KEY AUTOINCREMENT,
  "name" TEXT NOT NULL,
  "value" TEXT NOT NULL,
  "type" TEXT NOT NULL,
  "userid" TEXT NOT NULL
);

CREATE INDEX "{$db_prefix}metas_metaid" ON "{$db_prefix}metas" ("metaid");
