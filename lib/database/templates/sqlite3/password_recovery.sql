DROP TABLE IF EXISTS `{$db_prefix}password_recovery`;

CREATE TABLE "{$db_prefix}password_recovery" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "recovery_key" TEXT NOT NULL,
  "user_login" TEXT NOT NULL,
  "date" timestamp NOT NULL ,
  "new_passwd" TEXT NOT NULL
);
