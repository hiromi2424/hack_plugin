<?php

App::import('Helper', 'Hack.Alias');

class AliasHelperTestCase extends CakeTestCase {
	function startTest() {
		$this->Helper = new AliasHelper;
	}

	function endTest() {
		unset($this->Helper);
	}
}
