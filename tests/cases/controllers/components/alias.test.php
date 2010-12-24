<?php

App::import('Controller', array('Component', 'Controller'), false);

class AliasComponentTestController extends Controller {
	var $name = 'AliasComponentTest';
	var $components = array(
		'Cookie',
		'HackPlugin.Alias' => array(
			'Cookie' => 'ExtendedCookie',
		),
	);
	var $uses = null;
}

App::import('Component', 'Cookie');
class ExtendedCookieComponent extends CookieComponent {
	
}

class AliasTestCase extends CakeTestCase {
	var $Controller = null;

	function startTest() {
		$this->_reset();
	}

	function _reset($components = null, $unsets = null) {
		unset($this->Controller);
		$this->Controller = new AliasComponentTestController();

		if ($components !== null) {
			$this->Controller->components = Set::merge($this->Controller->components, $components);
		}
		if ($unsets !== null) {
			$components = Set::normalize($this->Controller->components);
			$this->Controller->components = Set::diff($components, array_flip((array)$unsets));
		}

		$this->Controller->constructClasses();
		$this->Controller->startupProcess();
	}

	function endTest() {
		unset($this->Controller);
	}

	function testOverride() {
		$this->assertEqual(get_class($this->Controller->Cookie), 'ExtendedCookieComponent');
	}

	function testAppend() {
		$this->_reset(null, 'Cookie');
		$this->assertEqual(get_class($this->Controller->Cookie), 'ExtendedCookieComponent');
	}
}
