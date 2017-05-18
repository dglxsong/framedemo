<?php

class BraveController extends Brave {

    var $actionPostfix = 'Action';
    var $data = null;
    var $view = null;

    function hasAction($name) {
        $name.= $this->actionPostfix;
        return method_exists($this, $name);
    }

    function setData($data, $key = null) {
        if (is_null($key))
            $this->data = $data;
        else
            $this->setValue($this->data, $key, $data);

        global $page;
        
        if (!isset($this->data['page'])) {
            $this->data['page'] = $page;    
        }
        
        if ($this->view) {
            $this->view->setData($this->data);
        }
    }

    function execAction($name) {
        $actionName = $name . $this->actionPostfix;
        $this->$actionName();
    }

    function forward($forward, $data = null) {
        $dispatcher = $this->getGlobal('BraveDispatcher');
        $dispatcher->dispatch($forward, $data);
    }

    function isConfirm() {
        return $this->isPost('confirm');
    }
    
    function isComplete() {
        return $this->isPost('complete');
    }
    
    function execJs($js = '') {
    	if (!$js) {
    		return;
    	}
    	
    	$js = '<script type="text/javascript">' . $js . '</script>';
    	echo $js;
    }
    
    
    /**
     * 发送医嘱原始照片到用户中心
     */
    public function sendPicture($url,$file_name,$file_url){
    	$ch = curl_init($url);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	if(!class_exists("CURLFile")){
	    	curl_setopt($ch, CURLOPT_POSTFIELDS,
	    		array(
	    		      'uname'=>$file_name,
	    			  'img_1'=>"@".$file_url,
	    		)
	    	);
    	}else{
	    	curl_setopt($ch, CURLOPT_POSTFIELDS,
	    		array(
	    		      'uname'=>$file_name,
	    			  'img_1'=>new CURLFile($file_url)
	    		)
	    	);
    	}
    	$data = curl_exec($ch);
    	$this->log(print_r($data,true));
    	curl_close($ch);
    }
    
}

?>
