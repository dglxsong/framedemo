<?php

class BaseModel extends BraveModel {

    function BaseModel($dsnid=1){
       $this->BraveModel($dsnid);
    }

    function sendSMS($config, $sms) {
    
        $phone = $sms['phone'];
        $message = $sms['message'];
        $url = $config['url']['send'];

        $data = array(
            'SpCode' => $config['SpCode'],
            'LoginName' => $config['LoginName'],
            'Password' => $config['Password'],
            'MessageContent' => urlencode(mb_convert_encoding($message, 'gb2312', 'utf8')),
            'UserNumber' => $phone,
            'SerialNumber' => '',
            'ScheduleTime' => '',
            'f' => 1
        );

        $url .= $this->makeParameter($data);

        $result = $this->httpRequest($url);

        $param = array();
        foreach(explode('&', $result) as $value) {
            $value = explode('=', $value);
            $param[$value[0]] = mb_convert_encoding($value[1], 'utf8', 'gb2312');
        }

        $param['raw'] = mb_convert_encoding($result, 'utf8', 'gb2312');

        $this->saveSentUserSMS($phone, $sms, $param);

        return $param['result'] == 0;
    }
    
    function saveUserSMS($phone, $message) {

        $table = 'tb_sms';
        $data = array(
            'phone' => $phone,
            'message' => $message,
            'status' => 1,
        );

        $data['created'] = NOW;
        return $this->Insert($table, $data);
    }
    
    function saveSentUserSMS($phone, $sms, $result) {

        $phone = $sms['phone'];
        $message = $sms['message'];

        $table = 'tb_sms';
        $data = array(
            'phone' => $phone,
            'message' => $message,
            'status' => $result['result'] == 0 ? 3 : 4,
            'result' => $result['result'],
            'data' => serialize($result),
        );

        if ($sms['id'] > 0) {
            $where = "id = '{$sms['id']}'";
            $this->Update($table, $data, $where);
            return $sms['id'];
        } else {
            $data['created'] = NOW;
            return $this->Insert($table, $data);
        }
    }

    function getAccessToken() {
        $sql = 'SELECT * FROM tb_access_token';
        
        $rs = $this->getOne($sql);
        return $rs['access_token'];       
    }
}

?>
