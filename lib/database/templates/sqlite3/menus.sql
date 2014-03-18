DROP TABLE IF EXISTS `{$db_prefix}menus`;

CREATE TABLE "{$db_prefix}menus" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "type" VARCHAR NOT NULL,
  "title" VARCHAR NOT NULL,
  "attributes" VARCHAR NOT NULL,
  "link" VARCHAR NOT NULL,
  "language" VARCHAR NOT NULL DEFAULT 'polski',
  "url_id" VARCHAR NOT NULL,
  "order" INT NOT NULL,
  "icon" VARCHAR NOT NULL,
  "tooltip" VARCHAR NOT NULL,
  "route" VARCHAR NOT NULL DEFAULT '',
  "routedata" VARCHAR NOT NULL DEFAULT '',
  "routeget" VARCHAR NOT NULL DEFAULT '',
  "enabled" INT NOT NULL DEFAULT 0
);
