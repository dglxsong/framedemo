<?php

function smarty_function_brave_uri_replace($params, &$smarty) {
    $uri = $_SERVER['REQUEST_URI'];
    
    if (empty($params)) {
        return $uri;
    }
    
    if (isset($params['array']) && strlen($params['array']) > 0) {
        $array = $params['array'];
        unset($params['array']);
    }
    
    foreach ($params as $k => $v) {
        if (strlen($array) > 0)
            $name = "{$array}[{$k}]";
        else
            $name = $k;
        
        if (strpos($uri, '?')) {
            $uri.= '&';
            $regex = "/([\?\&]+)" . preg_quote($name, '/') . "\=(.*?)&/is";
            
            if (preg_match($regex, $uri)) {
                $uri = preg_replace($regex, '$1' . "{$name}={$v}&", $uri);
                $uri = preg_replace('/^(.*?)[\&]+$/', '$1', $uri);
            }
            else {
                $uri.= "{$name}={$v}";
            }
        }
        else {
            $uri.= "?{$name}={$v}";
        }
    }
    
    return $uri;
}  

?>
