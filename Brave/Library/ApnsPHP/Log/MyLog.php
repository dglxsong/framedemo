<?php

class ApnsPHP_Log_MyLog implements ApnsPHP_Log_Interface {

	function log($sMessage) {
		// file
		$type = 'push';
		$dir = $this->mkdir();
		$file = $dir . $type . '.log';

		if (!is_file($file) && $fp = fopen($file, 'a')) {
			fclose($fp);
			$old = umask(0);
			chmod($file, 0777);
			umask($old);
		}

		if (!$fp = fopen($file, 'a')) {
			trigger_error("Can not mkfile: {$file}", E_USER_WARNING);
			exit;
		}

		// log
		flock($fp, LOCK_EX);


		$log = sprintf("[%s] : %s\n", date('Y-m-d H:i:s'),trim($sMessage));
		fwrite($fp, $log);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	function mkdir() {

		// dir
		$today = date('Y-m-d');
		$dir = APP_LOG . $today . DS;

		if (!file_exists($dir)) {
			if (!mkdir($dir, 0777, TRUE))
				trigger_error("Can not mkdir: {$dir}", E_USER_WARNING);
		}

		return $dir;
	}

}