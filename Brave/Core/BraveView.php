<?php

/**
* @desc Smarty
*/
define('SMARTY_COMPILE', APP_CACHE . "Smarty" . DS . "compile");
define('SMARTY_CACHE', APP_CACHE . "Smarty" . DS . "cache");

class BraveView extends Brave {

    var $smarty = null;
    var $data = null;
    var $leftDelimiter = '<{';
    var $rightDelimiter = '}>';
    var $tail = '.html';
    var $page = array();
    var $js = array();
    var $css = array();
    var $menu = array();
    var $login = array();

    function BraveView() {
        $file = LIBRARY . 'Smarty' . DS . 'Smarty.class.php';
        $smarty = $this->newObject($file, 'Smarty');
        $smarty->compile_dir = SMARTY_COMPILE;
        $smarty->cache_dir = SMARTY_CACHE;
        $smarty->left_delimiter = $this->leftDelimiter;
        $smarty->right_delimiter = $this->rightDelimiter;
        $this->smarty = $smarty;
        
        global $page, $menu,$code;
        $this->page = $page;
        $this->menu = $code['menu'];
    }
    
    function setData($data, $key = null) {
        if (is_null($key))
            $this->data = $data;
        else
            $this->setValue($this->data, $key, $data);
    }

    function assign($key, $data) {
        $this->smarty->assign($key, $data);
    }
    
	function assignByRef($key, $data) {
        $this->smarty->assign_by_ref($key, $data);
    }

    function autoAssign($data) {
        if (is_array($data) && !empty($data)) {
            foreach ($data as $k => $v) {
                $this->smarty->assign($k, $v);
            }
            
            return true;
        }
        else {
            return false;
        }
    }
    
    function format($tpl = null) {
        $dispatcher = $this->dispatcher();
        $controllerName = $dispatcher->controllerName;
        $actionName = $dispatcher->actionName;
        $this->smarty->template_dir = APP_TEMPLATE . ucfirst($controllerName);

        if (strlen($tpl) == 0) {
            $tpl = $actionName; 
        }

        if (preg_match('/[\.]+/', $tpl)) {
            $tpl = str_replace('.', DS, $tpl);
            $tpl.= $this->tail;
        } else {
            $tpl = ucfirst($controllerName) . DS . $tpl;
            $tpl.= $this->tail;
        }

        if (file_exists(APP_TEMPLATE . $tpl))
            $tpl = APP_TEMPLATE . $tpl;
        else
            $tpl = TEMPLATE . $tpl;
            
        return $tpl;
    }
    
    function fetch($tpl = null) {
        $tpl = $this->format($tpl);
        $this->assign('data', $this->data);
        return $this->smarty->fetch($tpl);
    }

    function display($tpl = null) {
        $tpl = $this->format($tpl);
        $this->assign('page', $this->page);
        $this->assign('css', $this->css);
        $this->assign('js', $this->js);
        $this->assign('data', $this->data);
        $this->smarty->display($tpl);
    }
    
    function menu() {
        $menu = $this->menu;
        $dispatcher = $this->dispatcher();
        $BraveAct = $this->load(CORE, 'BraveAct');
        
        foreach ($menu as $c => $m) {
            if (!isset($m['sub'])) {
                unset($menu[$c]);
                continue;
            }
            
            foreach ($m['sub'] as $a => $s) {
                $config = array(
                    $dispatcher->controllerAccessor => $c,
                    $dispatcher->actionAccessor => $a,
                );

                if (!$BraveAct->valid($config, true)) {
                    unset($m['sub'][$a]);
                }
            }
            
            if (empty($m['sub'])) {
                unset($menu[$c]);
                continue;
            }
            
            $menu[$c] = $m;
        }

        return $menu;
    }
    
    function js($src) {
        if (is_array($src) && $src) {
            $this->js = array_merge($this->js, $src);
        }
        else if (strlen($src)) {
            $this->js[] = $src;
        }
    }
    
    function css($src, $media = 'all') {
        if (is_array($src) && $src) {
            $this->css[$media] = array_merge($this->css, $src);
        }
        else if (strlen($src)) {
            $this->css[$media][] = $src;
        }
    }
}

?>
