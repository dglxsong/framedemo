<?php

class BraveHttp extends Brave {
    /**
    * browser
    * 
    */
    function browser() {
        $ua = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/MSIE[\s]+8/is', $ua)) {
            $browser = 'MSIE8';
        }
        elseif (preg_match('/MSIE[\s]+7/is', $ua)) {
            $browser = 'MSIE7';
        }
        elseif (preg_match('/MSIE[\s]+6/is', $ua)) {
            $browser = 'MSIE6';
        }
        elseif (preg_match('/Firefox\/2/is', $ua)) {
            $browser = 'FireFox2';
        }
        elseif (preg_match('/Firefox\/3/is', $ua)) {
            $browser = 'FireFox3';
        }
        elseif (preg_match('/Chrome/is', $ua)) {
            $browser = 'Chrome';
        }
        elseif (preg_match('/Safari/is', $ua)) {
            $browser = 'Safari';
        }
        elseif (preg_match('/Opera/is', $ua)) {
            $browser = 'Opera';
        }
        else {
            $browser = 'unknow';
        }
        
        return $browser;
    }
}

?>
