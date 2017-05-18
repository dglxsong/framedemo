<?php

class BraveUpload extends Brave {

    var $ext = array();
    var $size = 102400000;
    var $base = APP_UPLOAD;
    var $filter = array('php');
    var $error = 'error_msg';
    var $info = '_info';
    var $mode = 0777;

    function upload($config) {
       
        if (!$files = $this->files()) {
            return false;
        }

        foreach ($files as $k => $v) {
            if (isset($v['name']))  {
                if (strlen($v['name'])) {
                    $key = $k;
                    $param = $this->param($config, $key);
                    $this->valid($param, $v);
                    $this->handle($param, $v);
                    $_POST[$k] = $v['save'];
                    $_POST[$k . $this->info] = $v;
                    continue;
                }
                else {
                    $_POST[$k] = '';
                }
            }
            
            foreach ($v as $k1 => $v1) {
                $key = "{$k}[{$k1}]";
                $param = $this->param($config, $key);

                if (isset($v1['name'])) {
                    if (strlen($v1['name'])) {
                        $this->valid($param, $v1);
                        $this->handle($param, $v1);
                        $_POST[$k][$k1] = $v1['save'];
                        $_POST[$k][$k1 . $this->info] = $v1;
                        continue;
                    }
                    else {
                        $_POST[$k][$k1] = '';
                    }
                }
                
                foreach ($v1 as $k2 => $v2) {
                    if (isset($v2['name'])) {
                        if (strlen($v2['name'])) {
                            $this->valid($param, $v2);
                            $this->handle($param, $v2);
                            $_POST[$k][$k1][$k2] = $v2['save'];
                            $_POST[$k][$k1][$k2 . $this->info] = $v2;
                        }
                        else {
                            $_POST[$k][$k1][$k2] = '';
                        }
                    }
                }
            }
        }

        return true;
    }
    
    function param($config, $key) {
        $param = null;
        
        foreach ($config as $k => $v) {
            if ($key == $k) {
                return $v;
            }

            if (preg_match('/^\/\^(.*?)\$\/([is]*)$/i', $k) && preg_match($k, $key)) {
                return $v;
            }
        }
        
        return $param;
    }
    
    function valid($param, &$file) {
        if (!$file['tmp_name']) {
            $file[$this->error] = 'file_tmp_null';
        }
        
        $ext = (isset($param['ext']) && is_array($param['ext']))? $param['ext']: $this->ext;
        if (!$this->extValid($ext, $file)) {
            $file[$this->error] = 'file_ext_invalid';
        }
        
        $size = (isset($param['size']) && $param['size'])? $param['size']: $this->size;
        if (!$this->sizeValid($size, $file)) {
            $file[$this->error] = 'file_size_invalid';
        }
        
        $filter = (isset($param['filter']) && is_array($param['filter']))? $param['filter']: $this->filter;
        if (!$this->filterValid($filter, $file)) {
            $file[$this->error] = 'file_ext_filter';
        }
        
        return isset($file[$this->error])? false: true;
    }
    
    function handle($param, &$file) {
        if (isset($file[$this->error])) {
            return false;
        }
        
        $base = $file['base'] = (isset($param['base']) && strlen($param['base']))? $param['base']: $this->base;
        $save = $this->getSave($file);
        $new = $base . $save;
        
        $system = $this->load(EXTEND, 'BraveSystem');
        $system->mkdirs(dirname($new));
        
        $this->log('new is');
        $this->log($new);
        $this->log('tmp_name is');
        $this->log($file['tmp_name']);
        if (!move_uploaded_file($file['tmp_name'], $new)) {
            $file[$this->error] = 'file_move_failure';
        }
        else {
            chmod($new, $this->mode);
            // pdf to test
            $ext = $this->getExt($new);
            
            if (strtolower($ext) == 'pdf') {
                $txtfile = preg_replace('/^(.*?)\.pdf$/i', '$1.txt', $new);
                $cmd = "pdftotext {$new} {$txtfile}" and exec($cmd);
            }
            
        }
    }
    
    function getExt($fileName) {
        $system = $this->load(EXTEND, 'BraveSystem');
        return $system->fileExt($fileName);
    }

    function extValid($ext, &$file) {
        $fileExt = $this->getExt($file['name']);
        
        if (empty($ext)) {
            return true;
        }
        else if (in_array($fileExt, $ext)) {
            return true;
        }
        else {
            return false;
        }
    }
    
    function filterValid($filter, $file) {
        $fileExt = $this->getExt($file['name']);

        if (empty($filter)) {
            return true;
        }
        else if (in_array($fileExt, $filter)) {
            return false;
        }
        else {
            return true;
        }
    }
    
    function sizeValid($size, $file) {
        return ($size > $file['size'])? true: false;
    }
    
    function getSave(&$file) {
        $dir = date('Ymd');
        $name = md5(date('His', time()) . uniqid(rand(1000,9999), true));
        $ext = $this->getExt($file['name']);
        $file['ext'] = $ext;
        $file['file'] = $name . '.' . $ext;
        $file['save'] = $dir . '/' . $file['file'];
        return str_replace('/', DS, $file['save']);
    }
}

?>
