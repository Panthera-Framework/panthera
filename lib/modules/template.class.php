<?php
namespace Panthera;
include __DIR__. '/../vendor/pantheraframework/raintpl4/library/Rain/autoload.php';

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

        $this->rain = new \Rain\RainTPL4();

        $this->rain->setConfiguration(array(
            'base_url'            => null,
            'tpl_dir'             => $this->app->appPath. '/.content/templates/',
            'cache_dir'           => $this->app->appPath. '/.content/cache/RainTPL4/',
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
     * @param mixed $variable Name of template variable or associative array name/value
     * @param mixed $value value assigned to this variable. Not set if variable_name is an associative array
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function assign($variable, $value)
    {
        $this->rain->assign($variable, $value);
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

    public function compileString($code)
    {
        return $this->rain->drawString($code, true);
    }
}