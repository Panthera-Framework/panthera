Panthera Framework
========

Our project bet on simplicity, kiss rules and performance. We are targeting to application developers wanting create their apps 10 times faster than with any other tools. Why Panthera? Our project contains READY TO USE, fully customizable administration panel built on top of Panthera Framework. There is no need to create a new administration panel every project if there is already a good one. Ready to use simple objective interfaces and modules are making Panthera easiest framework ever.

## Panthera API examples

##### Editing user account

```php
$user = new pantheraUser('login', 'webnull');
$user -> changePassword('test123');
$user -> mail = 'example@example.org';

$user -> save(); // optional (it will be saved automaticaly when script execution ends)
```

##### Creating own database table and managing it in objetive model

Create table of our example table

```sql
CREATE TABLE IF NOT EXISTS `pa_cars` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `color` varchar(16) NOT NULL,
   PRIMARY KEY (`id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;
```

Turn database records into objects

```php
<?php
// this class will represent a record from "cars" table (pa_ is a prefix)
class car extends pantheraFetchDB
{
    protected $_tableName = 'cars'; // table name without prefix
    protected $_idColumn = 'id';
    
    protected $_constructBy = array(
        'id',
        'name',
        'array',
    );
}
```

Manage database records using a simple class

```php
$panthera -> db -> insert('cars', array(
    'name' => 'Ferrari X',
    'color' => 'blue',
));

$panthera -> db -> insert('cars', array(
    'name' => 'Fiat Y',
    'color' => 'red',
));

// list all cars
foreach (car::fetchAll() as $car)
    print($car -> name. " is " .$car -> color. " color<br>\n");

$ferrari = new car('name', 'Ferrari X');
$ferrari -> color = 'white';
$ferrari -> save(); // updates cache and database now, not at the end of script

// list all cars again
foreach (car::fetchAll() as $car)
    print($car -> name. " is " .$car -> color. " color<br>\n");
```

## Our Team
- Damian Kęska - co-founder, main programmer, translator, website maintainer
- Mateusz Warzyński - co-founder, programmer, translator, website maintainer
- Zoran Karavla - main graphics designer, giving us helpful tips about project design
- Dawid Niedźwiedzki - tester

## Notice
Panthera is still in beta development, we already made an installer, composer integration and many things to make it easier to install.

## Installation
Installation of Panthera Framework is very simple, but at this moment requires shell access to the server. In near future we plan making zipped packages with all dependencies to allow just place Panthera on shared hosting using FTP.

So, lets download fresh contents.

```bash
git clone https://github.com/webnull/panthera
cd panthera
./install.sh
```
And if you are not using account WWW server is using, you should allow your Nginx, Lighttpd or Apache to write to example-site directory.
It requires to create some directories and files, so make it writable.

```bash
chown www-data example-site -R
```
