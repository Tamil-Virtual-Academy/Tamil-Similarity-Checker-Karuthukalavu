<?php

class Base {
	function __construct($args = array()){
		// build all args into their corresponding class properties
		foreach($args as $key => $val) {
			// only accept keys that have explicitly been defined as class member variables
			if(property_exists($this, $key)) {
				$this->{$key} = $val;
			}
		}
	}
}

?>