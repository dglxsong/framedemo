<?php

class BaseController extends BraveController {

    // HTTP 请求相关方法
    function getRequest($url, $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        ob_start();
        curl_exec($ch);
        $rs = ob_get_contents();
        curl_close($ch);
        ob_end_clean();
        return $rs;
    }

    //微信相关方法

    /**
     * @desc 获取微信授权 Access Token
     */
    function getWXAccessToken($appID = '', $appsecret = '', $code = '') {
        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appID}&secret={$appsecret}&code={$code}&grant_type=authorization_code";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_url);
        ob_start();
        curl_exec($ch);
        $token_data = ob_get_contents();
        curl_close($ch);
        ob_end_clean();

        return json_decode($token_data, true);
    }

    /**
     * 刷新 Access Token
     */
    function refreshWXAccessToken() {
        //get
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . WX_APP_ID .'&secret=' . WX_APP_SECRET;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        
        $jsoninfo = json_decode($output, true);
        $access_token = $jsoninfo['access_token'];
        $expires_in = $jsoninfo['expires_in'];

        $systemModel = $this->getModel('System');

        $systemModel->saveAccessToken($jsoninfo);

        return $access_token;
    }

    //发送模板消息
    function pushWXTemplateMessage($data){
         $systemModel = $this->getModel("System");
         $accessToken = $systemModel->getAccessToken();
        
         for($i = 0; $i < 3; $i++) {//重试机制
             $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $accessToken['access_token'];
             $res = $this->httpPost($url, $data);
             $result = json_decode($res, TRUE);
             if(!isset($result['errcode']) || !in_array($result['errcode'], array('41001', '40001', '42001'))) {
                 return $result;
             } else {
                 $accessToken['access_token'] = $this->refreshWXAccessToken();
             }
         }

         return false;
    }

    /**
     * @desc 获取授权后的微信用户信息
     */
    function getWXUserInfo($access_token = '', $open_id = '') {
        $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
        
        $info_data = $this->getRequest($info_url);
        
        return json_decode($info_data, true);
    }

    /**
     * 微信支付
     */
    function wxPay($open_id, $order_title, $out_trade_no, $total_fee) {
        include_once(LIBRARY . 'WxPay/WxPayPubHelper.php');

        //返回微信支付数据
        $wx_app_id = WX_APP_ID;
        $wx_app_secret = WX_APP_SECRET;
        $wx_mchid = WX_MCHID;
        $wx_key = WX_PAY_KEY;
        $wx_notify_url = WX_NOTIFY_URL;

        $total_fee = $total_fee * 100;

        //设置统一支付接口参数
        $unifiedOrder = new UnifiedOrder_pub($wx_app_id, $wx_app_secret, $wx_mchid, $wx_key, WXPAY_CERT, WXPAY_KEY_CERT);
        $unifiedOrder->setParameter("openid", $open_id);
        $unifiedOrder->setParameter("body", $order_title);//商品描述
        $unifiedOrder->setParameter("notify_url", $wx_notify_url);//通知地址 
        $unifiedOrder->setParameter("trade_type", "JSAPI");//交易类型
        $unifiedOrder->setParameter("out_trade_no", $out_trade_no);
        $unifiedOrder->setParameter("total_fee", $total_fee);//总金额

        $prepayResult = $unifiedOrder->getPrepayId();

        //获取欲支付ID
        $prepay_id = 0;
        if($prepayResult['prepay_id']) {
            $prepay_id = $prepayResult["prepay_id"];

            //使用jsapi接口
            $jsApi = new JsApi_pub($wx_app_id, $wx_app_secret, $wx_mchid, $wx_key, WXPAY_CERT, WXPAY_KEY_CERT);

            //设置prepay_id
            $jsApi->setPrepayId($prepay_id);

            //返回参数json数据
            $jsApi->getParameters();
            $jsApiParameters = $jsApi->getJsApiObj();

            return $jsApiParameters;

        } else {
            $this->log('BaseController -> wxPayApp 获取支付ID失败：' . print_r(
                array(
                    'body' => $order_title,
                    'notify_url' => $wx_notify_url,
                    'out_trade_no' => $out_trade_no,
                    'total_fee' => $total_fee,
                    'result' => $prepayResult
                )
            , true));
        }

        return false;
    }
}

?>
