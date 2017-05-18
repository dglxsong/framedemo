<?php

class BraveTranslator extends Brave {
	
	/**
	 * @desc translate string to array or array to string
	 * @param $data
	 * @param $transField
	 */
    function translate(&$data,$transField = array()) {
    	if (!$transField) {
    		return false;
    	}
    	
    	foreach ($transField as $key => $val) {
    		if (!$data[$key]) {
    			continue;
    		}
    		
    		$func = $val[0];
    		$args = $val[1];
    		if (method_exists($this,$func)) {
    			$data[$key] = $this->$func($data[$key],$args);
    		}
    		elseif (function_exists($func)) {
    			$data[$key] = $func($data[$key],$args);
    		} 
    		else {
    			continue;
    		}
    	}
    }
    
	function arrayString($value,$args = array()) {
    	$sep = $args['sep'];
    	if (is_array($value)) {
    		return implode($sep,array_filter($value));
    	}
    	else {
    		return explode($sep,$value);
    	}
    }
}

?>
