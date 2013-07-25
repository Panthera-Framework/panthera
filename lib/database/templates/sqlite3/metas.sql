CREATE TABLE "{$db_prefix}metas" (
  "metaid" int(20) NOT NULL ,
  "name" varchar(128) NOT NULL,
  "value" varchar(128) NOT NULL,
  "type" varchar(32) NOT NULL,
  "userid" varchar(16) NOT NULL,
  PRIMARY KEY ("metaid")
);

CREATE INDEX "{$db_prefix}metas_metaid" ON "{$db_prefix}metas" ("metaid");
