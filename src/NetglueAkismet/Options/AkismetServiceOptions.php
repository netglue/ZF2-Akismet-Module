<?php

namespace NetglueAkismet\Options;

use Zend\Stdlib\AbstractOptions;
use NetglueAkismet\Exception;

use Zend\Uri\Http as HttpUri;
use Zend\Uri\Exception\ExceptionInterface as UriException;

class AkismetServiceOptions extends AbstractOptions {
	
	/**
	 * API Key
	 * @var string
	 */
	protected $apiKey;
	
	protected $defaultCommentType = 'comment';
	
	protected $websiteUrl;
	
	protected $websiteLanguage = 'en_GB';
	
	protected $websiteCharset = 'UTF-8';
	
	protected $invalidParamsThrowsException = false;
	
	public function setApiKey($key) {
		$this->apiKey = (string) $key;
		return $this;
	}
	
	public function getApiKey() {
		return $this->apiKey;
	}
	
	/**
	 * @see http://blog.akismet.com/2012/06/19/pro-tip-tell-us-your-comment_type/
	 */
	public function setDefaultCommentType($type) {
		$this->defaultCommentType = (string) $type;
		return $this;
	}
	
	public function getDefaultCommentType() {
		return $this->defaultCommentType;
	}
	
	/**
	 * The blog/website full URI
	 * @param string $url
	 * @return self
	 * @throws Exception\InvalidArgumentException
	 */
	public function setWebsiteUrl($url) {
		try {
			$uri = new HttpUri($url);
			$this->websiteUrl = (string) $uri;
			return $this;
		} catch(UriException $e) {
			$message = sprintf('An invalid URI was provided: %s', $url);
			throw new Exception\InvalidArgumentException($message, NULL, $e);
		}
	}
	
	public function getWebsiteUrl() {
		return $this->websiteUrl;
	}
	
	public function setWebsiteLanguage($lang) {
		$this->websiteLanguage = (string) $lang;
		return $this;
	}
	
	public function getWebsiteLanguage() {
		return $this->websiteLanguage;
	}
	
	public function setWebsiteCharset($charset) {
		$this->websiteCharset = (string) $charset;
		return $this;
	}
	
	public function getWebsiteCharset() {
		return $this->websiteCharset;
	}
	
	public function setInvalidParamsThrowsException($flag) {
		$this->invalidParamsThrowsException = (bool) $flag;
		return $this;
	}
	
	public function getInvalidParamsThrowsException() {
		return $this->invalidParamsThrowsException;
	}
	
	public function throwExceptionForInvalidParams() {
		return $this->invalidParamsThrowsException;
	}
	
}
