<?php

function smarty_function_brave_html_select_date($params, &$smarty) {
    $result = '';
    
    if (isset($params['default'])) {
        if ($params['default'] === 'blank') {
            $result.= '<option value=""></option>';
        }
    }

    if (!isset($params['value'][0]) || !intval($params['value'])) {
        // default
        if (isset($params['default'])) {
            $params['value'] = $params['default'];
        }
        else {
            $params['value'] = date('d');
        }
    }
    
    $list = null;
    
    for ($i = 1; $i <= 31; $i++) {
        $list[] = $i;
    }
    
    if (isset($params['order']) && $params['order'] == 'desc') {
        $list = array_reverse($list);
    }
        
    foreach ($list as $date) {
        $selected = ($date == $params['value'])?'selected':'';
        
        $result.= '<option value="' . str_pad($date, 2, '0', STR_PAD_LEFT) . '" ' . $selected . '>';
        $result.= str_pad($date, 2, '0', STR_PAD_LEFT);
        $result.= '</option>';
    }
    
    return $result;
}  

?>
