<?php
namespace Cron\Controller\Factory;

use Interop\Container\ContainerInterface;
use Cron\Controller\CronController;


class CronControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        
        $sm = $container->getServiceLocator();
        
        $modelTable = $sm->get('Application\Model\CronTable');

        // Instantiate the controller and inject dependencies
        return new CronController( $modelTable );
        
    }
}