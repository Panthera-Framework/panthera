<?php
/**
 * ORM fields validation tests
 *
 * @package Panthera\ORM\tests
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class validatorsTest extends PantheraFrameworkTestCase
{
    /**
     * Test data type validation for ORM field
     * @\var
     *
     * @expectedException \Panthera\ValidationException
     */
    public function testValidatingDataType()
    {
        $testObject = new testORMModel;
        $testObject->testId = 'non-numeric-but-declared-int-here-in-this-field';
        $testObject->validateProperty('testId');
    }

    /**
     * Test validation for integers and numeric strings
     *
     * @throws \Panthera\ValidationException
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testValidatingDataTypeValid()
    {
        $testObject = new testORMModel;
        $testObject->testId = '1';
        $this->assertTrue($testObject->validateProperty('testId'));

        $testObject->testId = 12357345;
        $this->assertTrue($testObject->validateProperty('testId'));
    }

    /**
     * Test custom validation - @see testORMModel::validateTestNameColumn()
     *
     * @expectedException \Panthera\ValidationException
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testCustomValidationFunction()
    {
        $testObject = new testORMModel;
        $testObject->testName = 'fail-this-test';
        $testObject->validateProperty('testName');
    }
}