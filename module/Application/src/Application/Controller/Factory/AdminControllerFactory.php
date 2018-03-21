<?php
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Application\Controller\AdminController;


class AdminControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        
        $sm = $container->getServiceLocator();
        $modelTable = $sm->get('Application\Model\AdminTable');

        // Instantiate the controller and inject dependencies
        return new AdminController( $modelTable );
        
    }
}