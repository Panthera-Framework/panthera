CREATE TABLE "{$db_prefix}uploads" (
  "id" int(8) NOT NULL ,
  "category" varchar(32) NOT NULL DEFAULT 'default',
  "location" varchar(256) NOT NULL,
  "description" varchar(512) NOT NULL,
  "icon" varchar(256) NOT NULL,
  "mime" varchar(32) NOT NULL,
  "uploader_id" int(6) NOT NULL,
  "uploader_login" varchar(32) NOT NULL,
  "protected" tinyint(1) NOT NULL DEFAULT '0',
  "public" tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
);
