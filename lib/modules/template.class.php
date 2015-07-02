<?php
namespace Panthera;

// Load RainTPL4
// todo: improve including RainTPL4 with in-built class
include 'path-to-raintpl4';

use Rain\RainTPL4;

/**
 * Panthera Framework 2 template management class - displaying, compiling,
 *   validating, etc.
 *
 * @Package Panthera
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class template extends baseClass
{
    /**
     * RainTPL4 object
     *
     * @var null
     */
    public $rain = null;

    /**
     * Debugging mode on/off
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Remove comments while compiling on/off
     *
     * @var bool
     */
    public $removeComments = true;

    /**
     * Ignore unknown tags on/on
     *
     * @var bool
     */
    public $ignoreUnknownTags = true;

    /**
     * Init template function
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function __construct()
    {
        parent::__construct();

        $this->debug = $this->app->config->get('template.debug', false);
        $this->removeComments = $this->app->config->get('template.remove_comments', true);
        $this->ignoreUnknownTags = $this->app->config->get('template.ignore_unknown_tags', true);

        $this->rain = new RainTPL4();

        $this->rain->setConfiguration(array(
            'base_url'            => null,
            'tpl_dir'             => '.content/templates/',
            'cache_dir'           => '.content/cache/RainTPL4/',
            'remove_comments'     => $this->removeComments,
            'debug'               => $this->debug,
            'ignore_unknown_tags' => $this->ignoreUnknownTags,
        ));
    }

    /**
     * Set configuration variables
     *
     * @param array $settings
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function setConfiguration($settings)
    {
        $this->rain->setConfiguration($settings, true);
    }

    /**
     * Assign value for template
     *
     * @param $variable array with template variables
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function assign($variable)
    {
        $this->rain->assign($variable);
    }

    /**
     * Draw template with assigned variables
     *
     * @param string $templateFile path to template
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return string
     */
    public function display($templateFile)
    {
        return $this->rain->draw($templateFile);
    }
}