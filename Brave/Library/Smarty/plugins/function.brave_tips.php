<?php

function smarty_function_brave_tips($params, &$smarty) {
    $tips = '';
    $data = $params['data'];
    
    if (strlen($data)) {
        $tips = '<img src="./images/tips.png" title="' . $data . '" />';
    }
    
    return $tips;
}  

?>
