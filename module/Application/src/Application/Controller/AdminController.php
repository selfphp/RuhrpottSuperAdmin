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

class AdminController extends AbstractActionController implements ConfigAwareInterface
{
    
    protected $modelTable;
    protected $configZfc;
    protected $globalData;
    
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
       
       // print_r($this->globalData);
        
        return parent::onDispatch($e);
    
    }
    
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function projectsAction()
    {
        return new ViewModel();
    }
    
    public function createprojectAction(){
        
        $response = array();
        $response['id'] = 0;
        
        $project_name = $this->getRequest()->getPost('project_name');
        $scrum = intval($this->getRequest()->getPost('scrum'));
        $kanban = intval($this->getRequest()->getPost('kanban'));
        
        $response['name'] = $project_name;
        $response['scrum'] = $scrum;
        $response['kanban'] = $kanban;
        
        $data = $this->modelTable->createProject( $response );
        
        if( $data > 0 ){
            $response['id'] = $data;
        }
        $response['id'] = $data;
        $viewModel = new ViewModel(array('response' => $response));
        $viewModel->setTerminal(true);
        return $viewModel;
        
    }
}
