此章节介绍基于百度地图的周边美食搜索功能的实现

主要使用了百度地图 web端URI API（http://developer.baidu.com/map/index.php?title=uri/api/web）。
按照文档要求构造 url（主要代码：responseMsg() 方法中的 case 'location'），然后发送给用户，用户点击 url 后可直接进入结果显示页面。
这里为了隐藏 url 细节，回复图文消息用户体验更好。还可以和自定义菜单结合，实现一键周边美食搜索、一键回家等功能。

代码中还简单的使用了获取用户信息的接口，此接口应用范围不止于此，比如比较火的微信墙就主要通过此接口来实现。