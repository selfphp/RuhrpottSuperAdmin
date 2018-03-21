<?php
namespace Cron;
use Zend\ServiceManager\Factory\InvokableFactory;

return array(
    'controllers' => array(
        'factories' => array(
            'Cron\Controller\Cron' => Controller\Factory\CronControllerFactory::class,
        ),
    ),
    
);

