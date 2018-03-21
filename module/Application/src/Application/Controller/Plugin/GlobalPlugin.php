<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class GlobalPlugin extends AbstractPlugin
{
    
    protected $app;
    protected $sm;
    protected $hm;
    protected $viewModel;
    protected $controller;
    protected $profileTable;
    protected $auth;
    protected $globalData;
    protected $userId;
    
    
    public function startEnvironment($e, $fromcontroller){
        
        // Get application
        $this->app = $e->getApplication();
        
        // Get service manager
        $this->sm  = $this->app->getServiceManager();
        
        // Get view model
        $this->viewModel = $this->app->getMvcEvent()->getViewModel();
        
        // Set controller
        $this->controller = $fromcontroller;
        
        // Set models
        $this->profileTable = $this->sm->get('Application\Model\ProfileTable');
        
        /**
         * Set variables
         */
        
        // Set controller/action name in view
        $this->setViewControllerAction();
        
        // Get Auth Service
        $this->auth = $this->sm->get('zfcuser_auth_service');
        
        // Set user data and global user ID
        $this->setUserData();
        
        // Set global data in View
        $this->viewModel->globalData = $this->globalData;
        
        return $this->globalData;
        
    }
    
    private function setUserData(){
        
        $user = array();
        $this->userId = 0;
        
        if ($this->auth->hasIdentity()) {
            $user['userId'] = $this->auth->getIdentity()->getId();
            $this->userId = $user['userId'];
            $user['mail'] = $this->auth->getIdentity()->getEmail();
            $user['name'] = $this->auth->getIdentity()->getUsername();
        
            $userdata = $this->profileTable->getUser( $user['userId'] );
        
            $this->globalData['user']['userAuth'] = $user;
            $this->globalData['user']['userData'] = $userdata[0];
        }
    }
   
    
    private function setViewControllerAction(){
        
        $controllerClass = get_class($this->controller);
        $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
        $tmp = substr($controllerClass, strrpos($controllerClass, '\\')+1 );
        $controllerName = str_replace('Controller', "", $tmp);
        
        //set 'variable' into layout...
        $this->viewModel->currentModuleName      = strtolower($moduleNamespace);
        $this->viewModel->currentControllerName  = strtolower($controllerName);
        $this->viewModel->currentActionName      = $this->controller->params('action');
        
    }
    
    
    
   
    
}

