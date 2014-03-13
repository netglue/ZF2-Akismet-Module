<?php

namespace NetglueAkismet\Validator;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\AbstractValidator;
use NetglueAkismet\Service\AkismetService;
use NetglueAkismet\Service\AkismetServiceAwareInterface;


class Akismet extends AbstractValidator implements AkismetServiceAwareInterface {
	
	const MSG_INVALID = 'invalid';
	const MSG_SPAM = 'spam';
	
	/**
	 * Akismet Service
	 * @var AkismetService
	 */
	protected $akismetService;
	
	/**
	 * Message Templates
	 * @var array
	 */
	protected $messageTemplates = array(
		self::MSG_INVALID => 'Expected a string',
		self::MSG_SPAM => 'The information has been classified as spam',
	);
	
	/**
	 * Constructor
	 * @param Traversable|array $options
	 * @return void
	 */
	public function __construct($options = null) {
		if($options instanceof Traversable) {
			$options = ArrayUtils::iteratorToArray($options);
		}
		if(isset($options['comment_type'])) {
			$this->setCommentType($options['comment_type']);
		}
		if(isset($options['context_map'])) {
			$this->setContextMap($options['context_map']);
		}
		parent::__construct($options);
	}
	
	/**
	 * Given an array of form values, change the keys to those expected by the Akismet Service
	 * @param array $context
	 * @return array
	 */
	public function mapContext(array $context) {
		
		$service = $this->getAkismetService();
		$validParams = $service->getParameterListForMethod('comment-check');
		
		$map = $this->getContextMap();
		$out = array();
		
		foreach($map as $formField => $apiParam) {
			if(in_array($apiParam, $validParams)) {
				$out[$apiParam] = $context[$formField];
			}
		}
		
		// Also iterate over the context for matching parameter names
		foreach($context as $formField => $value) {
			if(in_array($formField, $validParams)) {
				$out[$formField] = $value;
			}
		}
		
		return $out;
	}
	
	/**
	 * Whether the given value is valid
	 * @param string $value
	 * @param array $context
	 * @return bool
	 */
	public function isValid($value, $context = NULL) {
		
		if(!is_string($value)) {
			$this->error(self::MSG_INVALID);
			return false;
		}
		
		if(NULL !== $context) {
			$context = $this->mapContext($context);
		}
		
		$service = $this->getAkismetService();
		$params = $this->mapContext($context);
		$result = $service->isSpam($value, NULL, $this->getCommentType(), $params);
		if(true === $result) {
			$this->error(self::MSG_SPAM);
			return false;
		}
		return true;
	}
	
	/**
	 * Set Akismet Service
	 * @param AkismetService $service
	 * @return self
	 */
	public function setAkismetService(AkismetService $service) {
		$this->akismetService = $service;
		return $this;
	}
	
	/**
	 * Get Akismet Service
	 * @return AkismetService|NULL
	 */
	public function getAkismetService() {
		return $this->akismetService;
	}
	
	/**
	 * Set the comment type
	 * @param string $type
	 * @return self
	 */
	public function setCommentType($type) {
		$this->options['comment_type'] = (string) $type;
		return $this;
	}
	
	/**
	 * Return comment type
	 * Queries the Akismet service for the configured default type and returns that if no comment type has been set for the validator instance
	 * @return string|NULL
	 */
	public function getCommentType() {
		$type = isset($this->options['comment_type']) ? $this->options['comment_type'] : NULL;
		if(!$type) {
			$type = $this->getAkismetService()->getOptions()->getDefaultCommentType();
		}
		return $type;
	}
	
	/**
	 * Provide an array that maps form field names to parameters expected by the Akismet API
	 * @param array $map
	 * @return self
	 */
	public function setContextMap(array $map) {
		$this->options['context_map'] = $map;
		return $this;
	}
	
	/**
	 * Return form context to API params map
	 * @return array
	 */
	public function getContextMap() {
		$map = isset($this->options['context_map']) ? $this->options['context_map'] : array();
		return $map;
	}
	
}
