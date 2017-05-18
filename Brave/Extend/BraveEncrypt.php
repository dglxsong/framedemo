<?php
class BraveEncrypt extends Brave {
	function encryptWithKey($source,$encryptKey,$encryptIV) {
		/* Open the cipher */
	    $td = mcrypt_module_open(MCRYPT_BLOWFISH, '', 'cbc', '');
	
	    /* Create the IV and determine the keysize length, used MCRYPT_RAND
	     * on Windows instead */
	    $ks = mcrypt_enc_get_key_size($td);
	
	    /* Create key */
	    $key = substr(md5($encryptKey), 0, $ks);
	
	    /* Intialize encryption */
	    mcrypt_generic_init($td, $key, $encryptIV);
	
	    /* Encrypt data */
	    $encrypted = mcrypt_generic($td, $source);
	
	    mcrypt_generic_deinit($td);
	    mcrypt_module_close($td);
	    
	    return bin2hex($encrypted);
	}
	
	function decryptWithKey($encrypted,$encryptKey,$encryptIV) {
		$encrypted = $this->hex2bin($encrypted);
		
		/* Open the cipher */
	    $td = mcrypt_module_open(MCRYPT_BLOWFISH, '', 'cbc', '');
	
	    /* Create the IV and determine the keysize length, used MCRYPT_RAND
	     * on Windows instead */
	    $ks = mcrypt_enc_get_key_size($td);
	
	    /* Create key */
	    $key = substr(md5($encryptKey), 0, $ks);
	
	    /* Initialize encryption module for decryption */
	    mcrypt_generic_init($td, $key, $encryptIV);
	
	    /* Decrypt encrypted string */
	    $decrypted = mdecrypt_generic($td, $encrypted);
	
	    /* Terminate decryption handle and close module */
	    mcrypt_generic_deinit($td);
	    mcrypt_module_close($td);
	    
	    return trim($decrypted);
	}
	
	function hex2bin($data) { 
       $len = strlen($data); 
       return pack("H" . $len, $data); 
    }
}
?>