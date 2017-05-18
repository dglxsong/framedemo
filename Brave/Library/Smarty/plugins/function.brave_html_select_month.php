<?php

function smarty_function_brave_html_select_month($params, &$smarty) {
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
            $params['value'] = date('m');
        }
    }
    
    $list = null;
    
    for ($i = 1; $i <= 12; $i++) {
        $list[] = $i;
    }
    
    if (isset($params['order']) && $params['order'] == 'desc') {
        $list = array_reverse($list);
    }
        
    foreach ($list as $month) {
        $selected = ($month == $params['value'])?'selected':'';
        
        $result.= '<option value="' . str_pad($month, 2, '0', STR_PAD_LEFT) . '" ' . $selected . '>';
        $result.= str_pad($month, 2, '0', STR_PAD_LEFT);
        $result.= '</option>';
    }
    
    return $result;
}  

?>
