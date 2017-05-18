<?php

include_once(LIBRARY . DS . 'ApnsPHP' . DS . 'Autoload.php');

/**
* Android platform Use GCM
*/
define('GOOGLE_GCM_URL', 'https://android.googleapis.com/gcm/send');
define('GOOGLE_GCM_API_KEY', 'AIzaSyA4uqC9XH9A7PiApdTpz75KPv4fdEy5EHA');

class BravePush extends Brave {

    function iOSPush($msgs, $cert) {
        $push = new ApnsPHP_Push(
            ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
            $cert
        );
        
        $push->setRootCertificationAuthority(ROOT_CERT);
        $push->connect();
        $msgIds = array();
         
        foreach ($msgs as $v) {
            if (!strlen($v['body'])) {
                continue;
            }
            
            if (!preg_match('/^[a-z0-9]{64}$/', $v['token'])) {
                continue;
            }
            
            $id = $v['id'];
            $token = $v['token'];
            $body = $v['body'];
            
            $msgIds[$id] = $id;
            $message = new ApnsPHP_Message();
            $message->setText($body);
            $message->addRecipient($token);
            $message->setCustomIdentifier("{$id}#{$token}");
            $message->setBadge(1);
            $push->add($message);
        }
        
        $push->send();
        $push->disconnect();
        $errors = $push->getErrors();
        
        foreach ($errors as $v) {
            $cid = $v['MESSAGE']->getCustomIdentifier();
            list($id, $token) = explode('#', $cid);
            unset($msgIds[$id]);
        }
        
        return $msgIds;
    }

    function android_init() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, GOOGLE_GCM_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $header = array(
            'Authorization: key=' . GOOGLE_GCM_API_KEY,
            'Content-Type: application/json',
        );
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $this->push = $ch;
        return true;
    }
    
    function android_send(&$data) {
        if (!$data || !$this->push) {
            $this->all_error($data, 'init error');
            return false;
        }
        
        foreach ($data as $k => $v) {
            if (!strlen($v['text']) || !strlen($v['token'])) {
                $data[$k]['error'] = 'invalid token or text';
                continue;
            }
            
            $token = $v['token'];
            is_array($token) or $token = array($token);
            $text = $v['text'];
            $fields = array(
                'registration_ids' => $token,
                'data'=> array('message' => $text),
            );
            
            $fields = json_encode($fields);
            curl_setopt($this->push, CURLOPT_POSTFIELDS, $fields);
            $rs = curl_exec($this->push);
            
            if ($rs === false) {
                $data[$k]['error'] = curl_error($this->push);
                continue;
            }
            
            $rs = json_decode($rs, true);
            
            if (isset($rs['results'][0]['error'])) {
                $error = $rs['results'][0]['error'];
                $data[$k]['error'] = $error;
            }
        }
        
        return true;
    }
}

?>
