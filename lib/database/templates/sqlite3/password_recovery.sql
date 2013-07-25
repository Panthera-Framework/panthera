CREATE TABLE "{$db_prefix}password_recovery" (
  "id" int(6) NOT NULL ,
  "recovery_key" varchar(128) NOT NULL,
  "user_login" varchar(32) NOT NULL,
  "date" timestamp NOT NULL ,
  "new_passwd" varchar(128) NOT NULL,
  PRIMARY KEY ("id")
);
