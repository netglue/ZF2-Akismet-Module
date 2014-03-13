<?php

namespace NetglueAkismetTest\Validator;

use PHPUnit_Framework_TestCase;

use NetglueAkismet\Options\AkismetServiceOptions;
use NetglueAkismet\Service\AkismetService;
use NetglueAkismet\Validator\Akismet;

use NetglueAkismetTest\bootstrap;

/**
 * @coversDefaultClass NetglueAkismet\Validator\Akismet
 */
class AkismetTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @covers ::setAkismetService
	 * @covers ::getAkismetService
	 */
	public function testValidatorIsInitialisedWithService() {
		$sl = bootstrap::getServiceManager();

		
		$akismet = $this->getValidatorFromManager();
		$this->assertInstanceOf('NetglueAkismet\Validator\Akismet', $akismet);
		
		$service = $akismet->getAkismetService();
		$this->assertInstanceOf('NetglueAkismet\Service\AkismetService', $service);
		$this->assertSame($sl->get('NetglueAkismet\Service\AkismetService'), $service);
	}
	
	public function getValidatorFromManager() {
		$sl = bootstrap::getServiceManager();
		$manager = $sl->get('ValidatorManager');
		$akismet = $manager->get('AkismetValidator');
		return $akismet;
	}
	
	public function getServiceFromManager() {
		$sl = bootstrap::getServiceManager();
		return $sl->get('NetglueAkismet\Service\AkismetService');
	}
	
	/**
	 * @covers ::setAkismetService
	 * @covers ::getAkismetService
	 */
	public function testSetGetAkismetService() {
		$service = new AkismetService;
		$validator = new Akismet;
		
		$this->assertSame($validator, $validator->setAkismetService($service), 'Should be fluid');
		$this->assertSame($service, $validator->getAkismetService());
	}
	
	/**
	 * @covers ::setCommentType
	 * @covers ::getCommentType
	 */
	public function testSetGetCommentType() {
		$v = $this->getValidatorFromManager();
		$type = $v->getCommentType();
		$service = $this->getServiceFromManager();
		$expect = $service->getOptions()->getDefaultCommentType();
		$this->assertSame($expect, $type);
		
		$this->assertSame($v, $v->setCommentType('Foo'));
		$this->assertSame('Foo', $v->getCommentType());
	}
	
	/**
	 * @covers ::setContextMap
	 * @covers ::getContextMap
	 */
	public function testSetGetContextMapBasic() {
		$v = new Akismet;
		$map = array(
			'foo' => 'bar',
		);
		$this->assertInternalType('array', $v->getContextMap());
		$this->assertSame($v, $v->setContextMap($map));
		$this->assertSame($map, $v->getContextMap());
	}
	
	/**
	 * @covers ::__construct
	 */
	public function testOptionsInConstructorAreSet() {
		$map = array(
			'foo' => 'bar',
		);
		$options = array(
			'comment_type' => 'Foo',
			'context_map' => $map,
		);
		$v = new Akismet($options);
		$this->assertSame('Foo', $v->getCommentType());
		$this->assertSame($map, $v->getContextMap());
	}
	
	/**
	 * @covers ::__construct
	 */
	public function testConstructorAcceptsTraversable() {
		$map = array(
			'foo' => 'bar',
		);
		$array = new \ArrayObject(array(
			'comment_type' => 'Foo',
			'context_map' => $map,
		));
		$v = new Akismet($array);
		$this->assertSame('Foo', $v->getCommentType());
		$this->assertSame($map, $v->getContextMap());
	}
	
	/**
	 * @covers ::mapContext
	 */
	public function testMapContext() {
		$context = array(
			'myName' => 'Harry',
			'myEmail' => 'test@example.com',
			'myUserSite' => 'http://foo.com',
			'someOtherValue' => 'Foo',
			'comment_content' => 'ABC',
		);
		$map = array(
			'myName' => 'comment_author',
			'myEmail' => 'comment_author_email',
			'myUserSite' => 'comment_author_url',
		);
		$v = $this->getValidatorFromManager();
		$v->setContextMap($map);
		$result = $v->mapContext($context);
		$this->assertInternalType('array', $result);
		$expect = array(
			'comment_author' => 'Harry',
			'comment_author_email' => 'test@example.com',
			'comment_author_url' => 'http://foo.com',
			'comment_content' => 'ABC',
		);
		foreach($expect as $param => $value) {
			$this->assertArrayHasKey($param, $result);
			$this->assertSame($value, $result[$param]);
		}
	}
	
	/**
	 * @covers ::isValid
	 */
	public function testIsValid() {
		$v = new Akismet;
		$this->assertFalse($v->isValid(array()));
	}
}
