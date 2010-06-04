<?php
class AliasHelper{
	var $unset = true;
	var $settings = array();
	var $view;
	
	function beforeRender(){
		if(is_object($this->view)){
			foreach($this->settings as $from => $to){
				$this->_replace($from, $to);
			}
		}
		parent::beforeRender();
	}
	
	function __construct($settings = array()){
		$this->view =& ClassRegistry::getObject('view');
		$this->settings = array_merge($this->settings, $settings);
	}
	
	function _replace($from, $to){
		$unset = $this->unset;
		if(is_array($to)){
			extract($to);
		}
		
		$from = Inflector::variable($from);
		$to   = Inflector::variable($to);
		
		$this->view->_loadHelpers($this->view->_loaded, array($to));
		
		list($plugin, $to) = pluginSplit($to);
		
		if(isset($this->view->loaded[$from]) && isset($this->view->loaded[$to])){
			$this->view->loaded[$from] = $this->view->loaded[$to];
			if($unset){
				unset($this->view->loaded[$to]);
			}
			return true;
		}
		return false;
	}
}
