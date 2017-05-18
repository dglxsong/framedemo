<?php

class BraveAct extends Brave {
    
    var $controllerAccessor = 'c';
    var $actionAccessor = 'a';
    var $controllerName = '';
    var $actionName = '';
    var $session = null;
    var $ignore = -1;

    function BraveAct() {
        $dispatcher = $this->dispatcher();
        $this->controllerAccessor = $dispatcher->controllerAccessor;
        $this->actionAccessor = $dispatcher->actionAccessor;
        $this->session = $this->getSession(SESSION_ROLE);
    }
    
    function valid($config, $return = false) {
        global $act;
        $this->controllerName = $this->getValue($config, $this->controllerAccessor);
        $this->actionName = $this->getValue($config, $this->actionAccessor);

        if (isset($act[$this->controllerName])) {
            $rule = $act[$this->controllerName];
            // actions
            if (isset($rule['action'])) {
                $rs = $this->action($rule['action'], $return);

                if (!$this->ignore($rs))
                    return $rs;
            }
            
            // allow
            if (isset($rule['allow'])) {
                if ($this->allow($rule['allow']) === true) {
                    return true;
                }
            }
            
            // deny
            if (isset($rule['deny'])) {
                $rs = $this->deny($rule['deny']);

                if (!$this->ignore($rs)) {
                    if ($rs && !$return) 
                        return $this->direct($rule);
                    else 
                        return false;
                }
            }
        }

        return true;
    }
    
    function direct($rule) {
        $uri = $this->server('REQUEST_URI');
        $this->setSession('act_referer', $uri);
        if (isset($rule['direct'])) {
            $this->redirect($rule['direct']);
            return true;
        }
        else {
            return false;
        }
    }
    
    function ignore($rs) {
        return ($rs === $this->ignore)? true: false;
    }
    
    function match($role) {
        if ($role === ACT_NO_ROLE) {
            return strlen($this->session)? false: true;
        }

        if ($role === ACT_HAS_ROLE) {
            return strlen($this->session)? true: false;
        }
        
        if ($role === ACT_EVERYONE) {
            return true;
        }

        if (preg_match('/^\/\^(.*?)\$\/([is]*)$/i', $role)) {
            return preg_match($role, $this->session);
        }
        else {
            return ($role == $this->session)? true: false;
        }
    }
    
    function deny($deny) {
        if (!empty($deny)) {
            foreach ($deny as $v) {
                if ($this->match($v))
                    return true;
            }
        }
        
        return $this->ignore;
    }
    
    function allow($allow) {
        if (!empty($allow)) {        
            foreach ($allow as $v) {
                if ($this->match($v))
                    return true;
            }
        }
        
        return $this->ignore;
    }
    
    function action($action, $return) {
        if (isset($action[$this->actionName])) {
            $rule = $action[$this->actionName];
            
            // allow
            if (isset($rule['allow'])) {
                if ($this->allow($rule['allow']) === true) {
                    return true;
                }
            }
            
            // deny
            if (isset($rule['deny'])) {
                $rs = $this->deny($rule['deny']);

                if (!$this->ignore($rs)) {
                    if ($rs && !$return) 
                        return $this->direct($rule);
                    else 
                        return false;
                }
            }
        }

        return $this->ignore;
    }
}

?>