<?php
namespace Panthera\Components\Controller;

/**
 * Interface ResponseEncoderInterface
 *
 * @package Panthera\Components\Controller
 */
interface ResponseEncoderInterface
{
    public function encode(array $filteredVariables, array $variables, Response $response);
}