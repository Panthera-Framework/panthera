Components
==========

Components are sets of classes that are functioning for same functionality.
A good example is "Cache" component, there are multiple cache handlers plus CacheLoader.

Main goal of Components is to provide core functionality like your own session handling, database drivers
but not the functionality of the application itself.

Core components are delivered with the framework itself, but the structure allows you to put your modified
components here and extend or replace existing ones.

Autoload will look first in application's /Components directory, and then into the framework, please notice that
and do not modify framework files itself.