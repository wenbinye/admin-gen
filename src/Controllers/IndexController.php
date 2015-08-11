<?php
namespace AdminGen\Controllers;

/**
 * @RoutePrefix("")
 */
class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->render();
    }

    public function show404Action()
    {
        $this->render();
    }
}
