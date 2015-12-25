Migrations directory
====================

In .content/Schema/DatabaseMigrations will be created all database migrations when you use `migrations create XYZ` command
Additionally there is a deployment task `Build/Database/UpdateSystemMigrations` that keeps PF2 migrations in your application up to date.
Please don't remove files from directory "ignored" as there lands all PF2 migrations that you rejected from copying into your project.