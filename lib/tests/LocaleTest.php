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
}