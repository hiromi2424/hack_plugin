<?php

App::import('Controller', array('Component', 'Controller'), false);

class AliasComponentTestController extends Controller {
	var $name = 'AliasComponentTest';
	var $components = array(
		'HackPlugin.Alias' => array(
			'Cookie' => 'ExtendedCookie',
		),
		'Cookie',
	);
	var $uses = null;
}

App::import('Component', 'Cookie');
class ExtendedCookieComponent extends CookieComponent {
	
}

class HackAliasTestComponent extends Object {
	var $hoge;
	var $piyo;
	var $fuga;
	function initialize($controller, $settings = array()) {
		$this->_set($settings);
	}
}

class HackAliasTestOverrideComponent extends HackAliasTestComponent {
	function startup() {
		$this->hoge = 3;
	}
}


class AliasTestCase extends CakeTestCase {
	var $Controller = null;

	function startTest() {
		$this->_reset();
	}

	function _reset($sets = null, $unsets = null) {
		unset($this->Controller);
		$this->Controller = new AliasComponentTestController();

		if ($unsets !== null) {
			if ($unsets === true) {
				$this->Controller->components = array();
			} else {
				$components = Set::normalize($this->Controller->components);
				$this->Controller->components = Set::diff($components, array_flip((array)$unsets));
			}
		}
		if ($sets !== null) {
			$this->Controller->components = Set::merge($this->Controller->components, $sets);
		}
		$this->Controller->constructClasses();
		$this->Controller->Component->initialize($this->Controller);
		$this->Controller->beforeFilter();
	}

	function endTest() {
		unset($this->Controller);
	}

	function testOverride() {
		$this->assertEqual(get_class($this->Controller->Cookie), 'ExtendedCookieComponent');

		$this->_reset(array(
			'HackAliasTest' => array(
				'piyo' => 1,
			),
			'HackPlugin.Alias' => array(
				'HackAliasTest' => array(
					'HackAliasTestOverride' => array(
						'fuga' => 2,
					),
				),
			),
		), true);
		$this->assertEqual(get_class($this->Controller->HackAliasTest), 'HackAliasTestOverrideComponent');
		$this->assertEqual($this->Controller->HackAliasTest->piyo, 1);
		$this->assertEqual($this->Controller->HackAliasTest->fuga, 2);
		$this->assertNull($this->Controller->HackAliasTest->hoge);

		$this->Controller->Component->triggerCallback('startup', $this->Controller);
		$this->assertEqual($this->Controller->HackAliasTest->hoge, 3);
	}

	function testAppend() {
		$this->_reset(null, 'Cookie');
		$this->assertEqual(get_class($this->Controller->Cookie), 'ExtendedCookieComponent');

		$this->_reset(array(
			'HackPlugin.Alias' => array(
				'HackAliasTest' => array(
					'HackAliasTestOverride' => array(
						'fuga' => 2,
					),
				),
			),
		), true);
		$this->assertEqual(get_class($this->Controller->HackAliasTest), 'HackAliasTestOverrideComponent');
		$this->assertEqual($this->Controller->HackAliasTest->fuga, 2);
		$this->assertNull($this->Controller->HackAliasTest->hoge);

		$this->Controller->Component->triggerCallback('startup', $this->Controller);
		$this->assertEqual($this->Controller->HackAliasTest->hoge, 3);
	}

	function testLoadMethods() {
		$this->_reset('HackPlugin.Alias', true);
		$this->assertTrue($this->Controller->Alias->loadComponent('HackAliasTest', array('piyo' => 1)));
		$this->assertEqual(get_class($this->Controller->HackAliasTest), 'HackAliasTestComponent');
		$this->assertEqual($this->Controller->HackAliasTest->piyo, 1);

		$this->assertTrue($this->Controller->Alias->loadAlias('HackAliasTest', 'HackAliasTestOverride', array('fuga' => 2)));
		$this->assertEqual(get_class($this->Controller->HackAliasTest), 'HackAliasTestOverrideComponent');
		$this->assertEqual($this->Controller->HackAliasTest->piyo, 1);
		$this->assertEqual($this->Controller->HackAliasTest->fuga, 2);
		$this->assertEqual($this->Controller->HackAliasTest->hoge, 3);
		$this->assertFalse($this->Controller->Alias->loadComponent('NotDefined'));

		$this->_reset('HackPlugin.Alias', true);
		$this->Controller->Alias->loadComponents(array(
			'HackAliasTest' => array(
				'HackAliasTestOverride' => array(
					'fuga' => 2,
					'startup' => false,
				),
			),
			'Cookie' => 'ExtendedCookie',
		));
		$this->assertEqual(get_class($this->Controller->HackAliasTest), 'HackAliasTestOverrideComponent');
		$this->assertEqual($this->Controller->HackAliasTest->fuga, 2);
		$this->assertNull($this->Controller->HackAliasTest->hoge);
		$this->assertEqual(get_class($this->Controller->Cookie), 'ExtendedCookieComponent');
	}

	function testSessionComponent() {
		$this->_reset('HackPlugin.Alias', true);
		$this->Controller->base = '/base';
		$this->assertTrue($this->Controller->Alias->loadComponent('Session'));
		$this->assertEqual(get_class($this->Controller->Session), 'SessionComponent');
		$this->assertEqual($this->Controller->Session->path, '/base');
	}
}
