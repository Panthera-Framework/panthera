<?php
namespace Panthera\Components\Controller;

/**
 * Panthera Framework 2
 * --------------------
 * Simply returns response text instead of variables and rendering a template
 *
 * @package Panthera\Components\Controller
 */
class ResponseText extends Response
{
    /**
     * @param string $responseText
     */
    public function __construct($responseText)
    {
        $this->variables = [
            'text' => $responseText,
        ];
    }

    /**
     * @return void
     */
    public function display()
    {
        print($this->variables['text']);
    }
}