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
class AkismetServiceNetworkTest extends PHPUnit_Framework_TestCase {
	
	protected $spam = array(
		//'comment_content' => 
	);
	
	/**
	 * @covers NetglueAkismet\Factory\AkismetServiceFactory::createService
	 */
	public function testServiceLocatorConfig() {
		$sl = bootstrap::getServiceManager();
		$key = 'NetglueAkismet\Service\AkismetService';
		$service = $sl->get($key);
		$this->assertInstanceOf('NetglueAkismet\Service\AkismetService', $service);
	}
	
	public function getService() {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$sl = bootstrap::getServiceManager();
		$key = 'NetglueAkismet\Service\AkismetService';
		$service = $sl->get($key);
		return $service;
	}
	
	public function testForConfiguredApiKey() {
		$service = $this->getService();
		$key = $service->getOptions()->getApiKey();
		$this->assertInternalType('string', $key, 'The network tests require a valid api key');
		$this->assertTrue( strlen($key) > 1, 'The network tests require a valid api key' );
	}
	
	public function testForConfiguredWebsiteUri() {
		$service = $this->getService();
		$uri = $service->getWebsiteUri();
		$this->assertTrue($uri->isValid(), 'The network tests require a valid website uri');
	}
	
	/**
	 * @covers ::verifyKey
	 * @covers ::call
	 * @depends testForConfiguredApiKey
	 */
	public function testVerifyKey() {
		$service = $this->getService();
		$result = $service->verifyKey(array(
			'key' => 'Invalid Key',
		));
		$this->assertFalse($result, 'Expected false for testing an invalid api key');
		
		$result = $service->verifyKey();
		$this->assertTrue($result, 'Either your API key configured is invalid or the test really did fail');
	}
	
	/**
	 * @covers ::isSpam
	 * @covers ::call
	 * @depends testVerifyKey
	 */
	public function testIsSpam() {
		$service = $this->getService();
		$params = array(
			'comment_author' => 'viagra-test-123', // Force a true response
			'comment_author_email' => 'viagra-test-123', // Force a true response
		);
		$result = $service->isSpam('viagra-test-123', 'viagra-test-123', 'comment', $params);
		$this->assertInternalType('bool', $result);
		$this->assertTrue($result, 'Expected isSpam to return true: ');
		
		$params = array(
			'user_role' => 'administrator',
		);
		$result = $service->isSpam('Test', 'someone@example.com', 'comment', $params);
		$this->assertInternalType('bool', $result);
		$this->assertFalse($result, 'Expected isSpam to return false');
	}
}
