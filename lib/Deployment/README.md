Deployment system
=================

Deployment system is a platform for running automation scripts.
Built-in `deploy` command available from Panthera Framework 2 shell allows
running deployment scripts with various options.

### Usage

Type `deploy -m` to get a list of all available tasks for your project.
The list includes also tasks built-in Panthera Framework 2.

To see all available options for selected task type eg.

```bash
$ deploy Build/Database/UpdateSystemMigrations --help`

# Arguments defined by current tasks:
#  (Build/Database/UpdateSystemMigrations) --yes   Automatically agree to copy all migrations
```

In case you want to check which tasks would be ran as dependent of one you typed you could use "--check-dependencies" switch.

Example:

```bash
$ deploy Tests --check-dependencies

# - Tests/BuildTestDatabase
# - Tests/PHPUnitConfigure
# - Tests/PHPUni
```

When there is a rare case that for any reason one of dependent task cannot execute you could skip it using "--exclude" switch, use comma separator to skip multiple tasks.

### Convention

We assume that: $TaskName is our task name.

1. Naming, every class has to be named $TaskNameTask, with "Task" suffix.
2. Namespace should be \YourAPP\Deployment\Directory1\SubDirectory1

Example:
```
Task name: Build/Environment/Developer
 Placed in: /Deployment/Build/Environment/DeveloperTask.php
 Namespace: \Panthera\Deployment\Build\Environment
 Class name: DeveloperTask
```

Folders and subfolders are used to group tasks. Tasks that are named same as folder they are stored in
are "index tasks" eg. "Tests/TestsTask.php" will be considered not as "Tests/Tests" but just as "Tests".