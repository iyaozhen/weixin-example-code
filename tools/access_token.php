<?php
/**
 * 获取access_token
 */

class accessToken
{
    // 公众平台后台获取
    private $appid = "wx28c82cbb31934ef0";
    private $appsecret = "1bb990600b25bc3cda1a199cc0e140af";

    public function get()
    {
        // 尝试从文件里面读取token
        $data = file_get_contents("../access_token.txt");
        if(strlen($data) > 0){
            $accessTokenArray = json_decode($data, true);
            $expiresTime = $accessTokenArray['expires_time'];    // 过期时间
            $now = time();
            if($expiresTime - $now > 10){
                $accessToken = $accessTokenArray['access_token'];   // access_token
            }
            else{
                // 如果token过期 重新获取
                $accessToken = $this->save();
            }
        }
        else{
            // 如果文件为空 重新获取
            $accessToken = $this->save();
        }

        return $accessToken;
    }

    // 存储token
    private function save()
    {
        // 获取Token https://mp.weixin.qq.com/wiki/11/0e4b294685f817b95cbed85ba5e82b8f.html
        $accessTokenJson = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}");
        $accessTokenArray = json_decode($accessTokenJson, true);
        $accessToken = $accessTokenArray['access_token'];   // access_token
        $expiresTime = time() + $accessTokenArray['expires_in'];    // 过期时间 = 当前时间 + 7200s
        // 把获取的token存起来
        $saveToken = array(
            "access_token" => $accessToken,
            "expires_time" => $expiresTime
        );
        // 因为Token有时间限制，且获取的次数有限，所以需要存起来
        file_put_contents("../access_token.txt", json_encode($saveToken));

        // 并返回获取到的token
        return $accessToken;
    }
}

// end of access_token.php