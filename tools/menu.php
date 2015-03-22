<?php
require("access_token.php");

// 创建：create  删除：del  查询：get
$type = isset($_GET['type']) ? $_GET['type'] : 'get';
$accessTokenObj = new accessToken();
$accessToken = $accessTokenObj->get();
//$type = 'get';
if($type == 'create'){
    // json格式，结构类似数组
    $menuJson = '{
        "button": [
            {
                "name": "扫码",
                "sub_button": [
                    {
                        "type": "scancode_waitmsg",
                        "name": "扫码带提示",
                        "key": "rselfmenu_0_0"
                    },
                    {
                        "type": "scancode_push",
                        "name": "扫码推事件",
                        "key": "rselfmenu_0_1"
                    }
                ]
            },
            {
                "name": "发图",
                "sub_button": [
                    {
                        "type": "pic_sysphoto",
                        "name": "系统拍照发图",
                        "key": "rselfmenu_1_0"
                     },
                    {
                        "type": "pic_photo_or_album",
                        "name": "拍照或者相册发图",
                        "key": "rselfmenu_1_1"
                    },
                    {
                        "type": "pic_weixin",
                        "name": "微信相册发图",
                        "key": "rselfmenu_1_2"
                    }
                ]
            },
            {
                "name": "菜单",
                "sub_button": [
                    {
                        "name": "发送位置",
                        "type": "location_select",
                        "key": "rselfmenu_2_0"
                    },
                    {
                       "type": "view",
                       "name": "跳转URL",
                       "url": "http://www.ukejisong.com/"
                    },
                    {
                       "type": "click",
                       "name": "点击推事件",
                       "key": "text"
                    }
                ]
            }
        ]
    }';
    $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$accessToken}";
    $options = array(
        'http' => array(
            'method'  => 'POST',
            'content' => $menuJson,
            'header'=>  "Content-Type: application/json\r\n" .
                "Accept: application/json\r\n"
        )
    );
    $context  = stream_context_create($options);
// 获取access_token
    $accessTokenObj = new accessToken();
    $accessToken = $accessTokenObj->get();
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    if($response['errcode'] == 0){
        echo "菜单创建成功";
    }
    else{
        echo $response['errmsg'];
    }
}
elseif($type == 'del'){
    $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$accessToken}";
    $result = file_get_contents($url);
    $response = json_decode($result, true);

    if($response['errcode'] == 0){
        echo "菜单创建成功";
    }
    else{
        echo $response['errmsg'];
    }
}
else{
    $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$accessToken}";
    $result = file_get_contents($url);
    $response = json_decode($result, true);

    if(isset($response['errcode'])){
        echo $response['errmsg'];
    }
    else{
        // 数组样式方便查看
        echo @json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}

// end of menu.php