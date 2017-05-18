<?php
//取重定向的地址 
class RedirectUrl{ 
	//地址 
	var $url; 
	//初始化地址 
	function RedirectUrl($url){ 
		$this->url = $url; 
	} 
	/** 
	* get_redirect_url() 
	* 取重定向的地址 
	* 
	* @param string $url 
	* @return string 
	*/ 
	private function get_redirect_url($url){ 
		$redirect_url = null; 
		
		$url_parts = @parse_url($url); 
		if (!$url_parts) return false; 
		if (!isset($url_parts['host'])) return false; //can't process relative URLs 
		if (!isset($url_parts['path'])) $url_parts['path'] = '/'; 
		
		$sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 300); 
		if (!$sock) return false; 
		
		$request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ?'?'.$url_parts['query'] : '') . " HTTP/1.1\r\n"; 
		$request .= 'Host: ' . $url_parts['host'] . "\r\n"; 
		$request .= "Connection: Close\r\n\r\n"; 
		fwrite($sock, $request); 
		$response = ''; 
		while(!feof($sock)) $response .= fread($sock, 8192); 
		fclose($sock); 
		
		if (preg_match('/^Location: (.+?)$/m', $response, $matches)){ 
			return trim($matches[1]); 
		} else { 
			return false; 
		} 
	} 
	
	/** 
	* get_all_redirects() 
	* 取所有重定向地址 
	* 
	* @param string $url 
	* @return array 
	*/ 
	private function get_all_redirects($url){ 
		$redirects = array(); 
		while ($newurl = $this->get_redirect_url($url)){ 
			if (in_array($newurl, $redirects)){ 
				break; 
			} 
			$redirects[] = $newurl; 
			$url = $newurl; 
		} 
		return $redirects; 
	} 
	
	/** 
	* get_final_url() 
	* 取实际地址 
	* 
	* @param string $url 
	* @return string 
	*/ 
	function get_final_url(){ 
		$redirects = $this->get_all_redirects($this->url); 
		
		if (count($redirects)>0){ 
			return array_pop($redirects); 
		} else { 
			return $this->url; 
		} 
	} 
} 
?> 