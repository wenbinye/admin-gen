<?php
namespace AdminGen\Mvc;

use PhalconX\Mvc\ViewHelper as BaseViewHelper;
use PhalconX\Annotations\ContextType;
use AdminGen\Annotations\Sidebar;

class ViewHelper extends BaseViewHelper
{
    public function menus()
    {
        $menus = $this->modelsMetadata->read('sidebar_menus');
        if (!isset($menus)) {
            $dir = $this->config->application->controllersDir;
            $menus = $this->annotations->scan($dir, Sidebar::CLASS, ContextType::T_CLASS);
            $this->modelsMetadata->write('sidebar_menus', $menus);
        }
        return $menus;
    }
}
