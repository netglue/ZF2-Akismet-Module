<?php

return array(
	
	'factories' => array(
		
	),
	
	'aliases' => array(
		'AkismetValidator' => 'NetglueAkismet\Validator\Akismet',
	),
	
	'invokables' => array(
		'NetglueAkismet\Validator\Akismet' => 'NetglueAkismet\Validator\Akismet',
	),
	
	'initializers' => array(
		function($instance, $sl) {
			if($instance instanceof \NetglueAkismet\Service\AkismetServiceAwareInterface) {
				$appServices = $sl->getServiceLocator();
				$service = $appServices->get('NetglueAkismet\Service\AkismetService');
				$instance->setAkismetService($service);
			}
		},
	),
	
);
