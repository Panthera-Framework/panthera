<?php
namespace Panthera\database;
use Panthera\framework;

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
	 * Construct object by results from database
	 *
	 * @param array|int $data Result set of a SQL query or an object id
	 * @author Damian Kęska <damian@pantheraframework.org>
	 */
	public function __construct($data)
	{
		/** @see \Panthera\baseClass::__construct **/
		parent::__construct();

		if (is_int($data))
		{
			self::selectObjectById($data);
		} else {
			$this->remapDatabaseResult($data);
		}
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
	 * @param string $what
	 * @param null $group
	 * @param null $limit
	 * @param array $values
	 *
	 * @author Damian Kęska <damian@pantheraframework.org>
	 * @return static[]
	 */
	public static function fetch($where = null, $order = null, $what = '*', $group = null, $limit = null, $values = array())
	{
		$database = framework::getInstance()->database;
		$select = $database->select(static::$__orm_Table, $what, $where, $order, $group, $limit, $values , static::$__orm_Joins);
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
	 * Return an object id (if any)
	 *
	 * @author Damian Kęska <damian@pantheraframework.org>
	 * @return mixed
	 */
	public function getId()
	{
		return $this->{static::$__orm_IdColumn};
	}
}