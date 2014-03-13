<?php

namespace NetglueAkismetTest\Options;

use PHPUnit_Framework_TestCase;

use NetglueAkismet\Options\AkismetServiceOptions;


/**
 * @coversDefaultClass NetglueAkismet\Options\AkismetServiceOptions
 */
class AkismetServiceOptionsTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @covers ::setApiKey
	 * @covers ::getApiKey
	 */
	public function testSetGetApiKey() {
		$options = new AkismetServiceOptions;
		$this->assertNull($options->getApiKey(), 'API key should be initially NULL');
		$this->assertSame($options, $options->setApiKey('Foo'), 'setApiKey should be fluid');
		$this->assertSame('Foo', $options->getApiKey(), 'Unexpected return value');
		$options->setApiKey(999);
		$this->assertSame('999', $options->getApiKey(), 'setApiKey() should cast to a string');
	}
	
	/**
	 * @covers ::setDefaultCommentType
	 * @covers ::getDefaultCommentType
	 */
	public function testSetGetDefaultCommentType() {
		$options = new AkismetServiceOptions;
		$this->assertInternalType('string', $options->getDefaultCommentType(), 'Default comment type should initially be set to a reasonable value');
		$this->assertSame($options, $options->setDefaultCommentType('Foo'), 'setDefaultCommentType() should be fluid');
		$this->assertSame('Foo', $options->getDefaultCommentType(), 'Unexpected return value');
		$options->setDefaultCommentType(999);
		$this->assertSame('999', $options->getDefaultCommentType(), 'getDefaultCommentType() should cast to a string');
	}
	
	/**
	 * @covers ::setWebsiteUrl
	 * @covers ::getWebsiteUrl
	 * @covers ::getUriValidator
	 * @covers ::setUriValidator
	 */
	public function testSetGetWebsiteUrl() {
		$options = new AkismetServiceOptions;
		$this->assertNull($options->getWebsiteUrl(), 'Website URL should be initially NULL');
		$this->assertSame($options, $options->setWebsiteUrl('http://example.com'), 'setWebsiteUrl() should be fluid');
		$this->assertSame('http://example.com', $options->getWebsiteUrl(), 'Unexpected return value');
	}
	
	/**
	 * @covers ::setWebsiteUrl
	 * @covers ::getWebsiteUrl
	 */
	public function testSetWebsiteUrlPermitsEmptyValue() {
		$options = new AkismetServiceOptions;
		$options->setWebsiteUrl('http://example.com');
		$options->setWebsiteUrl('');
		$this->assertNull($options->getWebsiteUrl());
	}
	
	/**
	 * @covers ::setWebsiteUrl
	 * @covers ::getUriValidator
	 * @covers ::setUriValidator
	 * @expectedException NetglueAkismet\Exception\InvalidArgumentException
	 * @expectedExceptionMessage An invalid URI was provided
	 */
	public function testSetWebsiteUrlThrowsExceptionForInvalidUrl() {
		$options = new AkismetServiceOptions;
		$options->setWebsiteUrl('foo');
	}
	
	/**
	 * @covers ::setWebsiteLanguage
	 * @covers ::getWebsiteLanguage
	 */
	public function testSetGetWebsiteLanguage() {
		$options = new AkismetServiceOptions;
		$this->assertInternalType('string', $options->getWebsiteLanguage(), 'Default language should initially be set to a reasonable value');
		$this->assertSame($options, $options->setWebsiteLanguage('Foo'), 'setWebsiteLanguage() should be fluid');
		$this->assertSame('Foo', $options->getWebsiteLanguage(), 'Unexpected return value');
		$options->setWebsiteLanguage(999);
		$this->assertSame('999', $options->getWebsiteLanguage(), 'setWebsiteLanguage() should cast to a string');
	}
	
	/**
	 * @covers ::setWebsiteCharset
	 * @covers ::getWebsiteCharset
	 */
	public function testSetGetWebsiteCharset() {
		$options = new AkismetServiceOptions;
		$this->assertInternalType('string', $options->getWebsiteCharset(), 'Default charset should initially be set to a reasonable value');
		$this->assertSame($options, $options->setWebsiteCharset('Foo'), 'setWebsiteCharset() should be fluid');
		$this->assertSame('Foo', $options->getWebsiteCharset(), 'Unexpected return value');
		$options->setWebsiteCharset(999);
		$this->assertSame('999', $options->getWebsiteCharset(), 'setWebsiteCharset() should cast to a string');
	}
	
	/**
	 * @covers ::setInvalidParamsThrowsException
	 * @covers ::getInvalidParamsThrowsException
	 * @covers ::throwExceptionForInvalidParams
	 */
	public function testSetInvalidParamThrowsException() {
		$options = new AkismetServiceOptions;
		$this->assertInternalType('bool', $options->getInvalidParamsThrowsException(), 'Exception flag should intially be a bool');
		$this->assertSame($options, $options->setInvalidParamsThrowsException(true), 'setInvalidParamsThrowsException() should be fluid');
		$this->assertTrue($options->getInvalidParamsThrowsException());
		$this->assertTrue($options->throwExceptionForInvalidParams());
		$options->setInvalidParamsThrowsException(false);
		$this->assertFalse($options->getInvalidParamsThrowsException());
		$this->assertFalse($options->throwExceptionForInvalidParams());
	}
	
}
