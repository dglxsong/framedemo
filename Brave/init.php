<?php

date_default_timezone_set("Asia/Chongqing");

define('ROOT', dirname(__FILE__));

define('ACT_NO_ROLE', 0); 
define('ACT_EVERYONE', 99);

define('CORE', ROOT . DS . 'Core' .DS);
define('EXTEND', ROOT . DS . 'Extend' .DS);
define('LIBRARY', ROOT . DS . 'Library' .DS);
define('TEMPLATE', ROOT . DS . 'Template' .DS);

define('APP_ROOT', APP . 'App' . DS); 
define('APP_CACHE', APP . 'Cache' . DS);
define('APP_CONFIG', APP . 'Config' . DS);
define('APP_LOG', APP . 'Log' . DS);

define('APP_CORE', APP_ROOT . 'Core' . DS);
define('APP_EXTEND', APP_ROOT . 'Extend' . DS);
define('APP_CONTROLLER', APP_ROOT . 'Controllers' . DS);
define('APP_MODEL', APP_ROOT . 'Models' . DS);
define('APP_TEMPLATE', APP_ROOT . 'Templates' . DS);
define('APP_UPLOAD', APP_WEBROOT . 'upload' . DS);


/**
 * @desc 系统定义
 */
include_once(CORE . 'Brave.php');
include_once(CORE . 'BraveException.php');
include_once(CORE . 'BraveDispatcher.php');
include_once(CORE . 'BraveDB.php');
include_once(CORE . 'BraveController.php');
include_once(CORE . 'BraveModel.php');
include_once(CORE . 'BraveView.php');
include_once(CORE . 'BraveValidator.php');
include_once(CORE . 'BaseModel.php');
include_once(CORE . 'BaseController.php');

include_once(APP_CONFIG . 'App.inc.php');
include_once(APP_CONFIG . 'Code.inc.php');
include_once(APP_CONFIG . 'Core.inc.php');
include_once(APP_CONFIG . 'Act.inc.php');
include_once(APP_CONFIG . 'Lang.inc.php');
include_once(APP_CONFIG . 'Mail.inc.php');
include_once(APP_CONFIG . 'Route.inc.php');

include_once(APP_CORE . 'AppController.php');
include_once(APP_CORE . 'AppModel.php');
include_once(APP_CORE . 'AppView.php');

function pr($data, $exit = false) {
    print_r('<pre>');
    print_r($data);
    print_r('</pre>');
    if ($exit) exit;
}

function errorHandler($errno = 0, $errstr = '', $errfile = null, $errline = null) {
    $error = array(
        'errno' => $errno,
        'errstr' => $errstr,
        'errfile' => $errfile,
        'errline' => $errline,
    );
    
    $exception = new BraveException;
    $exception->handle($error);
}



if (false) {
    set_error_handler('errorHandler');
}

if (defined('SESSION_START') && SESSION_START) {
    session_start();
}


?>
