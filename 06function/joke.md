此章节介绍笑话功能的实现

有些时候我们发现别人的网站上有一些有意思的东西，但网站又没提供接口，或者说接口是收费的。
我们也只需要获取一小部分的信息，那么可以试试把网站的内容爬取下来（类似搜索引擎的爬虫，但简单很多）。本次示例是爬取的糗事百科网站。

主要思路就是获取整个页面的 html（DOM结构）-> 使用 php 的 Html DOM 解析库（解析操作类似于 Jquery）-> 数据加工、拼接 -> 返回结果给用户
当然，有些网站为了保护数据做了防抓取，这是就需要使用 curl 等库来尽量模拟真实的请求来获取数据。
一般来说，抓取手机版的站点比 PC 版的简单，有些手机版的网站（APP）甚至会直接暴露出接口。

本章节的代码见根目录下的 main.php 文件中的笑话功能。