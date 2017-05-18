<?php

class WxPayRuntimeException extends Exception {
    public function errorMessage()
    {
        return $this->getMessage();
    }

}

?>