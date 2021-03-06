<?php
/**
 * BCGDrawPNG.php
 *--------------------------------------------------------------------
 *
 * Image Class to draw PNG images with possibility to set DPI
 *
 *--------------------------------------------------------------------
 * Revision History
 * v2.1.0	8  nov	2009	Jean-Sébastien Goupil
 *--------------------------------------------------------------------
 * $Id: BCGDrawPNG.php,v 1.1 2009/11/09 04:15:10 jsgoupil Exp $
 * PHP5-Revision: 1.1
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGDraw.php');

if (!function_exists('file_put_contents')) {
	function file_put_contents($filename, $data) {
		$f = @fopen($filename, 'w');
		if (!$f) {
			return false;
		} else {
			$bytes = fwrite($f, $data);
			fclose($f);
			return $bytes;
		}
	}
}

class BCGDrawPNG extends BCGDraw {
	var $dpi;
	
	/**
	 * Constructor
	 *
	 * @param resource $im
	 */
	function BCGDrawPNG(&$im) {
		BCGDraw::BCGDraw($im);
	}

	/**
	 * Sets the DPI
	 *
	 * @param int $dpi
	 */
	function setDPI($dpi) {
		if(is_numeric($dpi)) {
			$this->dpi = max(1, $dpi);
		} else {
			$this->dpi = null;
		}
	}

	/**
	 * Draws the PNG on the screen or in a file
	 */
	function draw() {
		ob_start();
		
		$w = imagesx($this->im);
		$h = imagesy($this->im);
		imagecolortransparent($this->im, imagecolorallocate($this->im,255, 255, 255));
		
		imagepng($this->im);
		$bin = ob_get_contents();
		ob_end_clean();

		$this->setInternalProperties($bin);

		if (empty($this->filename)) {
			$image = imagecreatefromstring($bin);
			echo $bin;
		} else {
			file_put_contents($this->filename, $bin);
		}
	}

	function setInternalProperties(&$bin) { // private
		// Scan all the ChunkType
		if(strcmp(substr($bin, 0, 8), pack('H*', '89504E470D0A1A0A')) === 0) {
			$chunks = $this->detectChunks($bin);

			$this->internalSetDPI($bin, $chunks);
			$this->internalSetC($bin, $chunks);
		}
	}

	function detectChunks($bin) { // private
		$data = substr($bin, 8);
		$chunks = array();
		$c = strlen($data);
		
		$offset = 0;
		while($offset < $c) {
			$packed = unpack('Nsize/a4chunk', $data);
			$size = $packed['size'];
			$chunk = $packed['chunk'];

			$chunks[] = array('offset'=>$offset + 8, 'size'=>$size, 'chunk'=>$chunk);
			$jump = $size + 12;
			$offset += $jump;
			$data = substr($data, $jump);
		}
		
		return $chunks;
	}

	function internalSetDPI(&$bin, &$chunks) { // private
		if($this->dpi !== null) {
			$meters = (int)($this->dpi * 39.37007874);

			$found = -1;
			$c = count($chunks);
			for($i = 0; $i < $c; $i++) {
				// We already have a pHYs
				if($chunks[$i]['chunk'] === 'pHYs') {
					$found = $i;
					break;
				}
			}

			$data = 'pHYs' . pack('NNC', $meters, $meters, 0x01);
			$crc = BCGDrawPNG::crc($data, 13);
			$cr = pack('Na13N', 9, $data, $crc);

			// We didn't have a pHYs
			if($found == -1) {
				// Don't do anything if we have a bad PNG
				if($c >= 2 && $chunk[0]['chunk'] = 'IHDR') {
					array_splice($chunks, 1, 0, array(array('offset'=>33, 'size'=>9, 'chunk'=>'pHYs')));

					// Push the data
					for($i = 2; $i < $c; $i++) {
						$chunks[$i]['offset'] += 21;
					}

					$firstPart = substr($bin, 0, 33);
					$secondPart = substr($bin, 33);
					$bin = $firstPart;
					$bin .= $cr;
					$bin .= $secondPart;
				}
			} else {
				$bin = substr_replace($bin, $cr, $chunks[$i]['offset'], 21);
			}
		}
	}

	function internalSetC(&$bin, &$chunks) { // private
		if(count($chunks) >= 2 && $chunk[0]['chunk'] = 'IHDR') {
			$firstPart = substr($bin, 0, 33);
			$secondPart = substr($bin, 33);
			$cr = pack('H*', '0000004C74455874436F707972696768740047656E657261746564207769746820426172636F64652047656E657261746F7220666F722050485020687474703A2F2F7777772E626172636F64657068702E636F6D597F70B8');
			$bin = $firstPart;
			$bin .= $cr;
			$bin .= $secondPart;
		}
		
		// Chunks is dirty!! But we are done.
	}

	var $crc_table = array();
	var $crc_table_computed = false;

	function make_crc_table() { // private static
		for($n = 0; $n < 256; $n++) {
			$c = $n;
			for ($k = 0; $k < 8; $k++) {
				if (($c & 1) == 1) {
					$c = 0xedb88320 ^ (BCGDrawPNG::SHR($c, 1));
				} else {
					$c = BCGDrawPNG::SHR($c, 1);
				}
			}
			$this->crc_table[$n] = $c;
		}

		$this->crc_table_computed = true;
	}

	function SHR($x, $n) { // private static
		$mask = 0x40000000;

		if ($x < 0) {
			$x &= 0x7FFFFFFF;
			$mask = $mask >> ($n - 1);
			return ($x >> $n) | $mask;
		}

		return (int)$x >> (int)$n;
	}

	function update_crc($crc, $buf, $len) { // private static
		$c = $crc;

		if (!$this->crc_table_computed) {
			BCGDrawPNG::make_crc_table();
		}
		
		for($n = 0; $n < $len; $n++) {
			$c = $this->crc_table[($c ^ ord($buf[$n])) & 0xff] ^ (BCGDrawPNG::SHR($c, 8));
		}

		return $c;
	}

	function crc($data, $len) { // private static
		return BCGDrawPNG::update_crc(-1, $data, $len) ^ -1;
	}
	
}
?>
