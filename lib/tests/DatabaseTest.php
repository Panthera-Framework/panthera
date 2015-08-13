<?php
/**
 * Panthera Framework 2 database test cases
 *
 * @package Panthera\database\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class DatabaseTest extends PantheraFrameworkTestCase
{
    /**
     * Check building select query with PDO support
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function testBuildingSelectQuery()
    {
        $this->setup();
        $query = $this->app->database->select('dbName', array('userName'), array(), array('userName ASC', 'userID DESC'), array('userName', 'count(userId)'));
        $this->assertContains('SELECT s1.userName FROM `dbName` as s1  ORDER BY s1.userName ASC, s1.userID DESC GROUP BY s1.userName, count(s1.userId)', $query[0]);
    }
}