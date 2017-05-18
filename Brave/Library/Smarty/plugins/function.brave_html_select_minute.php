<?php

function smarty_function_brave_html_select_minute($params, &$smarty) {
    $result = '';
    
    if (!isset($params['value'][0])) {
        $params['value'] = date('i');
    }
    
    $list = null;
    
    for ($i = 0; $i <= 59; $i++) {
        $list[] = $i;
    }
    
    if (isset($params['order']) && $params['order'] == 'desc') {
        $list = array_reverse($list);
    }
        
    foreach ($list as $minute) {
        $selected = ($minute == intval($params['value']))?'selected':'';
        $value = str_pad($minute, 2, '0', STR_PAD_LEFT);
        $result.= '<option value="' . $value . '" ' . $selected . '>';
        $result.= $value;
        $result.= '</option>';
    }
    
    return $result;
}  

?>
