DROP TABLE IF EXISTS `{$db_prefix}qmsg_categories`;

CREATE TABLE "{$db_prefix}qmsg_categories" (
  "category_id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "title" TEXT NOT NULL,
  "description" TEXT NOT NULL,
  "category_name" TEXT NOT NULL,
  "created" datetime default current_timestamp,
  "author_id" INTEGER NOT NULL
);

CREATE INDEX "{$db_prefix}qmsg_categories_category_name" ON "{$db_prefix}qmsg_categories" ("category_name");
