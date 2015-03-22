<?php
/**
 * wechat 综合示例
 */

// 官方PHP示例代码：http://mp.weixin.qq.com/mpres/htmledition/res/wx_sample.20140819.zip

// 认证 token
define("TOKEN", "weixin_test");
$wechatObj = new wechatCallbackapiTest();   // 实例化对象
$wechatObj->valid();    // 调用验证方法（此方法内调用回复方法）

class wechatCallbackapiTest
{
    function __construct() {
        require("tools/access_token.php");
        require("tools/iBotCloud.php");
    }

    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            $this->responseMsg();
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);   // 收到消息的类型
            //不同类型进行不同处理
            switch ($RX_TYPE)
            {
                case 'text':
                    $resultStr = $this->receiveText($postObj);
                    break;
                case 'voice':
                    $resultStr = $this->receiveText($postObj);
                    break;
                case 'event':
                    $resultStr = $this->receiveEvent($postObj);
                    break;
                case 'location':
                    // 获取access_token
                    $accessTokenObj = new accessToken();
                    $accessToken = $accessTokenObj->get();
                    // 获取用户基本信息 https://mp.weixin.qq.com/wiki/14/bb5031008f1494a59c6f71fa0f319c66.html
                    $openid = $postObj->FromUserName;
                    $data = file_get_contents("https://api.weixin.qq.com/cgi-bin/user/info?access_token={$accessToken}&openid={$openid}&lang=zh_CN");
                    $userData = json_decode($data, true);
                    // 获取昵称，还可以获取其它信息，详见官方文档。此功能可用于实现微信墙
                    $nickname = $userData['nickname'];
                    // 百度地图 web端URI API http://developer.baidu.com/map/index.php?title=uri/api/web
                    $locationX = $postObj->Location_X;
                    $locationY = $postObj->Location_Y;
                    $label = $postObj->Label;
                    $url = "http://api.map.baidu.com/place/search?query=".urlencode("美食")."&location={$locationX},{$locationY}&region=".urlencode($label)."&coord_type=wgs84&radius=1000&output=html&src=yourCompanyName%7cwechat";
                    // 这里回复图文消息效果更好
                    $contentstr = "{$nickname}，你好，已为你找到周边美食：{$url}。";
                    $resultStr = $this->ReplyText($postObj, $contentstr);
                    break;
                case 'image':
                    // 用户发送过来的图片再发送回去
                    $mediaId = $postObj->MediaId;
                    $resultStr = $this->ReplyImage($postObj, $mediaId);
                    break;
                default :
                    $contentstr = "你的".$RX_TYPE."信息已经收到";
                    $resultStr = $this->ReplyText($postObj, $contentstr);
                    break;
            }
            echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    // 用户事件
    private function receiveEvent($postObj)
    {
        $event = $postObj->Event;
        switch ($event) {
            case 'subscribe':	// 订阅
                // 存在EventKey说明是扫描带参数二维码事件
                if(isset($postObj->EventKey)){
                    $evenKey = $postObj->EventKey;  // 可通过key值进行相关统计
                    $contentstr = "扫描带参数二维码事件KEY值: {$evenKey}";
                    $resultStr = $this->ReplyText($postObj, $contentstr);
                }
                else{
                    $contentstr = "欢迎订阅";
                    $resultStr = $this->ReplyText($postObj, $contentstr);
                }
                break;
            case 'unsubscribe':	// 取消订阅
                $tousername = $postObj->FromUserName;
                // 可根据用户名进行删除（更新）用户信息等操作
                $resultStr = '';
                break;
            case 'CLICK':	// 自定义菜单
                $resultStr = $this->receiveText($postObj);	// 菜单点击事件
                break;
            case 'LOCATION':	// 用户上报地利位置
                /*
                 * 可用于实现签到功能
                 * 此功能除了需要有权限外，还需要手动在后台开启
                 * 根据经纬度获取地理位置的接口：http://developer.baidu.com/map/index.php?title=webapi/guide/webservice-geocoding
                 * */
                $contentstr = "地理位置上报成功，你的纬度为：{$postObj->Latitude}，你的经度为：{$postObj->Longitude}。";
                $resultStr = $this->ReplyText($postObj, $contentstr);
                break;
            case 'SCAN':
                $evenKey = $postObj->EventKey;  // 可通过key值进行相关统计
                $contentstr = "扫描带参数二维码事件KEY值: {$evenKey}";
                $resultStr = $this->ReplyText($postObj, $contentstr);
                break;
            default :
                $contentstr = "unknown";
                $resultStr = $this->ReplyText($postObj, $contentstr);
                break;
        }
        return  $resultStr;
    }

    // 把收到文本消息的回复封装起来
    private function receiveText($postObj)
    {
        $keyword = '';
        // 文本消息
        if (isset($postObj->Content)) {
            $keyword = trim($postObj->Content);
        }
        // 此处把收到的自定义菜单的点击事件也当作文本消息处理
        if (isset($postObj->EventKey)) {
            $keyword = trim($postObj->EventKey);
        }
        // 把语言识别的结果也当作文本处理
        if (isset($postObj->Recognition)) {
            $keyword = trim($postObj->Recognition);
        }

        if(!empty( $keyword ))
        {
            if($keyword == "news"){
                $news = array('title' => "单图文",
                    'description' => "图文描述",
                    'picurl' => "http://static.ukejisong.com/image/service/c5f5f36cf65d48deb59c46b70fd13bd4.jpg",
                    'url' => "http://www.ukejisong.com/",
                );
                $resultStr = $this->ReplyOneNews($postObj, $news);
            }
            elseif($keyword == "news2"){
                $news = array(
                    array(
                        'title' => "多图文1",
                        'picurl' => "http://static.ukejisong.com/image/service/c5f5f36cf65d48deb59c46b70fd13bd4.jpg",
                        'url' => "http://www.ukejisong.com/",
                    ),
                    array(
                        'title' => "多图文2",
                        'picurl' => "http://static.ukejisong.com/image/service/c5f5f36cf65d48deb59c46b70fd13bd4.jpg",
                        'url' => "http://www.ukejisong.com/",
                    ),
                    array(
                        'title' => "多图文2",
                        'picurl' => "http://static.ukejisong.com/image/service/c5f5f36cf65d48deb59c46b70fd13bd4.jpg",
                        'url' => "http://www.ukejisong.com/",
                    ),
                );
                $resultStr = $this->ReplyNews($postObj, $news);
            }
            elseif($keyword == "天气"){
                $apiUrl = "http://tq.360.cn/api/weatherquery/query?app=tq360&code=101010100&_jsonp=renderData&_=".time(); // 360天气接口 http://tq.360.cn
                // 其它天气接口：http://developer.baidu.com/map/carapi-7.htm  http://blog.csdn.net/zgyulongfei/article/details/7956118
                $weatherText = file_get_contents($apiUrl);
                if (strlen($weatherText) < 10) {
                    $contentStr = "出现错误，你可以稍后再试或把问题反馈给我们。";
                }
                else{
                    preg_match("/.*?(\{.+\}).*/", $weatherText, $matches);	// jsonp to json
                    $weatherJson = $matches[1];	// json数据
                    $weatherArray = json_decode($weatherJson, true);	// 转为数组

                    $RTtemperature = $weatherArray['realtime']['weather']['temperature']."℃";	// 实时温度
                    $RThumidity = $weatherArray['realtime']['weather']['humidity']."%";	// 实时湿度
                    $RTinfo = $weatherArray['realtime']['weather']['info'];	// 实时天气
                    $RTwind = $weatherArray['realtime']['wind']['direct'].$weatherArray['realtime']['wind']['power'];	// 实时风向
                    $pm25 = $weatherArray['pm25']['quality']." ".$weatherArray['pm25']['aqi'];	// 空气质量
                    $ganmao = $weatherArray['life']['info']['ganmao']['0']."，".$weatherArray['life']['info']['ganmao']['1'];	// 感冒指数
                    $ziwaixian = $weatherArray['life']['info']['ziwaixian']['0']."，".$weatherArray['life']['info']['ziwaixian']['1'];	// 紫外线指数

                    $weather7d = '☞<a href="http://mobile.weather.com.cn/city/101010100.html?data=7d">未来七天天气预报</a>';

                    $contentStr = sprintf("北京实时天气: %s，%s，%s，湿度: %s，空气质量: %s\n感冒指数: %s\n紫外线指数: %s\n%s", $RTinfo, $RTtemperature, $RTwind, $RThumidity, $pm25, $ganmao, $ziwaixian, $weather7d);
                }
                $resultStr = $this->ReplyText($postObj, $contentStr);
            }
            elseif($keyword == "日历"){
                // 上传日历图片获取 media id 然后回复给用户
                $mediaId = $this->uploadImg("../calendar.png");
                $resultStr = $this->ReplyImage($postObj, $mediaId);
            }
            elseif(strpos($keyword, "翻译") !== false){
                $word = $this->get_content($keyword, "翻译"); // 获得需要翻译的文本
                if (strlen($word)) {
                    $word = urlencode($word);   // url编码
                    // http://developer.baidu.com/wiki/index.php?title=帮助文档首页/百度翻译/翻译API
                    // client_id: 开发者在百度开发者中心注册得到的授权API key（免费）
                    $baidufanyiapiUrl = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=7goN3CD3OSoXT5tDShhIZU7a&q=".$word."&from=auto&to=auto";
                    $transResultJson = file_get_contents($baidufanyiapiUrl);    // 获得json数据
                    $transResultArray = json_decode($transResultJson, true);    // 转换为数组
                    $transWord = $transResultArray['trans_result'][0]['dst'];   // 翻译后的内容
                    $contentstr = "翻译结果: ".$transWord;
                }
                else{
                    $contentstr = "输入括号里的关键字【翻译+内容】即可智能翻译。如【翻译apple】、【翻译今天天气真好】。\n当需要翻译的语言为非中文时，会被翻译成中文。为中文时，会被翻译为英文。";
                }
                $resultStr = $this->ReplyText($postObj, $contentstr);
            }
            elseif(strpos($keyword, "快递") !== false){
                $kuaidiNum = $this->get_content($keyword, "快递"); // 获得快递单号
                if (strlen($kuaidiNum)) {
                    // 自动识别单号的api，为了更加稳定的使用建议去申请kuaidi100的api：http://www.kuaidi100.com/openapi/（免费）
                    $url = "http://m.ickd.cn/query.php?com=auto&no={$kuaidiNum}";
                    $infoJson = file_get_contents($url);
                    $infoArray = json_decode($infoJson, true);
                    $errCode = $infoArray['errCode'];
                    if($errCode == 0){
                        $status = $infoArray['status']; // 单号状态
                        $statusArray = array("在途，即货物处于运输过程中", "揽件，货物已由快递公司揽收并且产生了第一条跟踪信息", "疑难，货物寄送过程出了问题", "签收，收件人已签收", "退签，即货物由于用户拒签、超区等原因退回，而且发件人已经签收", "派件，即快递正在进行同城派件", "退回，货物正处于退回发件人的途中");
                        $statusStr = $statusArray[$status];	// 运单状态
                        $com = $infoArray['expTextName'];	// 公司
                        $mailNo = $infoArray['mailNo'];	// 单号
                        $data = $infoArray['data'];
                        $dataStr = '';
                        foreach ($data as $value) {
                            $dataStr .= $value['time']."\n";
                            $dataStr .= $value['context'];
                        }
                        unset($value);
                        $contentStr = sprintf("%s-%s\n状态: %s\n-----物流信息-----\n%s", $com, $mailNo, $statusStr, $dataStr);
                    }
                    elseif($errCode == 404){
                        $contentStr = "无法识别快递单号";
                    }
                    else{
                        $contentStr = "查询出错";
                    }
                }
                else{
                    $contentStr = "输入括号里的关键字【快递+单号】即可查询物流信息，如【快递966650707261】，不用指明具体快递公司哦。";
                }
                $resultStr = $this->ReplyText($postObj, $contentStr);
            }
            else{
                // 没有匹配到关键词调用机器人回答
                $iBot = new iBotCloud();
                $answerStr = $iBot->get_answer($keyword, $postObj->FromUserName);
                if(preg_match("/auth result/", $answerStr)){
                    $contentStr = "机器人歇菜了。";
                }
                else{
                    $contentStr = trim($answerStr);
                }
                $resultStr = $this->ReplyText($postObj, $contentStr);
            }
        }else{
            $resultStr = "Input something...";
        }

        return $resultStr;
    }

    // 在示例代码的基础上封装一下，实现一个回复文本的方法
    private function ReplyText($object, $contentstr)
    {
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $contentstr);
        return $resultStr;
    }

    // 回复格式为图文消息（多条,无Description），传入数组参数
    private function ReplyNews($object, $news)
    {
        $ArticleCount = count($news);	// 图文数量
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>%s</ArticleCount>
					<Articles>";

        for ($i=0; $i < $ArticleCount; $i++) { 		//多条图文消息组合
            $textTpl .= "
					<item>
					<Title><![CDATA[".$news[$i]['title']."]]></Title>
					<PicUrl><![CDATA[".$news[$i]['picurl']."]]></PicUrl>
					<Url><![CDATA[".$news[$i]['url']."]]></Url>
					</item>";
        }

        $textTpl .= "
					</Articles>
					</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $ArticleCount);
        return $resultStr;
    }

    // 回单图文消息
    private function ReplyOneNews($object, $news)
    {
        /*单图文图片大小推荐：360px * 200px*/
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>1</ArticleCount>
					<Articles>
					<item>
					<Title><![CDATA[%s]]></Title>
					<Description><![CDATA[%s]]></Description>
					<PicUrl><![CDATA[%s]]></PicUrl>
					<Url><![CDATA[%s]]></Url>
					</item>
					</Articles>
					</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $news['title'], $news['description'], $news['picurl'], $news['url']);
        return $resultStr;
    }

    // 回复图片消息
    private function ReplyImage($object, $mediaId)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[image]]></MsgType>
                    <Image>
                    <MediaId><![CDATA[%s]]></MediaId>
                    </Image>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $mediaId);
        return $resultStr;
    }

    private function uploadImg($file)
    {
        // 获取access_token
        $accessTokenObj = new accessToken();
        $accessToken = $accessTokenObj->get();
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$accessToken}&type=image";
        // 注意：此处需要使用绝对路径（将需要上传的图片放在固定文件夹下，方便处理）
        $data['media'] = "@".realpath($file);
        // CURL 上传文件
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	// 返回原生输出
        curl_setopt($ch, CURLOPT_HEADER, 0);	// 不显示header头
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // 不检查SSL证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        curl_close($ch);
        // 返回media_id
        // 上传完成后应该把图片存储进数据库，以便下次使用
        $resArray = json_decode($result, true);
        return @$resArray['media_id'];
    }
    /*
     * 如果公众号处于开发模式，普通微信用户向公众号发消息时
     * 微信服务器会先将消息POST到开发者填写的url上
     * 如果希望将消息转发到多客服系统，则需要开发者在响应包中返回MsgType为transfer_customer_service的消息
     * 微信服务器收到响应后会把当次发送的消息转发至多客服系统
     * */
    private function transfer_customer_service($object, $KfAccount = null)
    {
        // 默认不指定客服
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>";
        if($KfAccount !== null){
            $textTpl .= "<TransInfo>
                           <KfAccount><![CDATA[{$KfAccount}]]></KfAccount>
                        </TransInfo>";
        }
        else{
            $textTpl .= "<MsgType><![CDATA[transfer_customer_service]]></MsgType>
                        </xml>";
        }

        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $resultStr;
    }

    private function get_content($str, $keyword)	// 匹配字符串中关键词后面的内容
    {
        $pregStr = "/(?<=".$keyword.").*/u";	// 正则表达式语法，向后查找
        preg_match($pregStr, $str, $matches);	// 使用向后查找可以匹配例如“图书图书”的情况
        $content = trim($matches[0]);	// 去除前后空格
        // http://www.php.net/manual/zh/function.strpos.php
        if (strpos($content, '+') !== FALSE) {	// 如果获得的字符串前面有+号则去除
            $content = preg_replace("/\+/", '', $content, 1);	// 去除加号，且只去除一次，解决用户多输入+号的情况
            $content = trim($content);
        }
        return $content;
    }

    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    // 析构函数
    function __destruct() {
        unset($postObj);
    }
}
// end of main.php