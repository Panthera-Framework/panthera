<?php
namespace Panthera\Components\Orm;

use Panthera\Classes\BaseExceptions\DatabaseException;
use Panthera\Classes\BaseExceptions\InvalidDefinitionException;
use Panthera\Classes\BaseExceptions\ValidationException;

use Panthera\Classes\Utils\ClassUtils;
use Panthera\Classes\Utils\StringUtils;
use Panthera\Components\Database\Column;
use Panthera\Components\Database\Pagination;

use Panthera\Components\Kernel\BaseFrameworkClass;
use Panthera\Components\Kernel\Framework;

/**
 * Basic abstract ORM model
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\database
 */
abstract class ORMBaseFrameworkObject extends BaseFrameworkClass
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $__ORM_Table = '';

    /**
     * What to select
     *
     * @var string|array
     */
    protected static $__ORM_What = '*';

    /**
     * Order by statement
     *
     * @var string
     */
    protected static $__ORM_Order = '';

    /**
     * Group by statement
     *
     * @var array
     */
    protected static $__ORM_Group = '';

    /**
     * Joined tables
     * Don't touch, it's a cache entry :-)
     *
     * @var array
     */
    protected static $__ORM_Joins = [];

    /**
     * List of columns to select on every fetch
     *
     * @var array
     */
    protected static $__ORM_SelectColumns = [];

    /**
     * List of joined column per table
     *
     * @var array
     */
    protected $__ORM_JoinedColumns = [];

    /**
     * Id column - table specific
     *
     * @var string
     */
    protected static $__ORM_IdColumn = 'id';

    /**
     * Internal cache for columns mapping
     *
     * @var array
     */
    protected $__ORM_MetaMapping = [];

    /**
     * PHPDoc types mapping to what @see gettype() function returns
     *
     * @var array $phpTypes
     */
    protected $phpTypes = [
        'int'       => [ 'integer', 'numeric' ],
        'float'     => [ 'numeric', 'float' ],
        'bool'      => [ 'integer', 'string' ],
        'array'     => [ 'array' ],
        'resource'  => [ 'object', 'resource' ],
    ];

    /**
     * Construct object by results from database
     *
     * @param array|int|null $data Result set of a SQL query or an object id
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct($data = null)
    {
        /** @see \Panthera\baseClass::__construct **/
        parent::__construct();
        $this->__rebuildJoinsData();
        $this->__rebuildColumnsMapping();

        if (is_int($data))
        {
            $this->selectObjectById($data);
        }
        elseif (is_array($data))
        {
            $this->remapDatabaseResult($data);
        }
        /**
         * else:
         *  if null is passed then we are creating a new object
         */
    }

    /**
     * Construct object by id
     *
     * @param int|string $id Numerical id
     * @throws DatabaseException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    protected function selectObjectById($id)
    {
        $what = static::$__ORM_SelectColumns;
        $what[] = '*';

        $where = array(
            '|=|' .static::$__ORM_IdColumn. '' => $id,
        );

        $result = $this->app->database->select(static::$__ORM_Table, $what, $where, static::$__ORM_IdColumn, null, new Pagination(1, 1), array(), static::$__ORM_Joins);

        if (is_array($result))
        {
            if (empty($result))
            {
                throw new DatabaseException('Record not found for id=' . $id, 'SQL_NO_RESULT');
            }

            $this->remapDatabaseResult($result[0]);
            return true;
        }

        return false;
    }

    /**
     * Get columns mapping, store in cache
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function __rebuildColumnsMapping()
    {
        $this->__ORM_MetaMapping = array_merge($this->__ORM_MetaMapping, ORMMetaDataProvider::getColumnsMapping($this));
    }

    /**
     * Rebuilds information about used SQL joins for entity
     *
     * @throws InvalidDefinitionException
     */
    protected function __rebuildJoinsData()
    {
        // don't rebuild if joins was typed statically into class
        if (static::$__ORM_Joins)
        {
            return;
        }

        list(static::$__ORM_Joins, static::$__ORM_SelectColumns, $this->__ORM_JoinedColumns) = ORMMetaDataProvider::getJoinsData($this);

    }

    /**
     * Push database result array into object properties
     *
     * Requires a valid "@column" tags in PHPDoc in class properties.
     *
     * eg. public $groupId; // PHPDoc: @column group_id
     *
     * @cache orm.mapping.`get_called_class()`
     * @param array $result Database result as array
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function remapDatabaseResult($result)
    {
        // support caching, as the Reflection class is not enough fast
        if (!$this->__ORM_MetaMapping)
        {
            if ($this->app->cache)
            {
                $this->__ORM_MetaMapping = $this->app->cache->get('orm.mapping.' . get_called_class());
            }

            if (!$this->__ORM_MetaMapping)
            {
                $this->__rebuildColumnsMapping();

                if ($this->app->cache)
                {
                    $this->app->cache->set('orm.mapping.' . get_called_class(), $this->__ORM_MetaMapping, 600);
                }
            }
        }

        foreach ($result as $column => $value)
        {
            if (isset($this->__ORM_MetaMapping[$column]))
            {
                $this->{$this->__ORM_MetaMapping[$column]} = $value;
            }
        }
    }

    /**
     * Fetch objects from database
     *
     * @param null|array $where
     * @param null|array $order
     * @param null|array $group
     * @param null|Pagination $limit
     * @param array|array $values
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return $this[]
     */
    public static function fetch($where = null, $order = null, $group = null, $limit = null, $values = [])
    {
        $what = static::$__ORM_SelectColumns;
        $what[] = '*';

        $database = Framework::getInstance()->database;
        $select = $database->select(static::$__ORM_Table, $what, $where, $order, $group, $limit, $values , static::$__ORM_Joins, $execute=false);
        $query = $database->query($select[0], $select[1]);

        $objects = array();

        if (is_array($query) && count($query))
        {
            $currentClass = get_called_class();

            foreach ($query as $row)
            {
                $objects[] = new $currentClass($row);
            }
        }

        return $objects;
    }

    /**
     * Fetch only one record
     *
     * @param null|array $where
     * @param null|array $order
     * @param null|array $group
     * @param array|array $values
     *
     * @return $this|null
     */
    public static function fetchOne($where = null, $order = null, $group = null, $values = [])
    {
        $results = static::fetch($where, $order, $group, new Pagination(1, 1), $values);
        return isset($results[0]) ? $results[0] : null;
    }

    /**
     * Delete a object
     *
     * @slot framework.orm.object-{#CLASS#}.delete
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function delete()
    {
        $this->app->signals->execute('framework.orm.object-' .get_called_class(). '.delete', $this);

        // add limit if driver supports it
        $pagination = ($this->app->database->deleteUpdateLimitsAvailable() ? new Pagination(1, 1) : null);

        // where conditions
        $conditions = [
            '|=|' .static::$__ORM_IdColumn => $this->getId(),
        ];

        // @todo: Add dependencies support, but not at this development earlier stage
        $this->app->database->delete(static::$__ORM_Table, $conditions, null, null, $pagination, true);
        return true;
    }

    public function getDependencies()
    {
        throw new \Exception('@todo');
    }

    /**
     * Save a object
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function save()
    {
        $values = [];

        foreach ($this->__ORM_MetaMapping as $column => $propertyName)
        {
            if (strpos(ClassUtils::getSingleTag(get_called_class() . '::' . $propertyName, 'orm'), 'virtualColumn') !== false)
            {
                continue;
            }

            $values[$column] = $this->{$propertyName};
            $this->validateProperty($propertyName);
        }

        /**
         * If id is null, then we are creating a new object (or duplicating an existing one)
         */
        if ($this->getId() === null)
        {
            $this->app->database->insert(static::$__ORM_Table, $values);
        }
        else
        {
            $conditions = [
                '|=|' .static::$__ORM_IdColumn => $this->getId(),
            ];

            $this->app->database->update(static::$__ORM_Table, $values, $conditions);
        }
    }

    /**
     * Validate column basing on PHPDoc comment
     * PLEASE NOTE: You can create a custom validation for your field by creating a method named "validate{PROPERTY_NAME}Column" eg. "validateuserIdColumn"
     *
     * @param string $propertyName
     *
     * @throws ValidationException
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    public function validateProperty($propertyName)
    {
        $value = $this->{$propertyName};
        $isRequired = ClassUtils::getTag(get_called_class(). '::' .$propertyName, 'required');
        $varType = ClassUtils::getTag(get_called_class(). '::' .$propertyName, 'var');

        /**
         * @required
         */
        if ($isRequired && !$value)
        {
            throw new ValidationException('Column ' .get_called_class(). '::' .$propertyName. ' is required, but not filled up', 'FW_ORM_VALIDATION_FAILED', get_called_class(), $propertyName);
        }

        /**
         * @var
         */
        if ($varType && $value !== null)
        {
            $allowedTypes = explode('|', $varType[0]);
            $currentType = gettype($this->{$propertyName});
            $found = false;

            if ($currentType == 'string' && is_numeric($this->{$propertyName}))
            {
                $currentType = 'integer';
            }
            elseif (class_exists($this->{$propertyName}))
            {
                $currentType = $this->{$propertyName};
            }

            // single type check
            if ($allowedTypes && $allowedTypes[0] == $currentType)
            {
                $found = true;
            }

            if (!$found)
            {
                foreach ($allowedTypes as $typeName)
                {
                    if (isset($this->phpTypes[$typeName]) && in_array($currentType, $this->phpTypes[$typeName]))
                    {
                        $found = true;
                        break;
                    }
                }
            }

            $this->app->logging->output('Type: ' .$currentType);

            if (!$found)
            {
                throw new ValidationException('Column ' . get_called_class() . '::' . $propertyName . ' has values of unexpected type "' .$currentType. '"". Expected: ' .$varType[0], 'FW_ORM_UNEXPECTED_TYPE', get_called_class(), $propertyName);
            }
        }


        /**
         * Custom validation method
         */
        if (method_exists($this, 'validate' .ucfirst($propertyName). 'Column'))
        {
            if (!$this->{'validate' .ucfirst($propertyName). 'Column'}())
            {
                throw new ValidationException('Custom validation returned a failure for column ' .get_called_class(). '::' .$propertyName, 'FW_ORM_CUSTOM_VALIDATION_FAILED', get_called_class(), $propertyName);
            }
        }

        return true;
    }

    /**
     * Return an object id (if any)
     *
     * @param bool $getColumnName
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return mixed
     */
    public function getId($getColumnName = false)
    {
        if ($getColumnName)
        {
            return static::$__ORM_IdColumn;
        }

        if (!isset($this->__ORM_MetaMapping[static::$__ORM_IdColumn]))
        {
            return null;
        }

        return $this->{$this->__ORM_MetaMapping[static::$__ORM_IdColumn]};
    }
}