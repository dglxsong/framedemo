<?php

class BraveLogger extends Brave {

    var $postfix = '.log';
    var $mode = 0777;
    var $break = PHP_EOL;

    function mkdir() {
        // System
        $system = $this->load(EXTEND, 'BraveSystem');
        
        // dir
        $today = date('Y-m-d');
        $dir = APP_LOG . $today . DS;
        
        if (!$system->mkdirs($dir, $this->mode)) {
            trigger_error("Can not mkdir: {$dir}", E_USER_WARNING);
        }
        
        return $dir;
    }

    function logger($type, $log) {
        // file
        $dir = $this->mkdir();
        $file = $dir . $type . $this->postfix;
        
        if (!is_file($file) && $fp = fopen($file, 'a')) {
            fclose($fp);
            chmod($file, $this->mode);
        }
        
        if (!$fp = fopen($file, 'a')) {
            trigger_error("Can not mkfile: {$file}", E_USER_WARNING);
            exit;
        }
        
        // prefix
        $prefix = '[' . date('Y-m-d H:i:s') . ']' . $this->break;
        
        // log
        flock($fp, LOCK_EX);
        
        fwrite($fp, $prefix);
        fwrite($fp, $log);
        fwrite($fp, $this->break . $this->break);
        
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

?>