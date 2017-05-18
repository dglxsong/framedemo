<?php

class BraveSync extends Brave {
    
    var $var = 'sync';
    var $list = null;
    
    function BraveSync() {
        global $domain;
        $this->list = $domain;
    }
    
    function valid($domain) {
        if (empty($this->list)) {
            return false;
        }
        
        foreach ($this->list as $v) {
            if ($domain == $v['name']) {
                return $v;
            }
        }
        
        return false;
    }

    function next($domain) {
        $list = $this->list;
        $next = null;

        if (empty($list) || count($list) <= 1)
            return null;
        else
            $count = count($list);

        foreach ($list as $v) {
            if ($domain === $v['name']) {
                if ($num == ($count - 1))
                    $next = $list[0];
                else
                    $next = $list[$num + 1];
                
                break;
            }

            $num++;
        }
        
        return $next;
    }
    
    function sync($sessid) {
        $uri = $this->server('REQUEST_URI');
        $domain = $this->server('SERVER_NAME');

        if (!$this->valid($domain) || !$next = $this->next($domain)) {
            return false;
        }

        $serial = $this->var . '=' . $sessid;
        $regex1 = '/' . $this->var . '=[a-z0-9]+/i';
        $regex2 = '/[\&]+/';
        $regex3 = '/[\?]+/';

        if (preg_match($regex1, $uri))
            $uri = preg_replace($regex1, $serial, $uri);

        else if (preg_match($regex2, $uri))
            $uri.= "&{$serial}";

        else if (preg_match($regex3, $uri))
            $uri.= "{$serial}";

        else
            $uri.= "?{$serial}";

        $direct = "http://{$next['name']}{$uri}";
        $this->redirect($direct);
    }
    
    function forward() {
        $sessid = $this->get($this->var, null);

        if (strlen($sessid) == 0) {
            session_regenerate_id(true);
            return false;
        }

        if ($sessid != session_id()) {
            $name = ini_get('session.name');
            setcookie($name, $sessid);
        }
        
        $this->sync($sessid);
    }
}

?>
