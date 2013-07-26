DROP TABLE IF EXISTS `{$db_prefix}newsletters`;

CREATE TABLE "{$db_prefix}newsletters" (
  "nid" INTEGER PRIMARY KEY AUTOINCREMENT,
  "title" TEXT NOT NULL,
  "users" INTEGER NOT NULL,
  "attributes" text NOT NULL,
  "created" datetime NOT NULL,
  "default_type" TEXT NOT NULL DEFAULT 'mail'
);

CREATE INDEX "{$db_prefix}newsletters_title" ON "{$db_prefix}newsletters" ("title");
