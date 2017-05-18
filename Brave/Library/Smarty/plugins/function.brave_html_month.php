<?php
function smarty_function_brave_html_month($params, &$smarty) {
    $result = '';
    
	$month = $params['month'];
    $url = $params['url'];

    $days = date('t',strtotime($month . '-01'));

    $start = $month . '-01';
    $end = $month . '-' . $days;

    $allDays = array();
    $startTime = strtotime($start);
    $weekOfStart = date('w',$startTime);
    for ($i=$weekOfStart - 1; $i > 0 ; $i--) { 
        $allDays[] = array(
            'd' => date('d',strtotime("-{$i} day",$startTime)),
            'date' => date('Y-m-d',strtotime("-{$i} day",$startTime)),
            'type' => 'prev',
        );
    }

    for ($i=0;$i < $days; $i++) { 
        $allDays[] = array(
            'd' => date('d',strtotime("+{$i} day",$startTime)),
            'date' => date('Y-m-d',strtotime("+{$i} day",$startTime)),
            'type' => 'current',
        );
    }

    $endTime = strtotime($end);
    $weekOfEnd = date('w',$endTime);
    if ($weekOfEnd) {
        for ($i=1;$i < (8 - $weekOfEnd); $i++) { 
            $allDays[] = array(
                'd' => date('d',strtotime("+{$i} day",$endTime)),
                'date' => date('Y-m-d',strtotime("+{$i} day",$endTime)),
                'type' => 'next',
            );
        }
    }
    //pr($allDays);

    $result .= '<table>';
    $result .= '
    <tr>
        <th colspan="8" class="center">' . date('Y年m月',$startTime). '</th>
    </tr>
    <tr>
        <td>一</td>
        <td>二</td>
        <td>三</td>
        <td>四</td>
        <td>五</td>
        <td>六</td>
        <td>日</td>
        <td>设置</td>
    </tr>
    <tr>';

    foreach ($allDays as $key => $value) {
        $endOfWeek = $value['date'];
        if ($key % 7 == 0 && $key) {
            $result .= '</tr><tr>';
        }

        $class = $value['type'] == 'current' ? '' : " class='red'";
        $result .= '<td ' . $class . '>' . $value['d'] . '</td>';
        if ($key % 7 == 6) {
            $result .= '<td><a href="' . $url . '&sh[lastday]=' . $endOfWeek . '"><img src="./image/btn_edit_small.png"></a></td>';
        }
    }
    $result .= '</tr>
    </table>';
    return $result;
}  

?>
