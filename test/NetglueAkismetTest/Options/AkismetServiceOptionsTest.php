<?php

namespace NetglueAkismetTest\Options;

use PHPUnit_Framework_TestCase;

use NetglueAkismet\Options\AkismetServiceOptions;

use Zend\StdLib\AbstractOptions;

class Foo extends AbstractOptions{}

class AkismetServiceOptionsTest extends PHPUnit_Framework_TestCase {
	
	public function testNothing() {
		$opt = new AkismetServiceOptions;
	}
	
}
