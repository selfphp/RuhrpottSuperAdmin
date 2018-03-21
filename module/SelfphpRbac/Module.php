<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace SelfphpRbac;

use Zend\EventManager\EventInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * SelfphpRbac Module
 *
 * @author SELFPHP OHG <damir.enseleit@selfphp.de>
 */
class Module 
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        /* @var $app \Zend\Mvc\ApplicationInterface */
        $app            = $event->getTarget();
        /* @var $sm \Zend\ServiceManager\ServiceLocatorInterface */
        $serviceManager = $app->getServiceManager();
        //$config         = $serviceManager->get('BjyAuthorize\Config');
        //$strategy       = $serviceManager->get($config['unauthorized_strategy']);
        //$guards         = $serviceManager->get('BjyAuthorize\Guards');
/*
        foreach ($guards as $guard) {
            $app->getEventManager()->attach($guard);
        }

        $app->getEventManager()->attach($strategy);
        */
        echo 'Hallo SelfphpRbac';
    }

   

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
