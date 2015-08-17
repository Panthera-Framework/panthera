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
     * @return void
     */
    public function testGetTranslation()
    {
        $this->setup();
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
        $this->setExpectedException('\Panthera\FileNotFoundException');
        $this->app->locale->activeLanguage = 'unknown';
        $this->assertEquals('qwertyTestUnknown', $this->app->locale->get('qwertyTestUnknown', 'dashboard'));
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
     * @throws \Panthera\FileNotFoundException
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testEmptyDomain()
    {
        $this->setup();
        $this->setExpectedException('\Panthera\FileNotFoundException');
        $this->app->locale->activeLanguage ='unknown';
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
        $translations = $this->app->locale->compileCSV("key,value");
        $this->assertEquals('value', $translations['key']);
    }

    /**
     * Test compiling empty CSV file
     *
     * @throws \Panthera\SyntaxException
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testCompileCSVEmpty()
    {
        $this->setup();
        $this->assertEquals(0, count($this->app->locale->compileCSV('')));
    }
}