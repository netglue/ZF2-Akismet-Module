<?php

namespace NetglueAkismet\Service;

interface AkismetServiceAwareInterface {
	
	public function setAkismetService(AkismetService $service);
	
	public function getAkismetService();
	
}
