<?php

function smarty_function_brave_html_select_year($params, &$smarty) {
    $result = '';
    
    if (!isset($params['begin']) || $params['begin'] === 'now') {
        $params['begin'] = date('Y');
    }
    
    if (!isset($params['count'])) {
        $params['count'] = 0;
    }
    
    if (isset($params['default'])) {
        if ($params['default'] === 'blank') {
            $result.= '<option value=""></option>';
        }
    }

    // value
    if (!isset($params['value'][0]) || !intval($params['value'])) {
        // default
        if (isset($params['default'])) {
            $params['value'] = $params['default'];
        }
        else {
            $params['value'] = date('Y');
        }
    }
    
    $list = null;

    for ($i = 0; $i <= $params['count']; $i++) {
        $year = $params['begin'] + $i;
        $list[] = $year;
    }
    if (isset($params['order']) && $params['order'] == 'desc') {
        $list = array_reverse($list);
    }
        
    foreach ($list as $year) {
        $selected = ($year == $params['value'])?'selected':'';
        
        $result.= '<option value="' . $year . '" ' . $selected . '>';
        $result.= $year;
        $result.= '</option>';
    }

    return $result;
}  

?>
