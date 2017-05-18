<?php

function smarty_function_brave_array2hidden($params, &$smarty) {
	$array = $params['value'];

    return array2hidden($array);
}

function array2hidden($array,$fatherkey = '') {
	$str = '';
	if(!$array) {
		return '';
	}
	
	foreach($array as $key => $val) {
		if(is_array($val)) {
			$key = $fatherkey ? $fatherkey . '[' . $key . ']' : $key;
			$str .= array2hidden($val,$key);
		} else {
			if($fatherkey) {
				$str .= '<input type="hidden" name="' . $fatherkey . '[' . $key . ']" value="' . $val . '" />' . "\r\n";
			} else {
				$str .= '<input type="hidden" name="' . $key . '" value="' . $val . '" />' . "\r\n";
			}
			
		}
	}
	
	return $str;
}

?>
