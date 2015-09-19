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
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return void
     */
    public function testBuildingSelectQuery()
    {
        $query = $this->app->database->select('dbName', [
            'userName'
        ], [], ['userName ASC', 'userID DESC'], ['userName', 'count(userId)'], null, null, null, false);

        $this->assertRegExp('/SELECT(.*)userName FROM/i', $query[0]);
        $this->assertRegExp('/ORDER BY([ A-Za-z0-9.]+?).userName ASC/i', $query[0]);
        $this->assertRegExp('/([ A-Za-z0-9.]+?)userID DESC/i', $query[0]);
        $this->assertRegExp('/GROUP BY([ A-Za-z0-9.]+?)userName/i', $query[0]);
        $this->assertRegExp('/count\(([ A-Za-z0-9.]+?)userId\)/i', $query[0]);
    }

    /**
     * Test building select query with OOP
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testBuildingSelectQuery2()
    {
        $select = new \Panthera\database\select('testTable');
        $select->what = array(
            'testKey1',
            'testKey2',
        );

        $response = $select->execute(false);
        $this->assertRegExp('/SELECT([ A-Za-z0-9.]+?)testKey1/i', $response[0], true);
        $this->assertContains("testKey1,", $response[0], true);
        $this->assertContains("testKey2 FROM `testTable` as ", $response[0], true);
    }

    /**
     * Testing update functions
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testUpdate()
    {
        $this->setUp();

        // create a test table
        $this->app->database->query('CREATE TABLE `testUpdate` ( number INTEGER PRIMARY KEY ); ');

        // insert a value
        $this->app->database->insert('testUpdate', ['number' => 5]);

        // update it
        $this->app->database->update('testUpdate', ['number' => 10], ['|=|number' => 5]);

        // check it's value
        $fetch = $this->app->database->select('testUpdate', ['number']);

        $this->assertSame('10', $fetch[0]['number']);
    }

    /**
     * Test creating where condition query
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testWhereCondition()
    {
        $whereCondition = $this->app->database->parseWhereConditionBlock(array('|=|test' => 'testValue'));
        $this->assertContains('test = :test', $whereCondition['sql']);
    }

    /**
     * Test pagination with select query
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testPagination()
    {
        $select = new \Panthera\database\select('testTable');
        $select->what = array('testKey');
        $select->limit = new \Panthera\database\Pagination(5, 3);

        $response = $select->execute(false);

        $this->assertContains("LIMIT 5 OFFSET 10", $response[0], '', true);
    }

    /**
     * INSERT INTO syntax check, if contains passed columns, table name
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testInsertSyntax()
    {
        $insert = $this->app->database->insert('people', [
            'name'  => 'Anarchist',
            'age'   => 34,
            'chair' => 'long black-red'
        ], true);

        $this->assertContains('insert into', $insert['query'], '', true);
        $this->assertContains('`people`', $insert['query'], '', true);
        $this->assertContains('name', $insert['query'], '', true);
        $this->assertContains('age', $insert['query'], '', true);
        $this->assertContains('chair', $insert['query'], '', true);
        $this->assertSame(3, count($insert['data']));
        $this->assertArrayHasKey('name', $insert['data']);
    }
}