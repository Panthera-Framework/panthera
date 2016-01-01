<?php
namespace Panthera\Components\Database;

/**
 * Pagination
 *
 * Calculating SQL limit and offset, page items count.
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\Components\Database
 */
class Pagination
{
    public $perPage = null;
    public $page    = null;

    /**
     * Constructor
     *
     * @param int $perPage Items per page
     * @param int $page Page number (1...infinity)
     *
     * @throws PantheraFrameworkException
     * @author Damian Kęska <damian@pantheraframework.org>
     */

    public function __construct($perPage, $page = 1)
    {
        if (!is_numeric($perPage) || !is_numeric($page))
        {
            throw new PantheraFrameworkException('$perPage and $page should be of integer type', 'FW_SQL_PAGINATION_NOT_INT');
        }

        $this->perPage = intval($perPage);
        $this->page = intval($page);
    }

    /**
     * Get SQL offset and limit
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public function getSQLData()
    {
        return [($this->perPage * ($this->page - 1)), $this->perPage];
    }

    /**
     * Get offset from to eg. [5, 10] or [10, 15] if perPage = 5
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public function getFromTo()
    {
        return [($this->perPage * ($this->page - 1)), ($this->perPage * $this->page)];
    }
}