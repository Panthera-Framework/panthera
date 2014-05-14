DROP TABLE IF EXISTS `{$db_prefix}mails`;

CREATE TABLE "{$db_prefix}mails" (
  "template" TEXT PRIMARY KEY,
  "enabled" INTEGER NOT NULL,
  "default_subject" TEXT NOT NULL,
  "fallback_language" TEXT NOT NULL
);
