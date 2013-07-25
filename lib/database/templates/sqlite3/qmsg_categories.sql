CREATE TABLE "{$db_prefix}qmsg_categories" (
  "category_id" mediumint(4) NOT NULL ,
  "title" varchar(128) NOT NULL,
  "description" varchar(256) NOT NULL,
  "category_name" varchar(16) NOT NULL,
  PRIMARY KEY ("category_id")
);

CREATE INDEX "{$db_prefix}qmsg_categories_category_name" ON "{$db_prefix}qmsg_categories" ("category_name");
