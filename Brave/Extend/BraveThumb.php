<?php

class BraveThumb extends Brave {
	var $src = null;
	var $base = null;
	var $desc = null;
	var $thumb = null;
	
	function BraveThumb() {
        $file = LIBRARY . 'PHPThumb' . DS . 'ThumbLib.inc.php';
        $this->includeOnce($file);
    }
    
    function setSrc($base, $src, $dest = '_thumb') {
    	if (!$src || !preg_match('/(.*)\.(jpg|jpeg|png|gif)$/i',$src, $m)) {
    		return false;
    	}
    	
    	$this->desc = $m[1] . $dest . '.' . $m[2];
    	$this->src = $src;
		$this->base = $base;
    	$this->thumb = PhpThumbFactory::create($base.$this->src);
    	if (!$this->thumb) {
    		return false;
    	}
    	
    	return true;
    }
    
    function crop ($startX, $startY, $cropWidth, $cropHeight) {
		$this->thumb->crop($startX, $startY, $cropWidth, $cropHeight);
    }
    
    /**
     * 从中心开始裁剪
     * @param $cropWidth
     * @param $cropHeight
     */
    function cropFromCenter($cropWidth, $cropHeight = null) {
		$this->thumb->cropFromCenter($cropWidth, $cropHeight);
    }
    
    /**
     * 从左上角开始裁剪
     * @param $cropWidth
     * @param $cropHeight
     */
    function cropFromLeftTop($cropWidth, $cropHeight) {
    	$this->thumb->crop(0, 0, $cropWidth, $cropHeight);
    }
    
    /**
     * 从右上角开始裁剪
     * @param $cropWidth
     * @param $cropHeight
     */
    function cropFromRightTop($cropWidth, $cropHeight) {
    	$dimensions = $this->thumb->getCurrentDimensions();
    	$startX = $dimensions['width'] - $cropWidth;
    	$this->thumb->crop($startX, 0, $cropWidth, $cropHeight);
    }
    
    /**
     * 按百分比等比缩放
     * @param $percent
     */
    function resizePercent ($percent = 0) {
    	$this->thumb->resizePercent($percent);
    }
    
    /**
     * 按照宽高等比缩放
     * @param $maxWidth
     * @param $maxHeight
     */
    function resize ($maxWidth = 0, $maxHeight = 0) {
    	$this->thumb->resize($maxWidth, $maxHeight);
    }
    
    function adaptiveResize ($width, $height) {
    	$this->thumb->adaptiveResize($width, $height);
    }
    
    function show ($rawData = false) {
    	$this->thumb->show($rawData);
    }
    
    function save ($format = null) {
    	$this->thumb->save($this->base.$this->desc, $format);
    	return $this->desc;
    }
}