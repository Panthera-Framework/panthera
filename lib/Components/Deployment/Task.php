<?php
namespace Panthera\Components\Deployment;
use Panthera\Binaries\DeploymentApplication;
use Panthera\Components\Kernel\BaseFrameworkClass;
use Panthera\Components\Validator\Validator;

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

    /**
     * Ask a question until user will not answer correctly
     *
     * @param string $message
     * @param string $validatorString
     * @param int $maxRetries
     * @param string $type
     * @param string $errMessage Message to show when input was invalid (validation failed)
     *
     * @throws \Panthera\Classes\BaseExceptions\ValidationException
     *
     * @return string
     */
    protected function ask($message, $validatorString, $maxRetries = 3, $type = 'question', $errMessage = 'Invalid input, please try again')
    {
        $retries = -1;
        $validatorOutput = '';

        do
        {
            if ($retries > -1)
            {
                $this->output($errMessage);
            }

            $input = $this->getInput($message, $type);
            $retries++;

            if ($retries === $maxRetries && $maxRetries !== 0)
            {
                $this->output('Maximum retries exceeded, exiting...', 'error');
                exit;
            }

            if (!$input)
            {
                continue;
            }

            $validatorOutput = Validator::validate($input, $validatorString);

            if (is_string($errMessage))
            {
                $errMessage = $validatorOutput;
            }

        } while ($validatorOutput !== true && $validatorOutput !== 1);

        return $input;
    }
}