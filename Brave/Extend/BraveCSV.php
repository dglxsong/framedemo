<?php

class BraveCSV extends Brave {
    
    var $data = null;
    var $index = null;
    var $code = 'utf-8';
    var $break = PHP_EOL;
    var $delimiter = ',';
    var $enclosure = '"';
    var $encode = 'sjis-win';
    var $output = true;
    
    function setIndex($index) {
        $this->index = $index;
        $this->data = null;
    }
    
    function setEncode($encode) {
        $this->encode = $encode;
    }
    
    function setData($line) {
        $this->data[] = $line;
    }
    
    function outIndex() {
        $data = null;
        foreach ($this->index as $k => $v) {$data[$k] = $v['name'];}
        return $this->output($data);
    }
    
    function output($line = null) {
        if (!$this->index || !$line)
            return false;
        else
            $data = array();

        if (is_null($line)) {
            foreach ($this->data as $line) {
                $this->output($line);
            }
        }
        else {
            foreach ($this->index as $k => $n) {
                $data[$k] = isset($line[$k])? $line[$k]: null; 
            }
            
            $data = $this->encode($data);
            $output = $this->put($data);
            if ($this->output) {
            	echo $output . $this->break;
            } else {
            	return $output . $this->break;
            }
        }
    }
    
    function encode($line) {
        foreach ($line as $k => $v) {
            if (strlen($v)) {
                $line[$k] = mb_convert_encoding($v, $this->encode, $this->code);
            }
        }
        
        return $line;
    }
    
    function put($line) {
        $implode = $this->enclosure . $this->delimiter . $this->enclosure;
        $output = implode($implode, $line);
        $output = $this->enclosure . $output . $this->enclosure;
        return $output;
    }

    function outHeader($fileName) {
        $expire = 180;
        header("Pragma: public");
        header("Cache-control: max-age=".$expire);
        //header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Expires: " . gmdate("D, d M Y H:i:s",time()+$expire) . "GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . "GMT");
        header("Content-Disposition: attachment; filename=\"".$fileName."\"");
        header("Content-type: application/x-download");
        header("Content-Encoding: none");
        header("Content-Transfer-Encoding: binary");
    }
}  

?>
