DROP TABLE IF EXISTS `{$db_prefix}private_messages`;

CREATE TABLE "{$db_prefix}private_messages" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "title" TEXT NOT NULL,
  "sender" TEXT NOT NULL,
  "sender_id" INTEGER NOT NULL,
  "recipient" TEXT NOT NULL,
  "recipient_id" INTEGER NOT NULL,
  "content" TEXT NOT NULL,
  "directory" TEXT NOT NULL DEFAULT 'inbox',
  "sent" timestamp NOT NULL ,
  "visibility_sender" INTEGER NOT NULL,
  "visibility_recipient" INTEGER NOT NULL
);
