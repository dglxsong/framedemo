<?php

function smarty_function_brave_html_select_hour($params, &$smarty) {
    $result = '';
    
    if (!isset($params['value'][0])) {
    	if (isset($params['default']) && $params['default'] == 'now') {
    		$params['value'] = date('H');
    	}
    }
    
    $list = null;
    
    for ($i = 0; $i <= 23; $i++) {
        $list[] = $i;
    }
    
    if (isset($params['order']) && $params['order'] == 'desc') {
        $list = array_reverse($list);
    }
        
    foreach ($list as $hour) {
        $selected = ($hour == intval($params['value']))?'selected':'';
        $value = str_pad($hour, 2, '0', STR_PAD_LEFT);
        $result.= '<option value="' . $value . '" ' . $selected . '>';
        $result.= $value;
        $result.= '</option>';
    }
    
    return $result;
}  

?>
