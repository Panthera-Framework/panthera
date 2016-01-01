Autoloader
==========

Automatically loads missing classes basing on class namespace and on indexing service.

There is an important rule:
```
Your custom file in same place in project structure as in framework's place has always highest priority
eg.
/framework/Components/Test/Test.php
/application/Components/Test/Test.php # this one would be loaded, as its overloading first one
```

### Namespaces
Behavior is pretty simple, for example component \Panthera\Components\Database\Pagination would be
autoloaded from $DIR/Components/Database/Pagination.php

```
Please note that it supports your custom namespace defined in a constant PF2_NAMESPACE, which is PFApplication by default.
```

### Indexing service
If file is not in standard structure, the will be automatically catches from Indexing service that is collecting
information about all project files.