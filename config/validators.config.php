<?php

return array(
	
	'factories' => array(
		
	),
	
	'aliases' => array(
	
	),
	
	'invokables' => array(
		
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
