<?php
namespace Panthera;

/**
 * This class is handling all messages, saving them to file, displaying
 * its used to debug whole application based on Panthera Framework
 *
 * @Package Panthera
 * @author Damian Kęska
 */
class logging extends baseClass
{
    public $buffer = array();
    public $format = '';
    public $dateFormat = '';

    public function __construct()
    {
        parent::__construct();
        $this->format = $this->app->config->get('logging.format', '[%date][%path:%line] %message');
        $this->dateFormat = $this->app->config->get('logging.format.date', 'Y-m-d H:i');
    }

    public function output($message)
    {
        $backtrace = debug_backtrace();
        $backtrace = end($backtrace); // wtf

        $formatting = array(
            '%date'     => date($this->dateFormat),
            '%fullPath' => $backtrace['file'],
            '%path'     => str_replace($this->app->appPath, '', $backtrace['file']),
            '%line'     => $backtrace['line'],
            '%basename' => basename($backtrace['file']),
            '%function' => $backtrace['function'],
            '%class'    => isset($backtrace['class']) ? $backtrace['class'] : '',
            '%message'  => $message
        );

        foreach ($formatting as $key => $value)
        {
            $this->format = str_replace($key, $value, $this->format);
        }

        $this->buffer[] = $this->format;

        return $this->format;
    }
}