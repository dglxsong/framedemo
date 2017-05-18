<?php

function smarty_function_brave_jpdate($params, &$smarty) {
    $strTime = strtotime($params['date']);
    if (empty($params['date']) || (!$strTime && $params['from']!='jp' && $params['from']!='legal' && $params['from']!='jpcode' && $params['from']!='notification')) {
        return null;
    }

    $date = trim($params['date']);
    $year = (strlen($date)==4)?$date:date('Y',$strTime);
    $format = $params['format'];
    $from = empty($params['from'])?'default':$params['from'];
    if ($from == 'default') {
        $jpYear = array();
        
        if($year >= 1989){
            $jpYear = array('平成',$year - 1988);
        }elseif($year >= 1926){
            $jpYear = array('昭和',$year - 1925);
        }elseif($year >= 1912){
            $jpYear = array('大正',$year - 1912);
        }elseif($year >= 1868){
            $jpYear = array('明治',$year - 1867);
        }
        
        $result = '';
        if ($format == '年月日') {
            $result = $jpYear[0].$jpYear[1].date('年m月d日',$strTime);
        } elseif ($format == '年') {
            $result = $jpYear[0].$jpYear[1].'年';
        } elseif ($format == '月日') {
            $result = date('m月d日',$strTime);
        } else {
            $result = substr($jpYear[0],0,3).$jpYear[1].date('.m.d',$strTime);
        }
    } elseif ($from == 'legal') {
        $y = array(1=>'明',2=>'大',3=>'昭',4=>'平');
        $result = $y[$date[0]].''.$date[1].$date[2].'.'.$date[3].$date[4].'.'.$date[5].$date[6];
    } elseif ($from == 'jp') {
        $y = array(1=>'明',2=>'大',3=>'昭',4=>'平');
        $result = $y[$date[0]].'.'.$date[1].$date[2].'.'.$date[3].$date[4].'.'.$date[5].$date[6];
    } elseif ($from == 'jpcode') {
        $y = array(1=>'明治',2=>'大正',3=>'昭和',4=>'平成');
        $result = $y[$date[0]].$date[1].$date[2].'年'.$date[3].$date[4].'月'.$date[5].$date[6].'日';
    } elseif ($from == 'notification') {
        $y = array(1=>'明治',2=>'大正',3=>'昭和',4=>'平成');
        $year = intval($date[1].$date[2]);
        $month = intval($date[3].$date[4]);
        $day = intval($date[5].$date[6]);
        $result = $y[$date[0]].$year.'年'.$month.'月'.$day.'日';
    }

    return $result;
}  

?>
