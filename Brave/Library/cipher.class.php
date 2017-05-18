<?php

/**
* @desc CIPHER Center Auth
*/
define('CIPHER_AUTH_CIPHER', MCRYPT_RIJNDAEL_128);
define('CIPHER_AUTH_MODE', MCRYPT_MODE_CBC);

class cipher {
    
    var $cipher = null;
    
    function init() {
        $cipher = CIPHER_AUTH_CIPHER;
        $mode = CIPHER_AUTH_MODE;
        $this->cipher = mcrypt_module_open($cipher, '', $mode, '');
    }

    function encrypt($data, $key, $iv) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->encrypt($v, $key);
            }
        }
        else if (strlen($data)) {
            if (mcrypt_generic_init($this->cipher, $key, $iv) != -1) {
                $cipherText = mcrypt_generic($this->cipher, $data);
                mcrypt_generic_deinit($this->cipher);
                return base64_encode($cipherText);
            }
            else {
                systemError('mcrypt init error');
            }
        }
        
        return $data;
    }
    
    function decrypt($data, $key, $iv) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->decrypt($v, $key);
            }
        }
        else if (strlen($data)) {
            $data = base64_decode($data);

            if (mcrypt_generic_init($this->cipher, $key, $iv) != -1) {
                $data = mdecrypt_generic($this->cipher, $data);
                mcrypt_generic_deinit($this->cipher);
                return trim($data);
            }
            else {
                systemError('mcrypt init error');
            }
        }
        
        return $data;
    }
}