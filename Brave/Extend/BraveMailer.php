<?php

class BraveMailer extends Brave {
    
    var $mailer = null;
    var $tpldir = 'Sendmail.';
    var $code = 'utf-8';
    var $config = null;
    var $logType = 'Sendmail';
    var $html = false;
    
    function BraveMailer() {
        $file = LIBRARY . 'PHPMailer' . DS . 'class.phpmailer.php';
        $this->mailer = $this->newObject($file, 'PHPMailer');
        $this->mailer->SMTPDebug = 2;
    }
    
    function setConfig($config) {
        $this->config = $config;
    }
    
    function getConfig($key = null) {
        global $mail;
        $config = $this->getValue($mail, $key);
        $config['key'] = $key;

        if (!isset($config['smtp'])) $config['smtp'] = SEND_SMTP;
        if (!isset($config['port'])) $config['port'] = SMTP_PORT;
        if (!isset($config['host'])) $config['host'] = SMTP_HOST;
        if (!isset($config['user'])) $config['user'] = SMTP_USER;
        if (!isset($config['pass'])) $config['pass'] = SMTP_PASS;
        if (!isset($config['auth'])) $config['auth'] = SMTP_AUTH;
        if (!isset($config['secure'])) $config['secure'] = SMTP_SECURE;
        if (!isset($config['encode'])) $config['encode'] = SMTP_ENCODE;
        if (!isset($config['from'])) $config['from'] = SMTP_FROM;
        if (!isset($config['name'])) $config['name'] = SMTP_NAME;
        if (!isset($config['reply'])) $config['reply'] = SMTP_REPLY;
        
        if (isset($this->config['subject']))
            $config['subject'] = $this->config['subject'];
            
        if (isset($this->config['body']))
            $config['body'] = $this->config['body'];

        return $config;
    }
    
    function setAttachment($files) {
        if (empty($files)) {
            return false;
        }
        
        foreach ($files as $v) {
            $name = $v['name'];
            $filepath = str_replace('/', DS, $v['path']);

            if (!is_file($filepath) || !is_readable($filepath)) {
                continue;
            }
            
            $string = file_get_contents($filepath);
            $this->mailer->AddStringAttachment($string, $name);
        }

        return true;
    }
    
    function encodeName($name, $encode) {
        $name = mb_convert_encoding($name, $encode, $this->code);
        $name = mb_encode_mimeheader($name);
        return $name;
    }
    
    function encodeSubject($subject, $encode) {
        $subject = mb_convert_encoding($subject, $encode, $this->code);
        return $subject;
    }
    
    function encodeBody($body, $encode) {
        $body = mb_convert_encoding($body, $encode, $this->code);
        return $body;
    }
    
    function send($config, $data = null) {
        // init
        if ($config['smtp']) {
            $this->mailer->IsSMTP();
            $this->mailer->IsHTML($this->html);
            $this->mailer->Port = $config['port'];
            $this->mailer->Host = $config['host'];
            $this->mailer->Username = $config['user'];
            $this->mailer->Password = $config['pass'];
            //$this->mailer->IsHTML(true);
            
            if ($config['auth']) {
                $this->mailer->SMTPAuth = true;
            }
            
            if ($config['secure']) {
                $this->mailer->SMTPSecure = SMTP_SECURE;
            }
        }
        
        $config['name'] = $this->encodeName($config['name'], $config['encode']);
        $this->mailer->CharSet = $config['encode'];
        $this->mailer->Sender = $config['from'];
        
        // from
        if (!strlen($config['reply'])) {
            $this->mailer->SetFrom($config['from'], $config['name']);
        }
        else {
            $this->mailer->From = $config['from'];
            $this->mailer->FromName = $config['name'];
        }

        // content
        $view = $this->getView();
        $view->autoAssign($data);
        
        if (isset($config['body'])) {
            $body = $config['body'];
        }
        else {
            $template = str_replace('.', '_', $config['key']);
            $body = $view->fetch($this->tpldir . $template);
        }
        
        $subject = $config['subject'];
        $subject = $view->smarty->fetch('eval:' . $subject);
        $this->mailer->Subject = $this->encodeSubject($subject, $config['encode']);
        $this->mailer->Body = $this->encodeBody($body, $config['encode']);

        // send
        $this->addAddresses($config['to'], 'to');
        $this->addAddresses($config['cc'], 'cc');
        $this->addAddresses($config['bcc'], 'bcc');
        $this->addAddresses($config['reply'], 'reply');

        $rs = $this->handle($config);

        // admin
        if (isset($config['admin']['to']) && $config['admin']['to'] !== $config['to']) {
            $subject = $config['admin']['subject'];
            $template.= '_admin';
            $body = $view->fetch($this->tpldir . $template);
            $this->mailer->Subject = $this->encodeSubject($subject, $config['encode']);
            $this->mailer->Body = $this->encodeSubject($body, $config['encode']);
            $this->clearAddresses();
            $this->addAddresses($config['admin']['to'], 'to');
            $this->addAddresses($config['admin']['cc'], 'cc');
            $this->addAddresses($config['admin']['bcc'], 'bcc');
            $rs = $this->handle($config['admin']);
        }
        
        // return
        return $rs;
    }
    
    function handle($config) {
        ob_start();
        $rs = $this->mailer->Send();
        $msg = "Subject: " . $config['subject'] . PHP_EOL;
        $msg.= "To: " . $config['to'] . PHP_EOL;
        $msg.= "Cc: " . $config['cc'] . PHP_EOL;
        $msg.= "Bcc: " . $config['bcc'] . PHP_EOL;
        $msg.= ob_get_contents();
        $msg = str_replace('<br />', '', $msg);
        $msg = preg_replace("/[\r\n]+/", "\r\n", $msg);
        $this->log($msg);
        ob_end_clean();
        return $rs;
    }
    
    function addAddresses($data, $type = 'to') {
        if (!strlen($data)) {
            return false;
        }
        $adss = preg_split('/[\s]*\,[\s]*/', $data);

        switch($type) {
            case 'to':
                foreach ($adss as $v) {$this->mailer->AddAddress($v);}
                break;

            case 'cc':
                foreach ($adss as $v) {$this->mailer->AddCC($v);}
                break;
            
            case 'bcc':
                foreach ($adss as $v) {$this->mailer->AddBCC($v);}
                break;
                
            case 'reply':
                foreach ($adss as $v) {$this->mailer->AddReplyTo($v);}
                break;
        }
        
        return true;
    }
    
    function clearAddresses($type = 'to') {
        switch($type) {
            case 'to':
                $this->mailer->ClearAddresses();
                break;

            case 'cc':
                $this->mailer->ClearCCs();
                break;
            
            case 'bcc':
                $this->mailer->ClearBCCs();
                break;
                
            default:
                $this->mailer->ClearAddresses();
                $this->mailer->ClearCCs();
                $this->mailer->ClearBCCs();
                break;
        }
        
        return true;
    }

    function sendmail($to, $key, $data = null, $single = false) {
        // config
        $config = $this->getConfig($key);

        if ($single) {
            if (!isset($config['admin']['to'])) {
                $this->debug('admin.to is not found.', E_USER_WARNING);
                return false;
            }
            else {
                $config['to'] = $config['admin']['to'];
                unset($config['admin']);
                $config['bcc'] = $to;
            }
        }
        else {
            $config['to'] = $to;
        }

        return $this->send($config, $data);
    }
}

?>
