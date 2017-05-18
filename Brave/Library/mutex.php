<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Joey
 * Date: 4/22/11
 * Time: 10:55 AM
 * To change this template use File | Settings | File Templates.
 */
 
class Mutex {
    var $writeablePath = '';
    var $lockName = '';
    var $fileHandle = null;

    public function __construct($lockName, $writeablePath = null){
        $this->lockName = preg_replace('/[^a-z0-9]/', '', $lockName);
        if($writeablePath == null){
            $this->writeablePath = $this->findWriteablePath($lockName);
        } else {
            $this->writeablePath = $writeablePath;
        }
    }

    public function getLock(){
        return flock($this->getFileHandle(), LOCK_EX);
    }

    public function getFileHandle(){
        if($this->fileHandle == null){
            $this->fileHandle = fopen($this->getLockFilePath(), 'c');
        }
        return $this->fileHandle;
    }

    public function releaseLock(){
        $success = flock($this->getFileHandle(), LOCK_UN);
        fclose($this->getFileHandle());
        return $success;
    }

    public function getLockFilePath(){
        return $this->writeablePath . DIRECTORY_SEPARATOR . $this->lockName;
    }

    public function isLocked(){
        $fileHandle = fopen($this->getLockFilePath(), 'c');
        $canLock = flock($fileHandle, LOCK_EX);
        if($canLock){
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
            return false;
        } else {
            fclose($fileHandle);
            return true;
        }
    }

    public function findWriteablePath(){
        $foundPath = false;
        $fileName = tempnam("/tmp", "MUT");
        $path = dirname($fileName);
        if($path == '/') $path = '/tmp';
        unlink($fileName);
        if($fileHandle = fopen($path . DIRECTORY_SEPARATOR . $this->lockName, "c")){
            if(flock($fileHandle, LOCK_EX)){
                flock($fileHandle, LOCK_UN);
                fclose($fileHandle);
                $foundPath = true;
            }
        }
        if(!$foundPath){
            $path = '.';
            if($fileHandle = fopen($path . DIRECTORY_SEPARATOR . $this->lockName, "c")){
                if(flock($fileHandle, LOCK_EX)){
                    flock($fileHandle, LOCK_UN);
                    fclose($fileHandle);
                    $this->writeablePath = $path;
                }
            }
        }
        if(!$foundPath){
            $title = urlencode("获取锁失败");
            $name = urlencode(WX_APP_TITLE);
            $url = urlencode(WX_APP_URL);
            $status = urlencode(WX_APP_TITLE.'获取'.$fileName.$this->lockName.'失败！');
            $remark = '';
            $url = "http://wxbooking-maintenance.quickgot.com/api/sendMessage/alarm?title={$title}&name={$name}&url={$url}&status={$status}&remark={$remark}";
            $this->getRequest($url);
            throw new Exception("Cannot establish lock on temporary file.");
        }
        
        return $path;
    }
    
    /**
     * @desc 发送url请求，应该抽出来作为工具方法
     * @param url 地址
     * @param data 请求数据，可以为空
     */
    function getRequest($url, $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        ob_start();
        curl_exec($ch);
        $rs = ob_get_contents();
        curl_close($ch);
        ob_end_clean();
        return $rs;
    }
}
