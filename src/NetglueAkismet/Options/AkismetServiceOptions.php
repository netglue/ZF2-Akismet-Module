<?php

namespace NetglueAkismet\Options;

use Zend\Stdlib\AbstractOptions;
use NetglueAkismet\Exception;

use Zend\Validator\Uri as UriValidator;

class AkismetServiceOptions extends AbstractOptions {
	
	/**
	 * API Key
	 * @var string
	 */
	protected $apiKey;
	
	/**
	 * Default Comment Type
	 * @var string
	 * @link http://blog.akismet.com/2012/06/19/pro-tip-tell-us-your-comment_type/
	 */
	protected $defaultCommentType = 'comment';
	
	/**
	 * URL for the website the API is being called from
	 * @var string|NULL
	 */
	protected $websiteUrl;
	
	protected $websiteLanguage = 'en_GB';
	
	protected $websiteCharset = 'UTF-8';
	
	protected $invalidParamsThrowsException = false;
	
	/**
	 * URI Validator
	 * @var UriValidator|NULL
	 */
	protected $uriValidator;
	
	/**
	 * Set Akisment API Key
	 * @param string $key
	 * @return self
	 */
	public function setApiKey($key) {
		$this->apiKey = (string) $key;
		return $this;
	}
	
	/**
	 * Return API Key
	 * @return string|NULL
	 */
	public function getApiKey() {
		return $this->apiKey;
	}
	
	/**
	 * Set Default comment type
	 * @param string $type
	 * @return self
	 * @see http://blog.akismet.com/2012/06/19/pro-tip-tell-us-your-comment_type/
	 */
	public function setDefaultCommentType($type) {
		$this->defaultCommentType = (string) $type;
		return $this;
	}
	
	/**
	 * Get Default comment type
	 * @return string
	 */
	public function getDefaultCommentType() {
		return $this->defaultCommentType;
	}
	
	/**
	 * Return URI Validator instance
	 * @return UriValidator
	 */
	public function getUriValidator() {
		if(!$this->uriValidator) {
			$validator = new UriValidator(array('allowRelative'=>false));
			$this->setUriValidator($validator);
		}
		return $this->uriValidator;
	}
	
	/**
	 * Set URI Validator instance
	 * @param UriValidator $validator
	 * @return self
	 */
	public function setUriValidator(UriValidator $validator) {
		$this->uriValidator = $validator;
		return $this;
	}
	
	/**
	 * The blog/website full URI
	 * @param string $url
	 * @return self
	 * @throws Exception\InvalidArgumentException
	 */
	public function setWebsiteUrl($url = NULL) {
		if(empty($url)) {
			$this->websiteUrl = NULL;
			return $this;
		}
		$validator = $this->getUriValidator();
		if(!$validator->isValid($url)) {
			$message = sprintf('An invalid URI was provided: %s', $url);
			throw new Exception\InvalidArgumentException($message);
		}
		$this->websiteUrl = $url;
		return $this;
	}
	
	/**
	 * Return website Url
	 * @return string
	 */
	public function getWebsiteUrl() {
		return $this->websiteUrl;
	}
	
	/**
	 * Set Website Language
	 * @param string $lang
	 * @return self
	 */
	public function setWebsiteLanguage($lang) {
		$this->websiteLanguage = (string) $lang;
		return $this;
	}
	
	/**
	 * Get Website Language
	 * @return string|NULL
	 */
	public function getWebsiteLanguage() {
		return $this->websiteLanguage;
	}
	
	/**
	 * Set Website Charset
	 * @param string $charset
	 * @return self
	 */
	public function setWebsiteCharset($charset) {
		$this->websiteCharset = (string) $charset;
		return $this;
	}
	
	/**
	 * Get Website Charset
	 * @return string|NULL
	 */
	public function getWebsiteCharset() {
		return $this->websiteCharset;
	}
	
	/**
	 * Set flag to throw exceptions when processing api params
	 * @param bool $flag
	 * @return self
	 */
	public function setInvalidParamsThrowsException($flag) {
		$this->invalidParamsThrowsException = (bool) $flag;
		return $this;
	}
	
	/**
	 * Get flag to throw exceptions when processing api params
	 * @return bool
	 */
	public function getInvalidParamsThrowsException() {
		return $this->invalidParamsThrowsException;
	}
	
	/**
	 * Get flag to throw exceptions when processing api params
	 * @return bool
	 */
	public function throwExceptionForInvalidParams() {
		return $this->invalidParamsThrowsException;
	}
	
}
