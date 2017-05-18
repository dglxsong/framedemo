<?php

class BraveVerifyReceipt extends Brave {
	
	function verifyReceipt($data, $sandboxMode = false) {
		if(strpos($data,'{') !== false) {
			$data = base64_encode($data);
		}
		$data = json_encode(array('receipt-data' => $data));
		
		$ch = curl_init();
		if ($sandboxMode) {
			curl_setopt($ch,CURLOPT_URL,VERIFY_URL_SANDBOX);
		} else {
			curl_setopt($ch,CURLOPT_URL,VERIFY_URL);
		}
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); //post到https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $res = curl_exec($ch);
        curl_close($ch);
        
		return (array)json_decode($res);
	}

	function verify($data) {
		$return = $this->verifyReceipt($data,false);
		if (!$return) {
			return false;
		}

		//The 21007 status code indicates that this receipt is a sandbox receipt, 
		//but it was sent to the production service for verification.
		if ($return['status'] === 21007) {
			$return = $this->verifyReceipt($data,true);
			$return ? $return['environment'] = 1 : null; //1:sandbox 2:production
			return $return;
		} else {
			$return['environment'] = 2;
			return $return;
		}
	}
}
?>