<?php

return array(

	'factories' => array(
		
		/**
		 * Akismet Service
		 */
		'NetglueAkismet\Service\AkismetService' => 'NetglueAkismet\Factory\AkismetServiceFactory',
		
	),
	
	'aliases' => array(
		'AkismetService' => 'NetglueAkismet\Service\AkismetService',
	),
	
);
