<?php

namespace XelaxFormgen;

return array(
	'controllers' => array(
		'invokables' => array(
			'XelaxFormgen\Controller\Index' => Controller\IndexController::class,
		),
	),
	
	'console' => array(
		'router' => array(
			'routes' => array(
				'xelax-formgen' => array(
					'options' => array(
						'route' => 'formgen generate [--module=] [--entity=]',
						'defaults' => array(
							'controller' => 'XelaxFormgen\Controller\Index',
							'action' => 'index'
						),
					),
				),
			),
		),
	),
	
	'service_manager' => array(
		'factories' => array(
			'XelaxFormgen\Options\Formgen' => function (\Zend\ServiceManager\ServiceManager $sm) {
				$config = $sm->get('Config');
				return new Options\FormgenOptions(isset($config['xelax-formgen']) ? $config['xelax-formgen'] : array());
			},
		),
	),
	
);