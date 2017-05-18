<?php

function smarty_function_brave_array_format($params, &$smarty) {
    $return = '';
	$array = $params['value'];
	$sep = $params['sep'] ? $params['sep'] : '-';
	$encfield = $params['encfield'] ? explode(",",$params['encfield']) : null;
	
	if(is_array($array)) {
		$empty = array_count_values($array);
		if($empty[null] == count($array)) {
    		$return = '';
    	} else {
			if(!$encfield) {
    			$return = implode($sep,$array);
			} else {
				$i = 1;
				foreach($array as $val) {
					if(in_array($i,$encfield)) {
						$return .= str_repeat('＊',strlen($val)) . $sep;
					}
					else {
						$return .= $val . $sep;
					}
					$i++;
				}

				$return = rtrim($return,$sep);
			}
    	}
	} else {
		$return = $array;
	}
	return $return;
}  

?>
