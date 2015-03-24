此章节介绍如何接入微信公众平台，并回复文本消息。

按照官方接入指南，在后台设置 URL 和 TOKEN。
官方 PHP 示例代码：http://mp.weixin.qq.com/mpres/htmledition/res/wx_sample.20140819.zip
下载代码后填入后台设置的 TOKEN 即可验证通过。

在开发者首次提交验证申请时，微信服务器将发送GET请求到填写的URL上，并且带上四个参数（signature、timestamp、nonce、echostr），开发者通过对签名（即signature）的效验，来判断此条消息的真实性。
通过 TOKEN、timestamp、 nonce 可计算出 signature，和微信服务器传过来的 signature 做比较，若相等则验证通过。由此可见 TOKEN 非常重要，若被人知晓可能会被别人攻击。

此后，每次开发者接收用户消息的时候，微信也都会带上前面三个参数（signature、timestamp、nonce）访问开发者设置的URL，开发者依然通过对签名的效验判断此条消息的真实性。效验方式与首次提交验证申请一致。

认证通过后调用 responseMsg() 方法回复用户消息。我们这里稍稍修改一下官方示例代码实现一个回音壁的功能，用户发送什么文本，我们就回复什么文本。