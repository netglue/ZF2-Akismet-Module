# ZF2 Akismet Service Module

## Intro

A straightforward ZF2 module that provides a service for using the Akismet API and a handy validator you can attach to your form elements.

## Current Status

Mostly tested with exception of submitting ham and spam, though there's no reason why these wouldn't work fine as far as I can see.

## Install

Install via composer with `netglue/zf2-akismet-module` then enable in your `application.config.php` with the module name `NetglueAkismet`

Add your Akismet ApiKey to local autoloaded config like this:

	return array(
		'netglue_akismet' => array(
			'apiKey' => 'YourApiKey',
		),
	);

There are more options to setup defaults. Look at `config/module.config.php` for a full list.

## Akismet Service

The Akismet service, once the module is loaded should be accessible from the main service locator with the name `'NetglueAkismet\Service\AkismetService'`. It also has a shorter alias `'AkismetService'`

Basic usage of the service goes like this:
	
	$service = $this->getServiceLocator()->get('AkismetService');
	$result = $service->isSpam($contentToCheck, $authorEmail, $commentType, $additionalParams);

Not all of the parameters are required and all of them can be passed into the `$additionalParams` array making sure that the keys match up with whatever is expected by the API. You can find docs for it here: [comment-check docs](https://akismet.com/development/api/#comment-check)

## Validator

The validator is registered through the `ValidatorManager` by the Module as it depends on a service instance. Providing your form elements are created using the form element manager/factory stuff the validator should be ready to go.

For example, set up your input filter config like this:

	public function getInputFilterSpecification() {
		return array(
			'myNameFormElement' => array(
				'required' => true,
				'filters' => array(
					array('name' => 'Zend\Filter\StringTrim'),
				),
			),
			'emailFormElement' => array(
				'required' => true,
				'filters' => array(
					array('name' => 'Zend\Filter\StringToLower'),
					array('name' => 'Zend\Filter\StringTrim'),
				),
			),
			'comment' => array(
				'required' => true,
				'filters' => array(
					array('name' => 'Zend\Filter\StringTrim'),
				),
				'validators' => array(
					array(
						'name' => 'AkismetValidator', // Or 'NetglueAkismet\Validator\Akismet'
						'options' => array(
							'comment_type' => 'comment', // Or 'contact-form' or something else, or leave it to the service configured default
							'context_map' => array(
								'emailFormElement' => 'comment_author_email',
								'myNameFormElement' => 'comment_author',
							),
						),
					),
				),
			),
		);
	}
	
## Tests

The tests are pretty good I think, but `submitHam` and `submitSpam` aren't tested yet.

To run the tests, CD to the module directory and issue `php composer.phar install` to download all the dependencies; copy `phpunit.xml.dist` to `phpunit.xml`. By default the network tests are disabled. To run those too, comment out or delete the exclude directive in the `phpunit.xml` and make sure you setup a valid api key in `test/config/local.php`.