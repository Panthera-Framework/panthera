DROP TABLE IF EXISTS `{$db_prefix}gallery_items`;

CREATE TABLE "{$db_prefix}gallery_items" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "title" varchar NOT NULL,
  "description" varchar NOT NULL,
  "created" timestamp NOT NULL ,
  "url_id" varchar NOT NULL,
  "link" varchar NOT NULL,
  "thumbnail" varchar NOT NULL,
  "gallery_id" int NOT NULL,
  "visibility" int NOT NULL,
  "upload_id" int NOT NULL
);

CREATE INDEX "{$db_prefix}gallery_items_url_id" ON "{$db_prefix}gallery_items" ("url_id");
