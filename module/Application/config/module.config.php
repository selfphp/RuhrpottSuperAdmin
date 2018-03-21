<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\ServiceManager\Factory\InvokableFactory;

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'login' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/login[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Login',
                        'action'     => 'index',
                    ),
                ),
            ),
            'userlogin' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/user/login',
                ),
            ),
            'userlogout' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/user/logout',
                ),
            ),
            'dashboard' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/dashboard[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Dashboard',
                        'action'     => 'index',
                    ),
                ),
            ),
            'admin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/admin[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Admin',
                        'action'     => 'index',
                    ),
                ),
            ),
            'kanban' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/kanban[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Kanban',
                        'action'     => 'index',
                    ),
                ),
            ),
            'profile' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/profile[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Profile',
                        'action'     => 'index',
                    ),
                ),
            ),
            'users' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/users[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action'     => 'index',
                    ),
                ),
            ),            
            
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => Controller\IndexController::class,
            
        ),
        'factories' => array(
            'Application\Controller\Dashboard' => Controller\Factory\DashboardControllerFactory::class,
            'Application\Controller\Kanban' => Controller\Factory\KanbanControllerFactory::class,
            'Application\Controller\Admin' => Controller\Factory\AdminControllerFactory::class,
            'Application\Controller\Profile' => Controller\Factory\ProfileControllerFactory::class,
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'GlobalPlugin' => 'Application\Controller\Plugin\GlobalPlugin',
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            'zfcuser' => __DIR__ . '/../view',
            
        ),
    ),
    'route_layouts' => array(
        //will be used for each child route under the "some" route. Except for the defined "some/route" route
        'dashboard' => 'layout/layout-manager',
        'admin' => 'layout/layout-admin',
        'kanban' => 'layout/layout-manager',
        'profile' => 'layout/layout-manager',
        'users' => 'layout/layout-manager',
        //will be used for each child route under the "some/route" route.
        'home' => 'layout/layout',
        //'zfcuser' => __DIR__ . '/../view',
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
	            'cron' => array(
	            		'options' => array(
	            				'route'    => 'cron [--param1=] [--param2=]',
	            				'defaults' => array(
	            				    	'controller' => 'Cron\Controller\Cron',
	            						'action' => 'index'
	            				)
	            		)
	            )
            ),
        ),
    ),
);
