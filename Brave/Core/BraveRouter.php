<?php

class BraveRouter extends Brave {
    
    var $base = '/';
    var $uri = null;
    
    function dispatch() {
        global $route;

        // uri
       $uri = $this->get('uri');
        $this->uri.= $this->base . $uri;
        // handle
        if ($route) {
            // customize & automatic
            if ($result = $this->customize($route)) return $result;
            if ($result = $this->automatic($route)) return $result;
        }

        return null;
    }
    
    function customize($route) {
        $result = null;
        
        if (empty($route['rules']))
            return $result;
        else
            $rules = $route['rules'];

        foreach ($rules as $rule) {
            $uri = $rule['uri'];
            $regex = '/^\/\^(.*?)\$\/([is]*)$/i';

            // match
            foreach ($uri as $v) {
                if (preg_match($regex, $v) && preg_match($v, $this->uri, $match)) {
                    $rule['match'] = $match;
                    $result = $rule;
                    break;
                }
                
                if ($v == $this->uri) {
                    $result = $rule;
                    break;
                }
            }

            // http & https
            $host = $this->server('HTTP_HOST');
            $request = $this->server('REQUEST_URI');
            
            if (isset($result['https']) && $result['https']) {
                if (!$this->isHttps()) {
                    $url = "https://{$host}{$request}";
                    $this->redirect($url);
                }
            }
            else {
                if ($this->isHttps()) {
                    $url = "http://{$host}{$request}";
                    $this->redirect($url);
                }
            }

            // return
            if ($result) return $result;
        }
        
        return $result;
    }
    
    function automatic($route) {
        if (!$route['auto'])
            return null;
            
        // split
        $result = array();
        $regex = '/[\/\.]+/';
        $tmp = preg_split($regex, $this->uri);

        // controller & action
        $dispatcher = $this->getGlobal('BraveDispatcher');
        $replace = '';
        
        if (isset($tmp[1][0])) {
            $result['action']['c'] = $tmp[1];
            $replace.= '/' . $tmp[1];
        }
        else {
            $result['action']['c'] = $dispatcher->defaultController;
        }
        
        if (isset($tmp[2][0])) {
            $result['action']['a'] = $tmp[2];
            $replace.= '/' . $tmp[2];
        }
        else {
            $result['action']['a'] = $dispatcher->defaultAction;
        }
            
        // data
        $result['argv'] = str_replace($replace, '', $this->uri);
        return $result;
    }
}

?>
