DROP TABLE IF EXISTS `{$db_prefix}editor_drafts`;

CREATE TABLE "{$db_prefix}editor_drafts" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "directory" VARCHAR,
  "author_id" INTEGER,
  "content" VARCHAR DEFAULT '',
  "date" TIMESTAMP,
  "textid" VARCHAR DEFAULT ''
);

CREATE INDEX "{$db_prefix}editor_drafts_key" ON "{$db_prefix}editor_drafts ("key");
