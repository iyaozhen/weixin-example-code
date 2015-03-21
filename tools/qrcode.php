<?php
/**
 * 获取二维码
 */

require("access_token.php");

// 二维码类型 1: 永久 0: 临时
if(isset($_GET['type'])){
    $type = $_GET['type'];
}
else{
    $type = 0;
}
// 场景值ID
if(isset($_GET['id'])){
    $sceneId = $_GET['id'];
}
else{
    $sceneId = 1;
}

// https://mp.weixin.qq.com/wiki/18/28fc21e7ed87bec960651f0ce873ef8a.html
if($type == 0){
    // 临时二维码参数
    $post = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": ' . $sceneId . '}}}';
}
else{
    // 永久二维码参数
    $post = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id":  ' . $sceneId . '}}}';
}

/*
 * 资源流上下文参数
 * POST json数据
 * */
$options = array(
    'http' => array(
        'method'  => 'POST',
        'content' => $post,
        'header'=>  "Content-Type: application/json\r\n" .
            "Accept: application/json\r\n"
    )
);
$context  = stream_context_create($options);
// 获取access_token
$accessTokenObj = new accessToken();
$accessToken = $accessTokenObj->get();
$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$accessToken}";
$result = file_get_contents($url, false, $context);
$response = json_decode($result, true);

$ticket = $response['ticket'];

echo '<html>' .
    '<img src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket.'">' .
    '<p>二维码地址：<a href="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket.'">https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket.'</a></p>' .
    '</html>';