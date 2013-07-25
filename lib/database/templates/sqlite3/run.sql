CREATE TABLE "{$db_prefix}run" (
  "rid" int(16) NOT NULL ,
  "pid" bigint(32) NOT NULL,
  "name" varchar(64) NOT NULL,
  "storage" varchar(8192) NOT NULL,
  "started" double NOT NULL,
  "expired" double NOT NULL,
  PRIMARY KEY ("rid")
);

CREATE INDEX "{$db_prefix}run_rid" ON "{$db_prefix}run" ("rid");
