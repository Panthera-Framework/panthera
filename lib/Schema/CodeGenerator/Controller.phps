<?php
namespace {$namespace$};

use {$extends$};
use Panthera\Components\Controller\Response;

/**
 * {$projectName$}
 * ---------------
 * Controller {$className$}
 */
class {$className$} extends {$baseNameExtends$}
{
    public function defaultAction()
    {
        return new Response([], '{$className$}.tpl');
    }
}