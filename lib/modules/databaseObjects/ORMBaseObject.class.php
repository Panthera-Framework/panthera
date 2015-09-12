<?php
namespace Panthera\database;
use Panthera\framework;
use Panthera\utils\classUtils;
use Panthera\ValidationException;

/**
 * Basic abstract ORM model
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\database
 */
abstract class ORMBaseObject extends \Panthera\baseClass
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $__orm_Table = '';

    /**
     * What to select
     *
     * @var string|array
     */
    protected static $__orm_What = '*';

    /**
     * Order by statement
     *
     * @var string
     */
    protected static $__orm_Order = '';

    /**
     * Group by statement
     *
     * @var array
     */
    protected static $__orm_Group = '';

    /**
     * Joined tables
     *
     * @var null|array
     */
    protected static $__orm_Joins = null;

    /**
     * Id column - table specific
     *
     * @var string
     */
    protected static $__orm_IdColumn = 'id';

    /**
     * Internal cache for columns mapping
     *
     * @var array
     */
    private $__orm__meta__mapping = [];

    /**
     * PHPDoc types mapping to what @see gettype() function returns
     *
     * @var array
     */
    protected $phpTypes = [
        'int'       => ['integer', 'numeric'],
        'float'     => ['numeric', 'float'],
        'bool'      => ['integer', 'string'],
        'array'     => ['array'],
        'resource'  => ['object', 'resource'],
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
     * @throws \Panthera\PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return bool
     */
    protected function selectObjectById($id)
    {
        $where = array(
            '|=|' .static::$__orm_IdColumn. '' => $id,
        );

        $result = $this->app->database->select(static::$__orm_Table, '*', $where, static::$__orm_IdColumn, null, new Pagination(1, 1), array(), static::$__orm_Joins);

        if (is_array($result))
        {
            if (empty($result))
            {
                throw new \Panthera\PantheraFrameworkException('Result is empty, validate given $id', 'FW_SQL_NO_RESULT');
            }

            $this->remapDatabaseResult($result[0]);
            return true;
        }

        return false;
    }

    /**
     * Get columns mapping, store in cache
     *
     * @private
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    private function __rebuildColumnsMapping()
    {
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties() as $property)
        {
            $phpDoc = $property->getDocComment();
            $columnMetaPos = strpos($phpDoc, '@column ');

            if ($columnMetaPos !== false)
            {
                $column = substr($phpDoc, ($columnMetaPos + 8), (strpos($phpDoc, "\n", $columnMetaPos) - $columnMetaPos) - 8);

                if ($column)
                {
                    $this->__orm__meta__mapping[$column] = $property->getName();
                }
            }
        }
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
        if (!$this->__orm__meta__mapping)
        {
            if ($this->app->cache)
            {
                $this->__orm__meta__mapping = $this->app->cache->get('orm.mapping.' .get_called_class());
            }

            if (!$this->__orm__meta__mapping)
            {
                $this->__rebuildColumnsMapping();

                if ($this->app->cache)
                {
                    $this->app->cache->set('orm.mapping.' .get_called_class(), $this->__orm__meta__mapping, 600);
                }
            }
        }

        foreach ($result as $column => $value)
        {
            if (isset($this->__orm__meta__mapping[$column]))
            {
                $this->{$this->__orm__meta__mapping[$column]} = $value;
            }
        }
    }

    /**
     * Fetch objects from database
     *
     * @param null $where
     * @param null $order
     * @param null $group
     * @param null $limit
     * @param array $values
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return static[]
     */
    public static function fetch($where = null, $order = null, $group = null, $limit = null, $values = array())
    {
        $database = framework::getInstance()->database;
        $select = $database->select(static::$__orm_Table, '*', $where, $order, $group, $limit, $values , static::$__orm_Joins, $execute=false);
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
            '|=|' .static::$__orm_IdColumn => $this->getId(),
        ];

        // @todo: Add dependencies support, but not at this development earlier stage
        $this->app->database->delete(static::$__orm_Table, $conditions, null, null, $pagination, true);
        return true;
    }

    /**
     * Save a object
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function save()
    {
        $values = [];

        foreach ($this->__orm__meta__mapping as $column => $propertyName)
        {
            $values[$column] = $this->{$propertyName};
            $this->validateProperty($propertyName);
        }

        /**
         * If id is null, then we are creating a new object (or duplicating an existing one)
         */
        if ($this->getId() === null)
        {
            $this->app->database->insert(static::$__orm_Table, $values);
        }
        else
        {
            $conditions = [
                '|=|' .static::$__orm_IdColumn => $this->getId(),
            ];

            $this->app->database->update(static::$__orm_Table, $values, $conditions);
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
        $isRequired = classUtils::getTag(get_called_class(). '::' .$propertyName, 'required');
        $varType = classUtils::getTag(get_called_class(). '::' .$propertyName, 'var');

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
        if ($varType)
        {
            $allowedTypes = explode('|', $varType[0]);
            $currentType = gettype($this->{$propertyName});
            $found = false;

            if ($currentType == 'string' && is_numeric($this->{$propertyName}))
            {
                $currentType = 'numeric';
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
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return mixed
     */
    public function getId()
    {
        return $this->{$this->__orm__meta__mapping[static::$__orm_IdColumn]};
    }
}