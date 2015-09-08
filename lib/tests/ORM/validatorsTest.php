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

    public function testValidatingDataTypeValid()
    {
        $testObject = new testORMModel;
        $testObject->testId = '1';
        $this->assertTrue($testObject->validateProperty('testId'));

        $testObject->testId = 12357345;
        $this->assertTrue($testObject->validateProperty('testId'));
    }
}