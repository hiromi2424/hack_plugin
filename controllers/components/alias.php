<?php
class AliasComponent{
	function initialize(&$controller, $settings = array()){
		if(!is_array($settings) || empty($settings)){
			return;
		}
		
		$Component =& $controller->Component;
		$loaded =& $Component->_loaded;
		
		foreach($settings as $from => $to){
			$from = Inflector::camelize($from);
			$to   = Inflector::camelize($to);
			
			list($plugin, $name) = pluginSplit($to);
			$class = $name . 'Component';
			
			if(!class_exists($class)){
				if(!App::import('Component', $to)){
					continue;
				}
			}
			$object = null;
			if(is_subclass_of($class, 'SessionComponent')){
				$object = new $class($controller->base);
			}else{
				$object = new $class;
			}
			// component needs to have "enabled" parameter
			$object->enabled = isset($loaded[$from]) ? $loaded[$from]->enabled : true;
			$Component->_loadComponents($object, $name);
			
			if(method_exists($object, 'initialize')){
				//See current method timing to prevent the component from being initialized twice.
				$order = array_flip(array_keys($loaded));
				$will_be_initialized =
					isset($order[$from]) &&
					($order['Alias'] < $order[$from])
				;
				
				if(!$will_be_initialized){
					// Get original settings
					$options = array();
					if(isset($Component->__settings[$from])){
						$options = $Component->__settings[$from];
					}
					$object->initialize($controller, $options);
				}
			}
			
			// must not do =&
			$loaded[$from] = $object;
		}
	}
}
