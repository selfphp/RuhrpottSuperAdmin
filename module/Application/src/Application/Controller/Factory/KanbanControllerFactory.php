<?php
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Application\Controller\KanbanController;


class KanbanControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        
        $sm = $container->getServiceLocator();
        $modelTable = $sm->get('Application\Model\KanbanTable');

        // Instantiate the controller and inject dependencies
        return new KanbanController( $modelTable );
        
    }
}