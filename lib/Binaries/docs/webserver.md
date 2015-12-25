"webserver" tool
================

Standalone tool for setting up a web server. 
It's able to deploy a configuration from template and run a web server process.
Default web server is "PHP", it's a built-in PHP Web Server. Not recommended for production usage.

## Customizing deployment
In WebserverApplication::$webServers there is a list of available web servers.
'deployment' key in array is a command that will be executed before starting the server, 'command' is a startup command.


Examples:

```bash
webserver --server PHP start
```