<?php
$appIndex = array (
  'path_translations' => 
  array (
    0 => 'Packages/admin/dashboard/translations',
  ),
  'autoloader' => 
  array (
    '\\PFApplication\\Packages\\example\\ExampleAPIController' => '$APP$.content/packages/example/controllers/ExampleAPIController/ExampleAPIController.php',
    '\\Panthera\\Components\\Controller\\BaseFrameworkController' => '$LIB$/Components/Controller/BaseController.php',
    '\\Panthera\\Components\\Orm\\ORMBaseFrameworkObject' => '$LIB$/Components/Orm/ORMBaseObject.php',
    '\\Panthera\\Packages\\admin\\dashboard\\DashboardController' => '$LIB$/Packages/admin/dashboard/controllers/DashboardController/DashboardController.php',
    '\\test' => '$APP$.content/modules/test.class.php',
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
  'Routes' => 
  array (
    '`^/admin/dashboard$`' => 
    array (
      'matches' => 
      array (
      ),
      'controller' => '\\Panthera\\Packages\\admin\\dashboard\\DashboardController',
      'original' => '/admin/dashboard',
      'methods' => 'GET|POST',
      'priority' => 999,
    ),
  ),
);
