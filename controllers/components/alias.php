<?php
class AliasComponent{
	var $Controller;
	var $__primary;

	function initialize($controller, $settings = array()){
		$this->Controller = $controller;

		if (!is_array($settings) || empty($settings)) {
			return;
		}

		$this->__primary = true;
		$this->loadComponents($settings, false);
		$this->__primary = false;
	}

	function _loadComponent($from, $to, $settings, $startup = true) {
		$Component =& $this->Controller->Component;
		$loaded =& $Component->_loaded;

		$from = Inflector::camelize($from);
		$to   = Inflector::camelize($to);

		list($plugin, $name) = pluginSplit($to);
		$class = $name . 'Component';

		if (!class_exists($class)) {
			if (!App::import('Component', $to)) {
				return false;
			}
		}

		if ($class === 'SessionComponent' || is_subclass_of($class, 'SessionComponent')) {
			$object = new $class($this->Controller->base);
		} else {
			$object = new $class;
		}
		// component needs to have "enabled" parameter
		$object->enabled = isset($loaded[$from]) ? $loaded[$from]->enabled : true;
		if (!isset($object->components)) {
			$object->components = array();
		}

		$previouslyLoaded = array_keys($loaded);
		$Component->_loadComponents($object, $name);
		// initialize sub componets that has not been loaded within controller
		$newSubComponents = array_diff(array_keys($loaded), $previouslyLoaded);
		if (!empty($newSubComponents)) {
			foreach ($newSubComponents as $newSubComponent) {
				$NewSubComponent =& $loaded[$newSubComponent];
				if ($NewSubComponent->enabled && method_exists($NewSubComponent, 'initialize')) {
					$settings = isset($Component->__settings[$newSubComponent]) ? $Component->__settings[$newSubComponent] : array();
					$NewSubComponent->initialize($this->Controller, $settings);
				}
			}
		}

		if (method_exists($object, 'initialize')) {
			//See current method timing to prevent the component from being initialized twice.
			$order = array_flip(array_keys($loaded));
			$will_be_initialized =
				isset($order[$from]) &&
				($order['Alias'] < $order[$from])
			;

			if ($startup || !$will_be_initialized) {
				// Get original settings
				if (isset($Component->__settings[$from])) {
					$settings = Set::merge($Component->__settings[$from], $settings);
				}
				$Component->__settings[$from] = $settings;
				$object->initialize($this->Controller, $settings);
			}
		}

		if (!$this->__primary && $startup && method_exists($object, 'startup')) {
			$object->startup($this->Controller);
		}

		// must not do =&
		$loaded[$from] = $object;

		if ($this->__primary && !isset($this->Controller->$from) && !in_array($from, $Component->_primary)) {
			$Component->_primary[] = $from;
		}

		if (!isset($this->Controller->$from) || get_class($this->Controller->$from) !== $class) {
			$this->Controller->$from = $loaded[$from];
		}
		return true;
	}

	function loadComponent($component, $settings = array(), $startup = true) {
		return $this->_loadComponent($component, $component, $settings, $startup);
	}

	function loadComponents($components, $startupDefault = true) {
		foreach ($components as $from => $alias) {
			list($to, $settings) = each(Set::normalize($alias));
			if (empty($settings)) {
				$settings = array();
			}

			$startup = $startupDefault;
			if (!$this->__primary && isset($settings['startup'])) {
				$startup = $settings['startup'];
				unset($settings['startup']);
			}
			$this->_loadComponent($from, $to, $settings, $startup);
		}
	}

	function loadAlias($from, $to, $settings = array(), $startup = true) {
		return $this->_loadComponent($from, $to, $settings, $startup);
	}
}
