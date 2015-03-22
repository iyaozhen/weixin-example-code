<?php 

/**
* 智能机器人回复类
*
* @version 1.0
* @package http://cloud.xiaoi.com/
*/

class iBotCloud
{
    // 这里的key是临时的。请自行注册后获得，可试用，到期后需要购买
    private $app_key="1i0WYqi9LCOv";    // Key
    private $app_secret="JZEdu3AHsmjUADvpaEjB";     // Secret

    /*
    * 获取许可
    */
    private function get_xAuth()
    {
        // 官方提供的签名算法
        $app_key = $this->app_key;
        $app_secret = $this->app_secret;
        $realm = "xiaoi.com";
        $method = "POST";
        $uri = "/robot/ask.do";
        // nonce为40位随机数
        $nonce = '';
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';  
        for ( $i = 0; $i < 40; $i++) {
            $nonce .= $chars[ mt_rand(0, strlen($chars) - 1) ];         
        }
        $HA1 = sha1($app_key . ":" . $realm . ":" . $app_secret);
        $HA2 = sha1($method . ":" . $uri);
        $sign = sha1($HA1 . ":" . $nonce . ":" . $HA2);     // signature的值

        $xAuth = "app_key=\"$app_key\", nonce=\"$nonce\", signature=\"$sign\"";     // 注意：三个值都需要带上引号

        return $xAuth;
    }

    /*
    * 得到回答
    * $question：问题
    * $userId：用户的id，便于不同用户采取不同的问答策略，也可以用于统计uv
    */
    public function get_answer($question, $userId)
    {
        $xAuth = $this->get_xAuth();
        // http头部信息，X-Auth为必须项，用作验证
        $header = array (  
        "POST /robot/ask.do HTTP/1.1",  
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Host: nlp.xiaoi.com", 
        "Connection: Keep-Alive",
    //  "Content-Length: XXX",  // 此参数不好计算，不设置也可
        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",  
        "X-Auth: ".$xAuth, 
        "X-Requested-With: XMLHttpRequest"
        ); 
        $postData = "question=\"".$question."\"&userId=".$userId."&platform=weixin&type=0";     // 注意：question的值需要带上引号

        // 1. 初始化
        $ch = curl_init();
        // 2. 设置选项
        curl_setopt($ch, CURLOPT_URL, "http://nlp.xiaoi.com/robot/ask.do");     // 请求的地址
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 不自动返回内容
        curl_setopt($ch, CURLOPT_HEADER, 0);    // 不取得返回头信息
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  // 设置http头部信息
        curl_setopt($ch, CURLOPT_POST, 1);      // POST方法
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);   // 连接响应时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);  // 整个curl执行时间
        // 3. 执行并获取HTML文档内容
        $answerStr = curl_exec($ch);
        // 4. 释放curl句柄
        curl_close($ch);

        return $answerStr;
    }
}

// end of iBotCloud.php