<?php
/**
 * Panthera Framework 2 exception test cases
 *
 * @package Panthera\exceptions\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class ExceptionsTest extends PantheraFrameworkTestCase
{
    /**
     * Check PantheraFrameworkException from BaseExceptions module
     *
     * @expectedException \Panthera\PantheraFrameworkException
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testSetGetValue()
    {
        $this->setup();
        throw new \Panthera\PantheraFrameworkException('Test Panthera Framework exception', 'test');
    }

    /**
     * Check ValidationException from BaseExceptions module
     *
     * @expectedException \Panthera\ValidationException
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testValidationException()
    {
        $this->setup();
        throw new \Panthera\ValidationException('Simple message', 'yay, this is code');
    }
}