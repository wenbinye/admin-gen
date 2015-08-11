<?php
namespace AdminGen;

use Phalcon\DiInterface;
use Phalcon\Security as BaseSecurity;

class Security extends BaseSecurity
{
    private $logger;
    
    public function setDi(DiInterface $di)
    {
        $this->logger = $di['logger'];
        return parent::setDi($di);
    }
    
    public function getToken($num = null)
    {
        $this->logger->info("{$_SERVER['REQUEST_URI']} getToken");
        return parent::getToken($num);
    }

    public function getTokenKey($num = null)
    {
        $this->logger->info("{$_SERVER['REQUEST_URI']} getTokenKey");
        return parent::getTokenKey($num);
    }

    public function checkToken($tokenKey = null, $tokenValue = null, $destroy = null)
    {
        $this->logger->info("{$_SERVER['REQUEST_URI']} checkToken destroy={$destroy}");
        $ret = parent::checkToken($tokenKey, $tokenValue, $destroy);
        return $ret;
    }
}
