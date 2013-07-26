CREATE TABLE "{$db_prefix}menus" (
  "id" int(4) PRIMARY KEY,
  "type" varchar(32) NOT NULL,
  "title" varchar(128) NOT NULL,
  "attributes" varchar(1024) NOT NULL,
  "link" varchar(512) NOT NULL,
  "language" varchar(32) NOT NULL DEFAULT 'polski',
  "url_id" varchar(16) NOT NULL,
  "order" smallint(3) NOT NULL,
  "icon" varchar(256) NOT NULL,
  "tooltip" varchar(128) NOT NULL
);
