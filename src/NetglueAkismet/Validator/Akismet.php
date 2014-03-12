<?php

namespace NetglueAkismet\Validator;

use Zend\Validator\AbstractValidator;

class Akismet extends AbstractValidator implements AkismetServiceAwareInterface {
	
	const INVALID = 'invalid';
	const SPAM = 'spam';
	
	protected $akismetService;
	
	protected $messageTemplates = array(
		self::INVALID => 'Expected a string',
		self::SPAM => 'The information has been classified as spam',
	);
	
	/**
	 * Options
	 * @var array
	 */
	
	/**
	 * @todo Comment Type should be a validator option
	 */
	
	public function mapContext($context) {
		
		return array(
			'comment_author_email' => NULL,
			'comment_type' => NULL,
		);
		
	}
	
	public function isValid($value, $context = NULL) {
		
		if(!is_string($value)) {
			$this->error(self::INVALID);
			return false;
		}
		
		if(NULL !== $context) {
			$context = $this->mapContext($context);
		}
		
		$service = $this->getAkismetService();
		
		$result = $service->isSpam($value, $email, $type, $context);
		if(true === $result) {
			$this->error(self::SPAM);
			return false;
		}
		return true;
	}
	
	public function setAkismetService(AkismetService $service) {
		$this->akismetService = $service;
		return $this;
	}
	
	public function getAkismetService() {
		return $this->akismetService;
	}
	
}
