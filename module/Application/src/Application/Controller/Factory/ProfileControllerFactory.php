<?php
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Application\Controller\ProfileController;


class ProfileControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        
        $sm = $container->getServiceLocator();
        $modelTable = $sm->get('Application\Model\ProfileTable');

        // Instantiate the controller and inject dependencies
        return new ProfileController( $modelTable );
        
    }
}