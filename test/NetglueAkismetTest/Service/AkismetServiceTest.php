<?php

namespace NetglueAkismetTest\Service;

use PHPUnit_Framework_TestCase;

use NetglueAkismet\Options\AkismetServiceOptions;
use NetglueAkismet\Service\AkismetService;

use Zend\Http\Client as HttpClient;

use NetglueAkismetTest\bootstrap;

/**
 * @coversDefaultClass NetglueAkismet\Service\AkismetService
 */
class AkismetServiceTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @covers NetglueAkismet\Factory\AkismetServiceFactory::createService
	 */
	public function testServiceLocatorConfig() {
		$sl = bootstrap::getServiceManager();
		$key = 'NetglueAkismet\Service\AkismetService';
		$service = $sl->get($key);
		$this->assertInstanceOf('NetglueAkismet\Service\AkismetService', $service);
	}
	
	/**
	 * @covers ::__construct
	 * @covers ::getOptions
	 * @covers ::setOptions
	 */
	public function testConstructorInitialisesOptions() {
		$service = new AkismetService(array(
			'websiteUrl' => 'http://example.com',
		));
		$options = $service->getOptions();
		$this->assertInstanceOf('NetglueAkismet\Options\AkismetServiceOptions', $options);
		$this->assertSame('http://example.com', $options->getWebsiteUrl());
	}
	
	/**
	 * @covers ::setOptions
	 */
	public function testSetOptionsAcceptsArray() {
		$options = array(
			'websiteUrl' => 'http://example.com',
			'defaultCommentType' => 'test',
		);
		$service = new AkismetService;
		$this->assertSame($service, $service->setOptions($options), 'setOptions() should be fluid');
		$options = $service->getOptions();
		$this->assertSame('http://example.com', $options->getWebsiteUrl());
		$this->assertSame('test', $options->getDefaultCommentType());
	}
	
	/**
	 * @covers ::setOptions
	 * @covers ::getOptions
	 */
	public function testGetOptionsReturnsSameInstance() {
		$service = new AkismetService;
		$options = $service->getOptions();
		$this->assertInstanceOf('NetglueAkismet\Options\AkismetServiceOptions', $options, 'getOptions() should always return an instanceof AkismetServiceOptions');
		$this->assertSame($options, $service->getOptions());
		
		$newOptions = new AkismetServiceOptions;
		$service->setOptions($newOptions);
		$this->assertSame($newOptions, $service->getOptions());
	}
	
	/**
	 * @covers ::setHttpClient
	 * @covers ::getHttpClient
	 */
	public function testSetGetHttpClient() {
		$service = new AkismetService;
		$client = $service->getHttpClient();
		$this->assertInstanceOf('Zend\Http\Client', $client);
		$this->assertSame($client, $service->getHttpClient());
		
		$newClient = new HttpClient;
		$this->assertSame($service, $service->setHttpClient($newClient));
		$this->assertSame($newClient, $service->getHttpClient());
	}
	
	/**
	 * @covers ::getMethodUri
	 */
	public function testGetMethodUri() {
		$service = new AkismetService(array(
			'apiKey' => 'test',
		));
		
		$expect = array(
			'verify-key' => 'http://rest.akismet.com/1.1/verify-key',
			'comment-check' => 'http://test.rest.akismet.com/1.1/comment-check',
			'submit-spam' => 'http://test.rest.akismet.com/1.1/submit-spam',
			'submit-ham' => 'http://test.rest.akismet.com/1.1/submit-ham',
		);
		
		foreach($expect as $method => $uri) {
			$this->assertSame($uri, $service->getMethodUri($method));
		}
	}
	
	/**
	 * @covers ::getMethodUri
	 * @expectedException NetglueAkismet\Exception\InvalidArgumentException
	 * @expectedExceptionMessage Unknown is not a valid API method
	 */
	public function testGetMethodUriThrowsExceptionForInvalidMethod() {
		$service = new AkismetService;
		$service->getMethodUri('Unknown');
	}
	
	/**
	 * @covers ::getUserAgent
	 */
	public function testGetUserAgent() {
		$service = new AkismetService;
		$ua = $service->getUserAgent();
		$this->assertInternalType('string', $ua);
	}
	
	/**
	 * @covers ::getAvailableEndpoints
	 */
	public function testGetAvailableEndpoints() {
		$service = new AkismetService;
		$endpoints = $service->getAvailableEndpoints();
		$this->assertInternalType('array', $endpoints);
	}
	
	/**
	 * @covers ::getParameterListForMethod
	 */
	public function testGetParameterListForMethod() {
		$service = new AkismetService;
		foreach($service->getAvailableEndpoints() as $method => $endpoint) {
			$params = $service->getParameterListForMethod($method);
			$this->assertInternalType('array', $params);
		}
	}
	
	/**
	 * @covers ::getParameterListForMethod
	 * @expectedException NetglueAkismet\Exception\InvalidArgumentException
	 * @expectedExceptionMessage No such method Unknown
	 */
	public function testGetParameterListForMethodThrowsExceptionForInvalidMethod() {
		$service = new AkismetService;
		$service->getParameterListForMethod('Unknown');
	}
	
	/**
	 * @covers ::getWebsiteUri
	 * @expectedException NetglueAkismet\Exception\RuntimeException
	 * @expectedExceptionMessage Cannot determine current host
	 */
	public function testGetWebsiteUriThrowsExceptionWhenNoWebsiteCanBeDetermined() {
		$service = new AkismetService;
		$service->getWebsiteUri();
	}
	
	/**
	 * @covers ::getWebsiteUri
	 */
	public function testGetWebsiteUriReturnsUriWhenGivenValidUri() {
		$service = new AkismetService(array(
			'websiteUrl' => 'http://example.com/',
		));
		$uri = $service->getWebsiteUri();
		$this->assertInstanceOf('Zend\Uri\Http', $uri);
		$string = (string) $uri;
		$this->assertSame('http://example.com/', $string);
		
		$this->assertSame($uri, $service->getWebsiteUri(), 'getWebsiteUri() should return the same instance on successive calls');
	}
	
	/**
	 * @covers ::getWebsiteUri
	 */
	public function testGetWebsiteUriReturnsUriWhenValuesFoundInServerArray() {
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SERVER_PORT'] = '443';
		$service = new AkismetService;
		$uri = $service->getWebsiteUri();
		$this->assertSame('https://example.com', (string) $uri);
		
		unset($_SERVER['SERVER_PORT']);
		
		$_SERVER['HTTPS'] = 'ON';
		$service = new AkismetService;
		$uri = $service->getWebsiteUri();
		$this->assertSame('https://example.com', (string) $uri);
		
		unset($_SERVER['HTTPS']);
		$service = new AkismetService;
		$uri = $service->getWebsiteUri();
		$this->assertSame('http://example.com', (string) $uri);
	}
	
	/**
	 * @covers ::getPermalinkUri
	 * @covers ::getWebsiteUri
	 * @depends testGetWebsiteUriReturnsUriWhenGivenValidUri
	 */
	public function testGetPermalinkUri() {
		$service = new AkismetService(array(
			'websiteUrl' => 'http://example.com/',
		));
		$website = $service->getWebsiteUri();
		
		$permalink = $service->getPermalinkUri();
		$this->assertInstanceOf('Zend\Uri\Http', $permalink);
		$this->assertFalse( $website === $permalink, 'Permalink and website should be different instances');
		$this->assertSame($permalink, $service->getPermalinkUri(), 'getPermalinkUri() should return the same instance on successive calls');
		$this->assertSame( (string) $website, (string) $permalink);
	}
	
	/**
	 * @covers ::getPermalinkUri
	 * @covers ::getWebsiteUri
	 * @depends testGetPermalinkUri
	 */
	public function testGetPermalinkUriSetsPathWhenAvailable() {
		$_SERVER['REQUEST_URI'] = '/foo/bar';
		$service = new AkismetService(array(
			'websiteUrl' => 'http://example.com/',
		));
		$permalink = $service->getPermalinkUri();
		$this->assertSame('http://example.com/foo/bar', (string) $permalink);
	}
	
	/**
	 * @covers ::getDefaultParamsFromRequest
	 */
	public function testGetDefaultParamsFromRequestBasic() {
		$service = new AkismetService(array(
			'websiteUrl' => 'http://example.com/',
		));
		$expect = array(
			'blog',
			'permalink',
			'blog_lang',
			'blog_charset',
			'comment_type',
			'user_ip',
			'user_agent',
			'referrer',
		);
		
		$params = $service->getDefaultParamsFromRequest();
		$this->assertInternalType('array', $params);
		foreach($expect as $param) {
			$this->assertArrayHasKey($param, $params);
		}
	}
	
	/**
	 * @covers ::normaliseParams
	 */
	public function testNormaliseParamsBasic() {
		$service = new AkismetService;
		$options = $service->getOptions();
		$options->setInvalidParamsThrowsException(false);
		$input = array(
			'foo' => 'bar',
			'blog' => 'http://example.com',
		);
		
		$expect = array(
			'blog' => 'http://example.com',
		);
		
		$service->normaliseParams('comment-check', $input);
		$this->assertSame($expect, $input);
	}
	
	/**
	 * @covers ::normaliseParams
	 * @expectedException NetglueAkismet\Exception\InvalidArgumentException
	 * @expectedExceptionMessage Invalid parameter foo for API method comment-check
	 */
	public function testNormaliseParamsThrowsExceptionWhenConfigured() {
		$service = new AkismetService;
		$options = $service->getOptions();
		$options->setInvalidParamsThrowsException(true);
		$input = array(
			'foo' => 'bar',
			'blog' => 'http://example.com',
		);
		$service->normaliseParams('comment-check', $input);
	}
	
	/**
	 * @covers ::prepareParams
	 */
	public function testPrepareParamsBasic() {
		$service = new AkismetService;
		$options = $service->getOptions();
		$options->setInvalidParamsThrowsException(false);
		
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SERVER_PORT'] = '443';
		$_SERVER['REQUEST_URI'] = '/foo/bar';
		$_SERVER['REMOTE_ADDR'] = '12.34.56.78';
		$_SERVER['HTTP_USER_AGENT'] = 'Foo';
		
		$input = array(
			'foo' => 'bar',
			'user_ip' => '127.0.0.1',
			'comment_content' => 'Test',
		);
		
		$expect = array(
			'blog' => 'https://example.com',
			'user_ip' => '127.0.0.1',
			'user_agent' => 'Foo',
			'referrer' => NULL,
			'permalink' => 'https://example.com/foo/bar',
			'comment_type' => $options->getDefaultCommentType(),
			'comment_content' => 'Test',
			'blog_lang' => $options->getWebsiteLanguage(),
			'blog_charset' => $options->getWebsiteCharset(),
		);
		
		$output = $service->prepareParams('comment-check', $input);
		
		foreach($expect as $key => $value) {
			$this->assertArrayHasKey($key, $output);
			$this->assertSame($value, $output[$key]);
		}
		
	}
	
	
	
	/**
	 * @covers ::call
	 * @expectedException NetglueAkismet\Exception\InvalidArgumentException
	 * @expectedExceptionMessage foo is not a valid API method
	 */
	public function testCallThrowsExceptionForInvalidMethod() {
		$service = new AkismetService;
		$service->call('foo', array());
	}
}
