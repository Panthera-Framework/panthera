DROP TABLE IF EXISTS `{$db_prefix}cronjobs`;

CREATE TABLE "{$db_prefix}cronjobs" (
  "jobid" INTEGER PRIMARY KEY AUTOINCREMENT,
  "jobname" VARCHAR NOT NULL,
  "data" VARCHAR NOT NULL,
  "minute" VARCHAR NOT NULL DEFAULT '*',
  "hour" VARCHAR NOT NULL DEFAULT '*',
  "day" VARCHAR NOT NULL DEFAULT '*',
  "month" VARCHAR NOT NULL DEFAULT '*',
  "year" VARCHAR NOT NULL DEFAULT '*',
  "weekday" VARCHAR NOT NULL DEFAULT '*',
  "next_interation" INTEGER NOT NULL,
  "lock" INTEGER NOT NULL DEFAULT '0',
  "count_left" INTEGER NOT NULL DEFAULT '-1',
  "count_executed" INTEGER NOT NULL DEFAULT '0',
  "created" datetime NOT NULL,
  "start_time" INTEGER NOT NULL
);

CREATE INDEX "{$db_prefix}cronjobs_jobname" ON "{$db_prefix}cronjobs" ("jobname");
