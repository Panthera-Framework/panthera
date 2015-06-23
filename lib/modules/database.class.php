<?php
namespace Panthera;

class database extends baseClass
{
    public $object = null;

    public static function getDatabaseInstance($databaseType)
    {
        $framework = framework::getInstance();
        $path = $framework->getPath('modules/databaseHandlers/' .$databaseType. 'DatabaseHandler.class.php');
        $className = '\\Panthera\\' .$databaseType. 'DatabaseHandler';

        require $path;

        if (!in_array('Panthera\databaseHandlerInterface', class_implements($className)))
        {
            throw new \Panthera\PantheraFrameworkException('Database handler "' .$className. '" have to implement "databaseHandlerInterface" interface', 'FW_INVALID_DRIVER');
        }

        $object = new $className;
        $object->connect();

        return $object;
    }
}

interface databaseHandlerInterface
{
    public function connect();
    //public function select();
    //public function insert();
    //public function update();
}