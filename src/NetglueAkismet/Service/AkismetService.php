<?php

namespace NetglueAkismet\Service;

use NetglueAkismet\Options\AkismetServiceOptions;
use NetglueAkismet\Exception;
use Zend\Http\Client as HttpClient;
use Zend\Uri\Http as HttpUri;

class AkismetService {
	
	/**
	 * Api Version used for constructing URIs
	 */
	const AKISMET_VERSION = '1.1';
	
	/**
	 * Api Domain
	 */
	const AKISMET_SERVICE_DOMAIN = 'rest.akismet.com';
	
	/**
	 * Api Scheme (http)
	 */
	const AKISMET_SCHEME = 'http';
	
	/**
	 * HTTP Method for API requests
	 */
	const AKISMET_METHOD = 'post';
	
	/**
	 * Akismet Client Version
	 */
	const VERSION = '0.0.1';
	
	/**
	 * User Agent String
	 */
	const USER_AGENT = 'NetglueAkismetModule';
	
	/**
	 * Client Options
	 * @var AkismetServiceOptions
	 */
	protected $options;
	
	/**
	 * HTTP Client
	 * @var HttpClient
	 */
	protected $httpClient;
	
	/**
	 * Website Uri
	 * @var HttpUri
	 */
	protected $website;
	
	/**
	 * Current Request permalink
	 * @var HttpUri
	 */
	protected $permalink;
	
	/**
	 * API End Point Paths
	 * @var array
	 */
	protected $endpoints = array(
		'comment-check' => 'comment-check',
		'verify-key' => 'verify-key',
		'submit-spam' => 'submit-spam',
		'submit-ham' => 'submit-ham',
	);
	
	/**
	 * Valid parameters for each api method
	 * @var array
	 */
	protected $validParams = array(
		'comment-check' => array(
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
		'submit-spam' => array(
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
		'submit-ham' => array(
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
		'verify-key' => array(
			'blog',
			'key',
		),
	);
	
	/**
	 * Optionally  Provide options to the constructor
	 * @param array|Traversable|AkismetServiceOptions $options
	 * @return void
	 */
	public function __construct($options = NULL) {
		if($options) {
			$this->setOptions($options);
		}
	}
	
	/**
	 * Set Options
	 * @param array|Traversable|AkismetServiceOptions $options
	 * @return self
	 */
	public function setOptions($options = array()) {
		if(!$options instanceof AkismetServiceOptions) {
			$options = new AkismetServiceOptions($options);
		}
		$this->options = $options;
		return $this;
	}
	
	/**
	 * Get Options
	 * @return AkismetServiceOptions $options
	 */
	public function getOptions() {
		if(!$this->options instanceof AkismetServiceOptions) {
			$this->setOptions(array());
		}
		return $this->options;
	}
	
	/**
	 * Set HTTP Client
	 * @param HttpClient $client
	 * @return self
	 */
	public function setHttpClient(HttpClient $client) {
		$this->httpClient = $client;
		return $this;
	}
	
	/**
	 * Get Http Client
	 * @return HttpClient
	 */
	public function getHttpClient() {
		if(!$this->httpClient) {
			$client = new HttpClient;
			$this->setHttpClient($client);
		}
		return $this->httpClient;
	}
	
	/**
	 * Return URI for the given API method
	 * @param string $method
	 * @return string
	 */
	public function getMethodUri($method) {
		if(!array_key_exists($method, $this->endpoints)) {
			throw new Exception\InvalidArgumentException("{$method} is not a valid API method");
		}
		if($method === 'verify-key') {
			return sprintf('%s://%s/%s/%s',
				self::AKISMET_SCHEME,
				self::AKISMET_SERVICE_DOMAIN,
				self::AKISMET_VERSION,
				$this->endpoints['keyVerify']);
		}
		return sprintf('%s://%s.%s/%s/%s',
			self::AKISMET_SCHEME,
			$options->getApiKey(),
			self::AKISMET_SERVICE_DOMAIN,
			self::AKISMET_VERSION,
			$this->endpoints[$method]);
	}
	
	/**
	 * Call the given method with the params provided
	 * @param string $method
	 * @param array $params
	 * @return \Zend\Http\Response
	 * @throws Exception\ExceptionInterface
	 */
	public function call($method, array $params) {
		if(!array_key_exists($method, $this->endpoints)) {
			throw new Exception\InvalidArgumentException("{$method} is not a valid API method");
		}
		
		// Strip out invalid params and normalise
		$data = $this->prepareParams($method, $params);
		
		// Setup HTTP Client
		$client = $this->getHttpClient();
		$clientOptions = array(
			'useragent' => $this->getUserAgent(),
		);
		try {
			$client->reset()
				->setUri($this->getMethodUri($method))
				->setMethod(self::AKISMET_METHOD)
				->setOptions($clientOptions);
		
			$clientMethod = 'setParameter'.ucfirst(strtolower(self::AKISMET_METHOD));
			$client->{$clientMethod}($data);
			$response = $client->send();
			if($response->isSuccess()) {
				return $response;
			}
			throw new Exception\RuntimeException('Invalid Akismet Request: '.$response->getReasonPhrase(), $response->getStatusCode());
		} catch(\Exception $e) {
			throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
		}
	}
	
	/**
	 * Whether the given content is considered spam
	 * @param string $content
	 * @param string $email
	 * @param string $type
	 * @param array $params
	 * @return bool True = Spammy, False = Not Spammy
	 * @throws Exception\ExceptionIterface
	 * @link https://akismet.com/development/api/#comment-check
	 */
	public function isSpam($content, $email = NULL, $type = NULL, $params = array()) {
		$params['comment_content'] = $content;
		$params['comment_author_email'] = $email;
		// Only set comment type if non-empty otherwise default in config used
		if(!empty($type)) {
			$params['comment_type'] = $type;
		}
		
		$response = $this->call('comment-check', $params);
		$body = $response->getBody();
		if(strtolower($body) == 'true') {
			return true;
		}
		return false;
	}
	
	public function submitSpam($content, $email = NULL, $type = NULL, $params = array()) {
		$params['comment_content'] = $content;
		$params['comment_author_email'] = $email;
		// Only set comment type if non-empty otherwise default in config used
		if(!empty($type)) {
			$params['comment_type'] = $type;
		}
		
		$response = $this->call('submit-spam', $params);
		$body = $response->getBody();
		if(strtolower(trim($body)) == 'thanks for making the web a better place.') {
			return true;
		}
		return false;
	}
	
	public function submitHam($content, $email = NULL, $type = NULL, $params = array()) {
		$params['comment_content'] = $content;
		$params['comment_author_email'] = $email;
		// Only set comment type if non-empty otherwise default in config used
		if(!empty($type)) {
			$params['comment_type'] = $type;
		}
		
		$response = $this->call('submit-spam', $params);
		$body = $response->getBody();
		if(strtolower(trim($body)) == 'thanks for making the web a better place.') {
			return true;
		}
		return false;
	}
	
	public function verifyKey($params = array()) {
		if(!isset($params['key'])) {
			$params['key'] = $this->getOptions()->getApiKey();
		}
		$response = $this->call('verify-key', $params);
		$body = $response->getBody();
		if(strtolower(trim($body)) == 'valid') {
			return true;
		}
		return false;
	}
	
	/**
	 * Prepare Post params array for the given method
	 * @param string $method
	 * @param array $params
	 * @return array
	 */
	public function prepareParams($method, array $params) {
		// Load defaults
		$options = $this->getOptions();
		$data = $this->getDefaultParamsFromRequest();
		// Merge in user params overwriting defaults
		$data = array_merge($data, $params);
		$this->normaliseParams($method, $data);
		return $data;
	}
	
	/**
	 * Strips out parameters that are not listed in $this->validParams for the given method or throws an exception depending on config
	 * @param string $method
	 * @param array &$params
	 * @throws Exception\InvalidArgumentException
	 * @return void
	 */
	public function normaliseParams($method, &$params = array()) {
		$valid = $this->validParams[$method];
		foreach($params as $param => $value) {
			if(!in_array($param, $valid)) {
				if($this->getOptions()->throwExceptionForInvalidParams()) {
					throw new Exception\InvalidArgumentException(sprintf('Invalid parameter %s for API method %s',
						$param,
						$method));
				}
				unset($params[$param]);
			}
		}
	}
	
	/**
	 * Returns an array of request data to be sent to the remote service populated with defaults from options and data from the current request
	 * @return array
	 */
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
	
	/**
	 * Return the full URI for the current request
	 * Prefixed by configured website address
	 * @return HttpUri
	 */
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
	
	/**
	 * Return the base website URI
	 *
	 * Either returns whatever was configured, or works it out from the current request
	 * @return HttpUri
	 */
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
	
	/**
	 * Return user agent string for this client
	 * @return string
	 */
	public function getUserAgent() {
		return sprintf('%s/%s', self::USER_AGENT, self::VERSION);
	}
	
	/**
	 * Return the array of expected paramters for the given api method
	 * @param string $method
	 * @return array
	 * @throws Exception\InvalidArgumentException if the given method does not exist
	 */
	public function getParameterListForMethod($method) {
		if(!array_key_exists($method, $this->endpoints)) {
			throw new Exception\InvalidArgumentException('No such method '.$method);
		}
		return $this->validParams[$method];
	}
}
