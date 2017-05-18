<?php

class BravePreview extends Brave {
	var $src = null;
	var $base = null;
	var $desc = null;
	var $fileName = null;
	var $format = null;
	
	var $oldImage = null;
	var $newImage = null;
	var $currentDimensions = array();
	
	function setSrc($base,$src) {
		if (!$src || !preg_match('/(.*)\.(jpg|jpeg|png|gif)$/i',$src, $m)) {
    		return false;
    	}
    	
    	$this->desc = $m[1] . '_preview.' . $m[2];
    	$this->src = $src;
		$this->base = $base;
		$this->fileName = $base . $src;
		
		$this->determineFormat();
		
		switch ($this->format) {
			case 'GIF':
				$this->oldImage = imagecreatefromgif($this->fileName);
				break;
			case 'JPG':
				$this->oldImage = imagecreatefromjpeg($this->fileName);
				break;
			case 'PNG':
				$this->oldImage = imagecreatefrompng($this->fileName);
				break;
			default:
				return false;
		}
		
		$this->currentDimensions = array
		(
			'width' 	=> imagesx($this->oldImage),
			'height'	=> imagesy($this->oldImage)
		);
		
		if ($this->format == 'PNG') {
			imagesavealpha($this->oldImage,true);
		}
		
		return $this->oldImage ? true : false;
	}
	
	function determineFormat () {
		$formatInfo = getimagesize($this->fileName);
		if ($formatInfo === false) {
			return false;
		}
		
		$mimeType = isset($formatInfo['mime']) ? $formatInfo['mime'] : null;
		
		switch ($mimeType) {
			case 'image/gif':
				$this->format = 'GIF';
				break;
			case 'image/jpeg':
				$this->format = 'JPG';
				break;
			case 'image/png':
				$this->format = 'PNG';
				break;
			default:
				break;
		}
	}
	
	function getRGB($color) {
		$color = str_replace('#','',$color);
		return array(
			'r' => substr($color,0,2),
			'g' => substr($color,2,2),
			'b' => substr($color,4),
		);
	}
	
	function drawMessageArea($area = array(),$message = '') {
		$x = $area['x'];
		$y = $area['y'];
		$w = $area['w'];
		$h = $area['h'];
		$fontsize = $area['fontsize'] ? $area['fontsize'] : 20;
		$text = $area['text'];
		$textcolor = $area['color'];
		if (!$area['enable']) {
			return false;
		}
		
		if (($x + $w) > $this->currentDimensions['width'] 
			|| ($y + $h) > $this->currentDimensions['height']
			|| !$w || !$h
		) {
			return false;
		}
		
		$color = imagecolorallocate($this->oldImage, 255, 0, 0);
		imagesetthickness($this->oldImage,1);
		imagerectangle($this->oldImage,$x,$y,$x + $w,$y + $h,$color);
		
		$rgb = $this->getRGB($textcolor);
		$textcolor = imagecolorallocate($this->oldImage, hexdec($rgb['r']), hexdec($rgb['g']), hexdec($rgb['b']));
		
		$font = APP_WEBROOT . 'simhei.ttf';
		$box = imagettfbbox($fontsize,0,$font,$text);
		$boxW = $box[2] - $box[0];
		$boxH = $box[1] - $box[7];
		imagettftext($this->oldImage, $fontsize, 0, $x + ($w - $boxW)/2, $y + abs($box[7]) + ($h - $boxH)/2, $textcolor, $font, $text);
	}
	
	function drawImageArea($area = array()) {
		$x = $area['x'];
		$y = $area['y'];
		$w = $area['w'];
		$h = $area['h'];
		if (!$area['enable']) {
			return false;
		}
		
		if (($x + $w) > $this->currentDimensions['width'] 
			|| ($y + $h) > $this->currentDimensions['height'] 
			|| !$w || !$h
		) {
			return false;
		}
		
		$color = imagecolorallocate($this->oldImage, 0, 255, 0);
		imagesetthickness($this->oldImage,1);
		imagerectangle($this->oldImage,$x,$y,$x + $w,$y + $h,$color);
	}
	
	function save ($format = null) {
		$validFormats = array('GIF', 'JPG', 'PNG');
		$format = ($format !== null) ? strtoupper($format) : $this->format;
		
		if (!in_array($format, $validFormats)) {
			return false;
		}
		
		switch ($format) {
			case 'GIF':
				imagegif($this->oldImage, $this->base . $this->desc);
				break;
			case 'JPG':
				imagejpeg($this->oldImage, $this->base . $this->desc, 100);
				break;
			case 'PNG':
				imagepng($this->oldImage, $this->base . $this->desc);
				break;
		}
		
		return $this->desc;
	}
	
	
}