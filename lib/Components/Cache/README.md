Cache
=====

Simple key-value storage for storing objects, arrays, lists and all things that could be cached.
Every entry has an expiration time. Storage method is unrestricted, it could be in memory, database, files or other.

Every cache "Driver" should implement CacheInterface and correct namespace.

Things to remember when writing a cache driver:

- namespaces
- interface
- support for serialized objects and arrays
- exact match types of passed values (don't do mistakes when comparing eg. 0 with null)
- exists() method should also check if key was not expired, if expired then it does not exists