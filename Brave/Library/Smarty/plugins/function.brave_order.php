<?php

function smarty_function_brave_order($params, &$smarty) {
    $class = 'sort';
    $uri = $_SERVER['REQUEST_URI'];
    $sort = 'desc';
    $match = null;
    $active = false;

    if (!isset($params['code']) || empty($params['code']))
        return $uri;
    else
        $code = $params['code'];

    if (!isset($params['key']) || !isset($code[$params['key']])) {
        return $uri;
    }
    else {
        $key = $params['key'];
        $config = $code[$params['key']];
    }

    if (!isset($params['name']) || !isset($config[$params['name']]))
        return $uri;
    else
        $field = $config[$params['name']];
        
    if (isset($params['array']) && strlen($params['array'])) {
        $array = $params['array'];
    }
    
    if (strlen($array) > 0)
        $name = "{$array}[order]";
    else
        $name = 'order';
        
    if (strpos($uri, '?')) {
        $uri.= '&';
        $regex = "/([\?\&]+)" . preg_quote($name, '/') . "\=([0-9a-z\_\-])\.(asc|desc)&/s";
        
        if (preg_match($regex, $uri, $match)) {
            if ($match[2] == $key) {$active = true;}
            if ($match[3] == 'desc') {$sort = 'asc';}
            $uri = preg_replace($regex, '$1' . "{$name}={$key}.{$sort}&", $uri);
            $uri = preg_replace('/^(.*?)[\&]+$/', '$1', $uri);
        }
        else {
            $uri.= "{$name}={$key}.{$sort}";
        }
    }
    else {
        $uri.= "?{$name}={$key}.{$sort}";
    }
    
    if ($active) {
        $class = $match[3];
    }

    $txt = isset($params['txt'])? $params['txt']: '';
    $html = '><a href="javascript:void(0)" class="' . $class . '" onclick="order(\'' . $uri . '\')">' . $txt . '</a';
    return $html;
}  

?>
