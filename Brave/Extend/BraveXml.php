<?php

class BraveXml extends Brave {
	
	function getKey($key = '') {
    	if(is_numeric($key)) {
    		return 'item';
    	} elseif(preg_match("/^__([\w]+)__[\d]+$/",$key,$match)){
    		return $match[1];
    	} else {
    		return $key;
    	}
    }
    
 	function arrayToXml($data,$rootTag = 'rootnode', $headAdded = false) {
    	$xml = '';
    	if(!$headAdded) {
    		$xml = '<?xml version="1.0" encoding="UTF-8"?><' . $rootTag . '>';
    	}
    	if($data && is_array($data)) {
    		foreach($data as $key => $val) {  
    			$_key = $this->getKey($key);		
    			if(is_array($val)) {
    				$xml .=  '<' . $_key . '>' . $this->arrayToXml($val,$rootTag,true) . '</' . $_key . '>';
    			} else {
    				$xml .= '<' . $_key . '>' . htmlspecialchars($val) . '</' . $_key . '>';
    			}
    		}
    	}
    	if(!$headAdded) {
    		$xml .= '</' . $rootTag . '>';
    	}
    	return $xml;
    }
    
    function xmlToArray($xml = '',$isFile = false) {
    	$return = array();
    	if (($isFile && !file_exists($xml)) || (!$isFile && !$xml)) {
    		return $return;
    	}
    	
    	if ($isFile) {
    		$xmlObject = simplexml_load_file($xml);
    	} else {
    		$xmlObject = simplexml_load_string($xml);
    	}
    	if (!$xmlObject) {
    		return $return;
    	}
    	
    	if ((float)PHP_VERSION < 5.3) {
    		$str = serialize($xmlObject);
			$str = str_replace('O:16:"SimpleXMLElement"', 'a', $str);
			$return = unserialize($str);
    	} else {
    		$return = $this->parseXmlObject($xmlObject);
    	}
		
		return $return;
    }
    
	function parseXmlObject($xmlObject) {
		$array = array();
		
		foreach($xmlObject->children() as $key => $child) {
			if(count($child->children())) {	
				$tmp = $this->parseXmlObject($child);
				if (is_array($tmp) && count($child->attributes())) {
					foreach($child->attributes() as $k => $v) {
						$tmp['@attributes'][$k] = (string)$v;
					}
				}
				
				if(count($xmlObject->{$key}) > 1) {
					$array[$key][] = $tmp;
				} else {
					$array[$key] = $tmp;
				}
			} else {
				$tmp = (string)$child;
				if (!$tmp && count($child->attributes())) {
					foreach($child->attributes() as $k => $v) {
						$tmp['@attributes'][$k] = (string)$v;
					}
				}
				
				if(count($xmlObject->{$key}) > 1) {
					$array[$key][] = $tmp;
				} else {
					$array[$key] = $tmp;
				}
			}
		}
		
		return  $array;
	}
}