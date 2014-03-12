<?php

namespace NetglueAkismet\Service;

use NetglueAkismet\Options\AkismetServiceOptions;
use NetglueAkismet\Exception;
use Zend\Http\Client as HttpClient;
use Zend\Uri\Http as HttpUri;

class AkismetService {
	
	const AKISMET_VERSION = '1.1';
	
	const AKISMET_SERVICE_DOMAIN = 'rest.akismet.com';
	
	const AKISMET_SCHEME = 'http';
	
	const AKISMET_METHOD = 'post';
	
	
	const VERSION = '0.0.1';
	
	const USER_AGENT = 'NetglueAkismetModule';
	
	protected $options;
	
	protected $httpClient;
	
	protected $website;
	protected $permalink;
	
	protected $endpoints = array(
		'commentCheck' => 'comment-check',
		'keyVerify' => 'verify-key',
		'submitSpam' => 'submit-spam',
		'submitHam' => 'submit-ham',
	);
	
	protected $validParams = array(
		'commentCheck' => array(
			'blog',
			'user_ip',
			'user_agent',
			'referrer',
			'permalink',
			'comment_type',
			'comment_author',
			'comment_author_email',
			'comment_author_url',
			'comment_content',
			'comment_date_gmt',
			'comment_post_modified_gmt',
			'blog_lang',
			'blog_charset',
		),
	);
	
	public function __construct($options = NULL) {
		if($options) {
			$this->setOptions($options);
		}
	}
	
	public function setOptions($options = array()) {
		if(!$options instanceof AkismetServiceOptions) {
			$options = new AkismetServiceOptions($options);
		}
		$this->options = $options;
		return $this;
	}
	
	public function getOptions() {
		if(!$this->options instanceof AkismetServiceOptions) {
			$this->setOptions(array());
		}
		return $this->options;
	}
	
	
	public function setHttpClient(HttpClient $client) {
		$this->httpClient = $client;
		return $this;
	}
	
	public function getHttpClient() {
		if(!$this->httpClient) {
			$client = new HttpClient;
			$this->setHttpClient($client);
		}
		return $this->httpClient;
	}
	
	/**
	 * @todo Ignore invalid params, don't throw exceptions
	 */
	public function isSpam($content = NULL, $email = NULL, $type = NULL, $params = array()) {
		$options = $this->getOptions();
		$data = $this->getDefaultParamsFromRequest();
		$data = array_merge($data, $params);
		$data['comment_content'] = $content;
		$data['comment_author_email'] = $email;
		$data['comment_type'] = $type;
		$this->validateParams($data);
		
		$client = $this->getHttpClient();
		$clientOptions = array(
			'useragent' => $this->getUserAgent(),
		);
		$uri = sprintf('%s://%s.%s/%s/%s',
			self::AKISMET_SCHEME,
			$options->getApiKey(),
			self::AKISMET_SERVICE_DOMAIN,
			self::AKISMET_VERSION,
			$this->endpoints['commentCheck']);
		try {
			$client->reset()
				->setUri($uri)
				->setMethod(self::AKISMET_METHOD)
				->setOptions($clientOptions);
		
			$method = 'setParameter'.ucfirst(strtolower(self::AKISMET_METHOD));
			$client->{$method}($data);
			$response = $client->send();
			if($response->isSuccess()) {
				$body = $response->getBody();
				if(strtolower($body) == 'true') {
					return true;
				}
				if(strtolower($body) == 'false') {
					return false;
				}
			}
			throw new Exception\RuntimeException('Invalid Akismet Request: '.$body);
		} catch(\Exception $e) {
			throw new Exception\RuntimeException('Exception thrown', NULL, $e);
		}
		
	}
	
	protected function validateParams($method, $params = array()) {
		$valid = $this->validParams[$method];
		foreach($params as $param => $value) {
			if(!in_array($param, $valid)) {
				throw new Exception\InvalidArgumentException('Invalid parameter: '.$param);
			}
		}
	}
	
	protected function getDefaultParamsFromRequest() {
		$options = $this->getOptions();
		
		$data = array(
			'blog' => (string) $this->getWebsiteUri(),
			'permalink' => (string) $this->getPermalinkUri(),
			'blog_lang' => $options->getWebsiteLanguage(),
			'blog_charset' => $options->getWebsiteCharset(),
			'comment_type' => $options->getDefaultCommentType(),
		);
		
		$data['user_ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;
		$data['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : NULL;
		$data['referrer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL;
		return $data;
	}
	
	public function getPermalinkUri() {
		if($this->permalink) {
			return $this->permalink;
		}
		
		$permalink = clone $this->getWebsiteUri();
		if(isset($_SERVER['REQUEST_URI'])) {
			$permalink->setPath($_SERVER['REQUEST_URI']);
		}
		$this->permalink = $permalink;
		return $permalink;
	}
	
	public function getWebsiteUri() {
		if($this->website) {
			return $this->website;
		}
		$uri = $this->getOptions()->getWebsiteUrl();
		if(!empty($uri)) {
			$website = new HttpUri($uri);
		} else {
			$website = new HttpUri;
			if(isset($_SERVER['HTTP_HOST'])) {
				$website->setHost($_SERVER['HTTP_HOST']);
			}
			$website->setScheme('http');
			if(
				(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')
				||
				(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')
			) {
				$website->setScheme('https');
			}
		}
		if(!$website->isValid()) {
			throw new Exception\RuntimeException('Cannot determine current host');
		}
		$this->website = $website;
		return $website;
	}
	
	public function getUserAgent() {
		return sprintf('%s/%s', self::USER_AGENT, self::VERSION);
	}
	
}