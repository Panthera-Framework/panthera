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
        $query = $this->app->database->select('dbName', array('userName'), array(), array('userName ASC', 'userID DESC'), array('userName', 'count(userId)'), null, null, null, false);
        $this->assertContains('SELECT s1.userName FROM `dbName` as s1  ORDER BY s1.userName ASC, s1.userID DESC GROUP BY s1.userName, count(s1.userId)', $query[0]);
    }

    /**
     * Test building select query with OOP
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testBuildingSelectQuery2()
    {
        $this->setup();
        $select = new \Panthera\database\select('testTable');
        $select->what = array(
            'testKey1',
            'testKey2',
        );

        $response = $select->execute(false);
        $this->assertContains("testKey2 FROM `testTable` as ", $response[0]);
    }

    /**
     * Test creating where condition query
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testWhereCondition()
    {
        $this->setup();
        $whereCondition = $this->app->database->parseWhereConditionBlock(array('|=|test' => 'testValue'));
        $this->assertContains('test = :test_', $whereCondition['sql']);
    }

    /**
     * Test pagination with select query
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testPagination()
    {
        $this->setup();

        $select = new \Panthera\database\select('testTable');
        $select->what = array('testKey');
        $select->limit = new \Panthera\database\Pagination(5, 3);

        $response = $select->execute(false);

        $this->assertContains("LIMIT 5 OFFSET 10", $response[0]);
    }
}