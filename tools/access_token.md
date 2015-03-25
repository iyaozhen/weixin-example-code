此章节介绍如何获取和存储 access_token

access_token 在微信的接口中占有重要位置，许多接口都需要通过 access_token 认证。
access_token 是公众号的全局唯一票据，公众号调用各接口时都需使用 access_token。开发者需要进行妥善保存。
access_token 的存储至少要保留512个字符空间。access_token 的有效期目前为2个小时，需定时刷新，重复获取将导致上次获取的 access_token 失效。所以我们需要一个中间层，用来维护 access_token。
这里的思路非常简单，用一个文件存储 access_token 和到期时间（unix时间戳）。
当文件为空或者时间已过期则重新获取 access_token 并写进文件。

需要获取 access_token 时只要调用 accessToken 类的 get() 方法即可。