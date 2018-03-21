<?php

return array(
    'bjyauthorize' => array(

        // set the 'guest' role as default (must be defined in a role provider)
        'default_role' => 'guest',

        /* this module uses a meta-role that inherits from any roles that should
         * be applied to the active user. the identity provider tells us which
         * roles the "identity role" should inherit from.
         *
         * for ZfcUser, this will be your default identity provider
         */
        'identity_provider' => 'BjyAuthorize\Provider\Identity\ZfcUserZendDb',
    		
    	
        /* role providers simply provide a list of roles that should be inserted
         * into the Zend\Acl instance. the module comes with two providers, one
         * to specify roles in a config file and one to load roles using a
         * Zend\Db adapter.
         */
        'role_providers' => array(

            // this will load roles from the user_role table in a database
            // format: user_role(role_id(varchar), parent(varchar))
            'BjyAuthorize\Provider\Role\ZendDb' => array(
                'table'             => 'user_role',
                'identifier_field_name' => 'id',
                'role_id_field'         => 'roleId',
                'parent_role_field'     => 'parent_id',
            ),
        ),

        // resource providers provide a list of resources that will be tracked
        // in the ACL. like roles, they can be hierarchical
        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'pants' => array(),
            ),
        ),
        'guards' => array(
            'BjyAuthorize\Guard\Route' => array(
                array('route' => 'zfcuser', 'roles' => array('mod')),
                array('route' => 'dashboard', 'roles' => array('guest')),
                array('route' => 'admin', 'roles' => array('admin')),
                array('route' => 'profile', 'roles' => array('mod')),
                array('route' => 'kanban', 'roles' => array('guest')),
                array('route' => 'zfcuser/logout', 'roles' => array('guest')),
                array('route' => 'zfcuser/login', 'roles' => array('guest')),
                array('route' => 'zfcuser/forgotpassword', 'roles' => array('guest')),
                array('route' => 'zfcuser/resetpassword', 'roles' => array('guest')),
            	array('route' => 'zfcuser/logoutexpired', 'roles' => array('mod')),
            	array('route' => 'home', 'roles' => array('guest')),
            ),
        		
        ),    		
    ),
		 'view_manager' => array(
				'template_map' => array(
						//'layout/layout'   => './module/Application/view/layout/not-authorized-to-access.phtml',
						'error/403' => './module/Application/view/error/403.phtml'
				),
		), 
		 'module_layouts' => array(
				//'ZfcUser' => 'zfcuser/layout',
		), 
		
);
