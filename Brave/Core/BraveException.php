<?php

class BraveException extends Brave {

    var $error = null;
    var $errtypes = array (
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSING ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
        E_STRICT => 'STRICT NOTICE',
    );

    var $filter = array(
        E_STRICT,
        E_NOTICE,
        E_USER_NOTICE,
        E_WARNING,
        E_USER_WARNING,
    );

    /**
    * handle
    * 
    * @param int $errno
    * @param string $errstr
    * @param string $errfile
    * @param string $errline
    */
    function handle($error) {
        $errno = $error['errno'];
        $errtypes = $this->errtypes;

        if (isset($errtypes[$errno])) {
            if (in_array($errno, $this->filter))
                return true;
            else
                $this->error = $error;

            if (DEBUG_MODE)
                $this->show('debug');
            else {
                $this->log($error, 'server-error');
                $this->show('500');
            }
        }
    }
    
    function show($tpl) {
        $tpl = 'Exception.' . $tpl;
        $data = array(
            'errtypes' => $this->errtypes,
            'error' => $this->error,
        );

        $view = $this->getView();
        $view->autoAssign($data);
        $view->display($tpl);
        exit;
    }
}

?>