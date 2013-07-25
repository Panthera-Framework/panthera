CREATE TABLE "{$db_prefix}groups" (
  "group_id" int(4) NOT NULL ,
  "name" varchar(32) NOT NULL COMMENT 'Group name (identifier)',
  "description" varchar(128) NOT NULL,
  "attributes" text NOT NULL COMMENT 'ACL attributes of a group',
  PRIMARY KEY ("group_id")
);
