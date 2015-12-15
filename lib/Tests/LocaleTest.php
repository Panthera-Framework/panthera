<?php
/**
 * Panthera Framework 2 locale test cases
 *
 * @package Panthera\locale\tests
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class LocaleTest extends PantheraFrameworkTestCase
{
    /**
     * Check getting translations
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return null
     */
    public function testGetTranslation()
    {
        $this->setup();
        $this->markTestIncomplete();
        return null;

        $this->app->locale->activeLanguage = 'pl';
        $this->assertEquals('Widok dewelopera', $this->app->locale->get('Developer view', 'dashboard'));
    }

    /**
     * Test getting unknown translation
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function testGetUnknownTranslation()
    {
        $this->setup();
        $this->setExpectedException('\Panthera\Classes\BaseExceptions\FileNotFoundException');
        $this->app->locale->activeLanguage = 'unknown';
        $this->assertEquals('qwertyTestUnknown', $this->app->locale->get('qwertyTestUnknown', 'unknownDomain'));
    }

    /**
     * Test getting original translation
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testGetOriginalTranslation()
    {
        $this->setup();
        $this->app->locale->activeLanguage = 'original';
        $this->assertEquals('testUnknown', $this->app->locale->get('testUnknown', 'unknown'));
    }

    /**
     * Test empty domain error
     *
     * @throws \Panthera\Classes\BaseExceptions\FileNotFoundException
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testEmptyDomain()
    {
        $this->setup();
        $this->setExpectedException('\Panthera\Classes\BaseExceptions\FileNotFoundException');
        $this->app->locale->activeLanguage = 'unknown';
        $this->app->locale->get('testValue', '');
    }

    /**
     * Test compiling CSV file
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testCompileCSV()
    {
        $this->setup();
        $translations = $this->app->locale->compileCSV('"key", "value"\n"key2", "value2"');
        $this->assertEquals('value', $translations['key']);
        $this->assertEquals('value2', $translations['key2']);

        // escaping
        $translations = $this->app->locale->compileCSV('"key", "Escaped \"text\""');
        $this->assertEquals('Escaped \"text\"', $translations['key']);
    }

    /**
     * Test compiling empty CSV file
     *
     * @throws \Panthera\Classes\BaseExceptions\SyntaxException
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testCompileCSVEmpty()
    {
        $this->setup();
        $this->assertEquals(0, count($this->app->locale->compileCSV('')));
    }
}