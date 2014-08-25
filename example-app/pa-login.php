<?php
require_once 'content/app.php';
require_once PANTHERA_DIR. '/frontpages/pa-login.php';

class pa_loginController extends pa_loginControllerSystem
{
    public function __construct()
    {
        parent::__construct();
        $this -> template -> setTemplate('zlecenie2014');
    }
}

$controller = new pa_loginController;
$controller -> display();
