<?php
namespace AdminGen\Controllers;

use PhalconX\Mvc\Controller;

abstract class ControllerBase extends Controller
{
    public function initialize() 
    {
        $this->tag->setTitle("CRUD generator");
    }
}
