ORM Component
=============

ORM allows to map database tables into PHP classes, and records into objects.

Currently it's possible to:
- Map table into a PHP class
  - Fetching from database by:
    - Column
    - Conditions
  - Joining tables right/left, outer/inner
  - Saving
  - Deleting objects
- Validate input data by PHPDoc @param (class variables)

Todo:
- Dependencies support
- @orm timestampOnUpdate
- Deployment task for creating empty entities

### @orm tag values:
- "leftJoin", "outerJoin", "innerJoin", "leftOuterJoin" or "rightJoin"
- timestampOnCreate (when field is null then current timestamp is inserted)
- timestampOnUpdate (timestamp is inserted on every object save)

## Preparing class

To link a database row into code we need to know what's the table name and it's id (primary index) column.
Our class should extend `ORMBaseFrameworkObject` or it's child class if any.

```php
<?php
class UserEntity extends ORMBaseFrameworkObject
{
    protected static $__ORM_Table = 'users';
    protected static $__ORM_IdColumn = 'user_id';
}
```


## Basic tables mapping

Base thing we want to achieve is to link column from database into our code to make operations on database as simple as possible.

```php
<?php
class UserEntity extends ORMBaseFrameworkObject
{
   (...)

    /**
     * @orm
     * @column user_id
     * @var integer
     */
    protected $userId;
    
    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * @return $this
     */
    public function setUserId($uid)
    {
        $this->userId = (int)$uid;
        return $this;
    }
}
```

`@column` tag is a database table column name, `@var` is required for data type validation, and `@orm` means that class property is used in object relational mapping.
Also please notice getters and setters, it's a good practice to have getters and setters instead of exposing public class variables.

## Joining tables

To join a table into our entity we only need to add a two PHPDoc attributes.

#### 1. ORM join attribute

We can use "leftJoin", "outerJoin", "innerJoin", "leftOuterJoin" or "rightJoin" as equivalent of `LEFT JOIN`/`RIGHT JOIN`.

`**Please note:** Only "leftJoin" and "leftOuterJoin" is supported by SQLite3.`

Example:
```php
/**
 * @orm leftJoin
 * (...)
 */
```

#### 2. JOIN attribute

Join attribute has it's own, but very simple syntax.
At first we need to specify column from secondary table that would match value from our local table and `@column`, and then list of all columns we additionally want to select.

```php
/**
 * @orm rightJoin
 * @join "groups.group_id" => Select columns: "group_id as gid, group_name"
 * @column user_primary_group
 * @var int
 */
 protected $userGroupId;
```

There is also a possibility to add a RAW SQL code for advanced usage.
Advanced example, using SQL:

```php
/**
 * @orm rightJoin
 * @join r"$mainTable.user_primary_group = groups.group_id", Table: "groups" => Select columns: "group_id as gid, group_name"
 * @column user_primary_group
 * @var int
 */
 protected $userGroupId;
```

"r" at the beginning means it's a RAW SQL string, in extended example we **must provide separately a table name**
and also columns to select in SELECT clause.
This example is doing exactly the same as previous example, but it's in RAW SQL.


Results:
```bash
[2015-12-28 20:00:55 1451329255.3652][vendor/pantheraframework/panthera/lib/Components/Orm/ORMBaseFrameworkObject.php:116]  Executing query: SELECT groups.group_id as gid, groups.group_name as group_name, s1.* FROM `users` as s1 LEFT OUTER JOIN groups ON ( groups.group_id = user_id) WHERE ( s1.user_id = :user_id_c08f80d4 ) ORDER BY s1.user_id ASC LIMIT 1 OFFSET 0 , data: {
    "user_id_c08f80d4": 1
} 
=> Panthera\Components\SystemUser\Entities\UserEntity {#196
     +userId: "1",
     +userLogin: "root",
     +userFirstName: null,
     +userLastName: null,
     +userPassword: "$2y$12$awZOz8KzBRVDrxmPLOMAIuGlIG0EKRFWiJ5gLEdgqOjm69rBdQigW",
     +userEmail: "root@localhost",
     +userCreated: "2015-12-28 13:24:27",
     +userUpdated: null,
     +userPrimaryGroup: "1",
   }
>>> $u->getUserPrimaryGroupName()
=> "Users"
```

## Using Entities

#### [Finding entity by id:](https://asciinema.org/a/bgfs01acete5yuqfjcruxs8k3)

```php
>>> $u = new Panthera\Components\SystemUser\Entities\UserEntity(1);
=> Panthera\Components\SystemUser\Entities\UserEntity {#195
     +userId: "1",
     +userLogin: "root",
     +userFirstName: null,
     +userLastName: null,
     +userPassword: "$2y$12$awZOz8KzBRVDrxmPLOMAIuGlIG0EKRFWiJ5gLEdgqOjm69rBdQigW",
     +userEmail: "root@localhost",
     +userCreated: "2015-12-28 13:24:27",
     +userUpdated: null,
     +userPrimaryGroup: "1",
   }
>>> $u->getLogin()
=> "root"
```

#### [Fetching entities by conditions:](https://asciinema.org/a/599llshiggxwn9yq7oj1vt6ez)

```php
>>> Panthera\Components\SystemUser\Entities\UserEntity::fetch(['|=|user_id' => 1])
=> [
     Panthera\Components\SystemUser\Entities\UserEntity {#209
       +userId: "1",
       +userLogin: "root",
       +userFirstName: null,
       +userLastName: null,
       +userPassword: "$2y$12$awZOz8KzBRVDrxmPLOMAIuGlIG0EKRFWiJ5gLEdgqOjm69rBdQigW",
       +userEmail: "root@localhost",
       +userCreated: "2015-12-28 13:24:27",
       +userUpdated: null,
       +userPrimaryGroup: "1",
     },
   ]
```

As you can see there is an array of results, but you can also look up for just one record using `fetchOne()` function.

#### Editing entity and saving

```php
$u = new Panthera\Components\SystemUser\Entities\UserEntity(1);

>>> $u->getLogin() // root

$u->setLogin('new-root');

>>> $u->getLogin() // new-root

$u->save();

// testing
$u = new Panthera\Components\SystemUser\Entities\UserEntity(1);

>>> $u->getLogin(); // new-root
```

#### [Deleting entity](https://asciinema.org/a/e262pl7bxlutem1mfuh4axb2b)
```php
$u = new Panthera\Components\SystemUser\Entities\UserEntity(1);
$u->delete();
```