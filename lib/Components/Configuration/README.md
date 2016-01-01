Configuration
=============

Configuration is a small size data storage for storing configuration keys that could be retrieved globally in application.
Basic configuration is placed in /.content/app.php, extended configuration is placed in database in `configuration` table.

## Using configuration from code

> **Please note:**
> Keys defined in app.php cannot be changed from Configuration interface from code

Retrieving a configuration key:

```php
<?php
Framework::getInstance()->config->get('my-key');
```

Setting a configuration key:

```php
<?php
// set a value
Framework::getInstance()->config->set('my-key', 123);
```

Retrieving a key, if no any value set, then set defaults:
```php
<?php
Framework::getInstance()->config->get('my-key', 'defaults to this text');
```