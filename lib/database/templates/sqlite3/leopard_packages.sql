DROP TABLE IF EXISTS `{$db_prefix}leopard_package`;

CREATE TABLE "{$db_prefix}leopard_packages" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "name" varchar(64) NOT NULL,
  "manifest" text NOT NULL,
  "installed_as" varchar(8) NOT NULL DEFAULT 'manual',
  "version" float NOT NULL DEFAULT '0.1',
  "release" int(3) NOT NULL DEFAULT '1',
  "status" varchar(12) NOT NULL DEFAULT 'broken'
);
