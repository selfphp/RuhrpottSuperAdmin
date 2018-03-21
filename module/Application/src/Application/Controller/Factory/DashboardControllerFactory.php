<?php
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Application\Controller\DashboardController;


class DashboardControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        
        $sm = $container->getServiceLocator();
        $modelTable = $sm->get('Application\Model\DashboardTable');

        // Instantiate the controller and inject dependencies
        return new DashboardController( $modelTable );
        
    }
}