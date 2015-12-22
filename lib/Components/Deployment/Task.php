<?php
namespace Panthera\Components\Deployment;
use Panthera\Binaries\DeploymentApplication;
use Panthera\Components\Kernel\BaseFrameworkClass;

/**
 * Deployment task skeleton
 *
 * @package Panthera\Components\Deployment
 */
class Task extends BaseFrameworkClass
{
    /**
     * @var array $messageTypes
     */
    protected $messageTypes = [
        'arrow' => "\e[38;5;85m==> \e[38;5;230m",
    ];

    /**
     * @var array
     */
    public $dependencies = [];

    /**
     * @var DeploymentApplication
     */
    public $deployApp = null;

    /**
     * List of shell arguments that deployment application could take, but will be passed to proper tasks
     *
     * @var array
     */
    public $shellArguments = [];

    /**
     * Skip arguments strict checking, so unknown arguments could be passed
     *
     * @var bool
     */
    public $allowUnknownArguments = false;

    /**
     * Constructor
     *
     * @param DeploymentApplication $deployApp
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct(DeploymentApplication $deployApp)
    {
        parent::__construct();
        $this->deployApp = $deployApp;
    }

    /**
     * Output a message
     *
     * @param string $message
     * @param string $type
     * @param bool $newLine
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    protected function output($message, $type = 'arrow', $newLine = true)
    {
        $type = isset($this->messageTypes[$type]) ? $this->messageTypes[$type] : '';
        print($type . $message. "\e[0m");

        if ($newLine)
        {
            print("\n");
        }
    }

    /**
     * @return string
     */
    protected function getInput($message, $type = '')
    {
        if ($message)
        {
            $this->output($message . ": ", $type, false);
        }

        return trim(fgets(STDIN));
    }
}