<?php
/**
 * wechat 关键词回复图文消息
 */

// 认证 token
define("TOKEN", "weixin_test");
$wechatObj = new wechatCallbackapiTest();   // 实例化对象
$wechatObj->valid();    // 调用验证方法（此方法内调用回复方法）

class wechatCallbackapiTest
{
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
            $keyword = trim($postObj->Content); // 收到的文本消息内容
            if(!empty( $keyword ))
            {
                if($keyword == "news"){
                    $news = array('title' => "单图文",
                        'description' => "图文描述",
                        'picurl' => "http://static.ukejisong.com/image/service/c5f5f36cf65d48deb59c46b70fd13bd4.jpg",
                        'url' => "http://www.ukejisong.com/",
                    );
                    $resultStr = $this->ReplyOneNews($postObj, $news);
                    echo $resultStr;
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
                    echo $resultStr;
                }
                elseif($keyword == "text"){
                    $contentStr = "回复文本消息";
                    $resultStr = $this->ReplyText($postObj, $contentStr);
                    echo $resultStr;
                }
                else{
                    $contentStr = "无匹配关键词";
                    $resultStr = $this->ReplyText($postObj, $contentStr);
                    echo $resultStr;
                }
            }else{
                echo "Input something...";
            }

        }else {
            echo "";
            exit;
        }
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
}
// end of news.php