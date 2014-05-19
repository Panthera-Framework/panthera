DROP TABLE IF EXISTS `{$db_prefix}run`;

CREATE TABLE "{$db_prefix}run" (
  "rid" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  "pid" INTEGER NOT NULL,
  "name" VARCHAR NOT NULL,
  "storage" VARCHAR NOT NULL,
  "started" REAL NOT NULL,
  "expired" REAL NOT NULL
);

CREATE INDEX "{$db_prefix}run_rid" ON "{$db_prefix}run" ("rid");
