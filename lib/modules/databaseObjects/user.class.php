<?php
class user extends ORMBaseObject
{
    protected $ormTable = 'user';

    public $userId;
    public $userName;
    public $userPassword;
}