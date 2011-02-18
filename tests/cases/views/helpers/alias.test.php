<?php

App::import('Helper', 'Html');

class AliasTestHtmlHelper extends HtmlHelper {
}

class AliasHelperTestCase extends CakeTestCase {

	function startTest() {
		$this->View = new View(new Controller);
	}

	function endTest() {
		unset($this->View);
		ClassRegistry::flush();
	}

	function _reset($settings = array()) {
		$this->endTest();
		$this->startTest();
		$this->View->helpers = array('Hack.Alias' => $settings);
		$out = $this->_render();
	}

	function _render() {
		$this->View->_render(LIBS . 'view' . DS . 'pages' . DS . 'home.ctp', array());
	}

	function testReplace() {

		$this->_reset(array(
			'Html' => 'AliasTestHtml',
		));

		$this->assertTrue(isset($this->View->Html));
		$this->assertFalse(isset($this->View->html));
		$this->assertFalse(isset($this->View->AliasTestHtml));
		$this->assertFalse(isset($this->View->loaded['AliasTestHtml']));
		$this->assertFalse(isset($this->View->loaded['aliasTestHtml']));
		$this->assertIsA($this->View->Html, 'AliasTestHtmlHelper');
		$this->assertIsA($this->View->loaded['html'], 'AliasTestHtmlHelper');

		$this->_reset(array(
			'Html' => 'aliasTestHtml',
		));

		$this->assertTrue(isset($this->View->Html));
		$this->assertFalse(isset($this->View->html));
		$this->assertFalse(isset($this->View->AliasTestHtml));
		$this->assertFalse(isset($this->View->aliasTestHtml));

		$this->_reset(array(
			'html' => 'aliasTestHtml',
		));

		$this->assertTrue(isset($this->View->Html));
		$this->assertFalse(isset($this->View->html));
		$this->assertFalse(isset($this->View->AliasTestHtml));
		$this->assertFalse(isset($this->View->aliasTestHtml));

	}

}
