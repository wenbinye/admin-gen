<?php
namespace AdminGen\Mvc;

use Phalcon\Text;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use Phalcon\Mvc\View\Exception as ViewException;
use PhalconX\Exception\ValidationException;
use PhalconX\Exception;

class ExceptionHandler extends Injectable
{
    private $lastException;

    public function beforeDispatchLoop($event, $dispatcher)
    {
        $action = Text::camelize($dispatcher->getActionName());
        $dispatcher->setActionName(lcfirst($action));
    }

    public function beforeExecuteRoute($event, $dispatcher)
    {
        if (!$this->router->wasMatched() && !$dispatcher->wasForwarded()) {
            $dispatcher->forward([
                'controller' => 'index',
                'action' => 'show404'
            ]);
            return false;
        }
    }
    
    public function beforeException($event, $dispatcher, $exception)
    {
        if ($this->isSameException($exception)) {
            // 防止死循环
            $this->log($exception, 'error');
            echo "Internal error: recusive error encounted\n";
            exit;
        }
        $this->lastException = $exception;
        if ($this->response->getStatusCode() == 404
            || $exception instanceof DispatchException) {
            $this->logger->info("NOT FOUND uri=" . $this->request->getUri());
        }
        $contentType = $this->response->getHeaders()->get('Content-Type');
        if (strpos($contentType, 'json') !== false) {
            return $this->handleAjaxException($exception);
        }
        if ($exception instanceof Exception) {
            if ($exception instanceof ValidationException) {
                $this->response->setStatusCode(400);
                $this->log($exception, 'info');
                $dispatcher->forward(array(
                    'controller' => 'index',
                    'action' => 'show500'
                ));
            } elseif ($exception->getCode() == Exception::ERROR_LOGIN_REQUIRED) {
                $this->response->setStatusCode(302);
                $this->response->redirect("/login")->send();
                exit;
            } else {
                if ($exception->getCode() == Exception::ERROR_NOT_FOUND) {
                    $statusCode = 404;
                } else {
                    $statusCode = $this->response->getStatusCode();
                }
                if (!in_array($statusCode, ['404', '403', '401', '500'])) {
                    $statusCode = '500';
                }
                if ($statusCode == 500) {
                    $this->log($exception, 'error');
                } else {
                    $this->log($exception, 'info');
                }
                $dispatcher->forward(array(
                    'controller' => 'index',
                    'action' => 'show' . $statusCode
                ));
            }
        } elseif ($exception instanceof DispatchException) {
            $dispatcher->forward(array(
                'controller' => 'index',
                'action' => 'show404'
            ));
        } else {
            $this->log($exception, 'error');
            $dispatcher->forward(array(
                'controller' => 'index',
                'action' => 'show500'
            ));
        }
        ob_end_clean();
        $this->response->setContent('');
        $dispatcher->setNamespaceName($this->router->getDefaults()['namespace']);
        $dispatcher->setParams(['exception' => $exception]);
        $dispatcher->dispatch();
        return false;
    }

    private function isSameException($exception)
    {
        if (!$this->lastException) {
            return false;
        }
        if (get_class($exception) == get_class($this->lastException)
            && $exception->getMessage() == $this->lastException->getMessage()
            && $exception->getCode() == $this->lastException->getCode()
            && $exception->getFile() == $this->lastException->getFile()
            && $exception->getLine() == $this->lastException->getLine()) {
            return true;
        }
    }
    
    private function handleAjaxException($exception)
    {
        if ($exception instanceof DispatchException) {
            $this->response->setStatusCode(404);
            $this->response->setJsonContent([
                'error' => 'request ' . $this->request->getUri() . ' not found',
                'error_code' => Exception::ERROR_NOT_FOUND,
            ])->send();
            exit;
        }
        if ($exception instanceof ValidationException) {
            $this->response->setStatusCode(400);
            $this->log($exception, 'info');
        } elseif ($exception instanceof Exception
                  && $exception->getCode() == Exception::ERROR_NOT_FOUND) {
            $this->response->setStatusCode(404);
            $this->log($exception, 'info');
        } else {
            $this->response->setStatusCode(500);
            $this->log($exception, 'error');
        }
        $this->response->setJsonContent([
            'error_code' => $exception->getCode(),
            'error' => $exception->getMessage()
        ])->send();
        exit;
    }

    private function log($exception, $level)
    {
        call_user_func(
            array($this->logger, $level),
            "FAILED uri=" . $this->request->getUri()
            . " message=" . $exception->getMessage() . "\n"
            . $exception->getTraceAsString()
        );
    }
}
