<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Application\Model\DashboardTable;
use Application\Model\AdminTable;
use Application\Model\KanbanTable;
use Application\Model\ProfileTable;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
    
    public function getServiceConfig()
    {
        
        return array(
            'factories' => array(
                'Application\Model\DashboardTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new DashboardTable($dbAdapter);
                    return $table;
                },
                'Application\Model\AdminTable' =>  function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $table = new AdminTable($dbAdapter);
                return $table;
                },
                'Application\Model\KanbanTable' =>  function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $table = new KanbanTable($dbAdapter);
                return $table;
                },
                'Application\Model\ProfileTable' =>  function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $table = new ProfileTable($dbAdapter);
                return $table;
                },
           ),
        );
        
    }
    
    public function getControllerConfig()
    {
        return array(
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof ConfigAwareInterface) {
                        $locator = $sm->getServiceLocator();
                        $config  = $locator->get('Config');
                        //print_r($config);
                        //$config  = $locator->get('zfcuser');
                        //goalioforgotpassword_module_options
                        $instance->setConfig($config['zfcuser']);
                    }
                }
                )
            );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
