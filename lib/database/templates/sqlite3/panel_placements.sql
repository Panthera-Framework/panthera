DROP TABLE IF EXISTS `{$db_prefix}panels_placements`;

CREATE TABLE `{$db_prefix}panels_placements` (
  `placementid` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` VARCHAR,
  `title` VARCHAR
);

CREATE INDEX "{$db_prefix}panels_placements_placementid" ON "{$db_prefix}panels_placements" ("placementid");
