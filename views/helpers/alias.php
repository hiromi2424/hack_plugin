<?php
class AliasHelper extends AppHelper {
	var $unset = true;
	var $settings = array();
	var $view;

	function beforeRender(){
		if (is_object($this->view)) {
			foreach ($this->settings as $from => $alias) {
				$this->_replace($from, $alias);
			}
		}
		parent::beforeRender();
	}

	function __construct($settings = array()){
		$this->view =& ClassRegistry::getObject('view');
		$this->settings = array_merge($this->settings, $settings);
	}

	function _replace($from, $alias){

		list($to, $settings) = each(Set::normalize((array)$alias));

		$loadedHelpers = array();
		$this->view->_loadHelpers($loadedHelpers, array($to => $settings));

		list($plugin, $to) = pluginSplit($to);
		if (!isset($loadedHelpers[$to])) {
			return false;
		}
		$loaded =& $loadedHelpers[$to];

		list($plugin, $from) = pluginSplit($from);
		$key = Inflector::variable($from);
		$from = Inflector::classify($from);

		$this->view->loaded[$key] = $loaded;
		$this->view->$from =& $loaded;

		return true;
	}
}
