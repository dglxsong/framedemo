<?php

function smarty_function_brave_date_format($params, &$smarty) {
    $default = isset($params['default'][0])? $params['default']: '';
    $date = isset($params['date'][0])? $params['date']: '';
    $nRegex = '/^[0-9]{4}[\-\/]+[0-9]{2}[\-\/]+[0-9]{2}/';
    $sRegex = '/^0000/';
    $bRegex = '/^2099/';

    if (!preg_match($nRegex, $date)) {
        return $default;
    }
    else if (preg_match($sRegex, $date)) {
        return $default;
    }
    else if (preg_match($bRegex, $date)) {
        return $default;
    }

    $format = isset($params['format'][0])? $params['format']: 'y-m-d';
    $time = strtotime($date);
    $y = date('Y', $time);
    $m = date('m', $time);
    $d = date('d', $time);

    switch($format) {
        case 'ymd':
            $date = date('Ymd', $time);
            break;
        case 'y-m-d':
            $date = date('Y-m-d', $time);
            break;
		case 'm/d':
            $date = date('m/d', $time);
            break;
		case 'y-m-d h':
            $date = date('Y-m-d H', $time);
            break;
		case 'y-m-d h:i':
            $date = date('Y-m-d H:i', $time);
            break;
		case 'y-m-d h:i:s':
            $date = date('Y-m-d H:i:s', $time);
            break;
        case 'y/m/d':
            $date = date('Y/m/d', $time);
            break;
		case 'y/m/d h':
            $date = date('Y/m/d H', $time);
            break;
		case 'y/m/d h:i':
            $date = date('Y/m/d H:i', $time);
            break;
		case 'y/m/d h:i:s':
            $date = date('Y/m/d H:i:s', $time);
            break;
        case 'h:i:s':
            $date = date('H:i:s', $time);
            break;
        case '年月日':
            $date = "{$y}年{$m}月{$d}日";
			break;
        case '年月日時分':
            $date = "{$y}年{$m}月{$d}日 " . date('H時i分', $time);
            break;
		case 'j-M-y':
			$date = date('j-M-y', $time);
			break;
        case 'd-M-y':
            $date = date('d-M-y', $time);
            break;
		case 'Y.m.d':
			$date = date('Y.m.d', $time);
			break;
		default:
			break;
    }
    
    return $date;
}  

?>
