<?php
$appIndex = array (
  'path_translations' => 
  array (
    0 => 'packages/admin/dashboard/translations',
  ),
  'autoloader' => 
  array (
    '\\NodeVisitor_signalSearcher' => '$LIB$/modules/deployment/SignalIndexing.class.php',
    '\\PantheraFrameworkTemplatingTestCase' => '$LIB$/modules/tests/phpunit.bootstrap.php',
    '\\PantheraFrameworkTestCase' => '$LIB$/modules/tests/phpunit.bootstrap.php',
    '\\Panthera\\BaseFrameworkClass' => '$LIB$/modules/framework.class.php',
    '\\Panthera\\ControllerException' => '$LIB$/modules/BaseExceptions.php',
    '\\Panthera\\CoreSingleton' => '$LIB$/modules/CoreSingleton.class.php',
    '\\Panthera\\DatabaseException' => '$LIB$/modules/BaseExceptions.php',
    '\\Panthera\\FileException' => '$LIB$/modules/BaseExceptions.php',
    '\\Panthera\\FileNotFoundException' => '$LIB$/modules/BaseExceptions.php',
    '\\Panthera\\InvalidConfigurationException' => '$LIB$/modules/BaseExceptions.php',
    '\\Panthera\\PantheraFrameworkException' => '$LIB$/modules/BaseExceptions.php',
    '\\Panthera\\Signals' => '$LIB$/modules/Signals.class.php',
    '\\Panthera\\SyntaxException' => '$LIB$/modules/BaseExceptions.php',
    '\\Panthera\\ValidationException' => '$LIB$/modules/BaseExceptions.php',
    '\\Panthera\\applicationIndex' => '$LIB$/modules/applicationIndex.class.php',
    '\\Panthera\\cache\\SQLite3Cache' => '$LIB$/modules/cache/SQLite3Cache.class.php',
    '\\Panthera\\cache\\cache' => '$LIB$/modules/cache/cache.class.php',
    '\\Panthera\\cli\\application' => '$LIB$/modules/cli/application.class.php',
    '\\Panthera\\configuration' => '$LIB$/modules/configuration.class.php',
    '\\Panthera\\core\\controllers\\BaseFrameworkController' => '$LIB$/modules/controllers/BaseController.php',
    '\\Panthera\\core\\controllers\\Response' => '$LIB$/modules/controllers/Response.php',
    '\\Panthera\\cron' => '$LIB$/modules/cron.class.php',
    '\\Panthera\\database\\ORMBaseFrameworkObject' => '$LIB$/modules/databaseObjects/ORMBaseObject.class.php',
    '\\Panthera\\database\\Pagination' => '$LIB$/modules/database.class.php',
    '\\Panthera\\database\\SQLite3DatabaseHandler' => '$LIB$/modules/databaseHandlers/SQLite3DatabaseHandler.class.php',
    '\\Panthera\\database\\column' => '$LIB$/modules/databaseHandlers/SQLite3DatabaseHandler.class.php',
    '\\Panthera\\database\\driver' => '$LIB$/modules/database.class.php',
    '\\Panthera\\database\\select' => '$LIB$/modules/database.class.php',
    '\\Panthera\\deployment\\task' => '$LIB$/modules/deployment/task.class.php',
    '\\Panthera\\framework' => '$LIB$/modules/framework.class.php',
    '\\Panthera\\indexService' => '$LIB$/modules/indexService.class.php',
    '\\Panthera\\locale' => '$LIB$/modules/locale.class.php',
    '\\Panthera\\logging' => '$LIB$/modules/logging.class.php',
    '\\Panthera\\model\\user' => '$LIB$/modules/databaseObjects/user.class.php',
    '\\Panthera\\template' => '$LIB$/modules/template.class.php',
    '\\Panthera\\utils\\arrayUtils' => '$LIB$/modules/utils/arrayUtils.class.php',
    '\\Panthera\\utils\\classUtils' => '$LIB$/modules/utils/classUtils.class.php',
    '\\SignalIndexing' => '$LIB$/modules/deployment/SignalIndexing.class.php',
    '\\dashboardModule' => '$LIB$/packages/admin/dashboard/modules/dashboardModule.class.php',
    '\\test' => '$APP$.content/modules/test.class.php',
    '\\testORMModel' => '$LIB$/modules/tests/phpunit.bootstrap.php',
  ),
  'signals' => 
  array (
    'UI.Admin.template.menu' => 
    array (
      0 => 
      array (
        'type' => 'signal',
        'call' => '\\dashboardModule::attachToAdminMenu',
        'file' => '$LIB$/packages/admin/dashboard/modules/dashboardModule.class.php',
      ),
    ),
  ),
);