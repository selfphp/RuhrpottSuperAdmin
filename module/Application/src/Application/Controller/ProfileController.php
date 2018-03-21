<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Application\ConfigAwareInterface;

class ProfileController extends AbstractActionController implements ConfigAwareInterface
{
    
    protected $modelTable;
    protected $configZfc;
    
    public function setConfig($config)
    {
        $this->configZfc = $config;
    }
    
    public function __construct($dbone)
    {
        $this->modelTable = $dbone;
    
    
    
    
         
        //$this->session = new Container('formalerts');
    }
    
    public function onDispatch(MvcEvent $e)
    {
        
        $plugin = $this->GlobalPlugin();
        $this->globalData = $plugin->startEnvironment($e, $this);
       
        
        return parent::onDispatch($e);
    
    }
    
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function setnavbarAction()
    {
        
        $response = $this->modelTable->setNavbar( $this->globalData['user']['userData']['user_id'] );
        
        $viewModel = new ViewModel(array());
        $viewModel->setTerminal(true);
        return $viewModel;
        
    }
}
