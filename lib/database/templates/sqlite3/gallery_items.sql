CREATE TABLE "{$db_prefix}gallery_items" (
  "id" mediumint(6) NOT NULL ,
  "title" varchar(128) NOT NULL,
  "description" varchar(256) NOT NULL,
  "created" timestamp NOT NULL ,
  "url_id" varchar(64) NOT NULL COMMENT 'unique SEO url id',
  "link" varchar(256) NOT NULL,
  "thumbnail" varchar(256) NOT NULL,
  "gallery_id" mediumint(4) NOT NULL,
  "visibility" tinyint(1) NOT NULL,
  "upload_id" int(8) NOT NULL,
  PRIMARY KEY ("id")
);

CREATE INDEX "{$db_prefix}gallery_items_url_id" ON "{$db_prefix}gallery_items" ("url_id");
