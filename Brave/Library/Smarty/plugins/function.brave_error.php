<?php

function smarty_function_brave_error($params, &$smarty) {
    $error = '';
    $tag = $params['tag'] ? $params['tag'] : 'p';
    $data = $params['data'];
    
    if (isset($data[0])) {
        foreach ((array)$data as $v) {
            $error.= '<' . $tag . ' class="error"' . '>' . $v . '</' . $tag . '>';
        }
    }
    
    return $error;
}  

?>
