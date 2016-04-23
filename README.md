#Lotter System
A Wechat lotter system for international culture festival, used PHP,and MySQL,API from wechat platform.
***
##TODO
* 图片存储，投票结果分析。（交给下一届国际文化节抽奖人员来改这段代码吧\_(:з」∠)_）


#Release Nodes
***

##1.1 - 2016-4-23
* 修复了获奖后无提醒信息的bug，需要建立客服消息发送，使用json封装，不允许直接以xml封装回复

##1.0 - 2016-4-22
* 本段代码继承于上届学长，去年部署于Sae服务器上，直接使用了新浪的云应用。可较为方便的搭建此应用，由于本人有AWS所以今年部署于AWS上。
* 由于新浪云对mysql自带了一个SaeMysql的类，本次自己写了一个SaeMysql的类来连接数据库。附赠网上爬到的SaeMysql源码。
* 在使用个人服务器时请注意php对mbstring的支持，请安装支持解决报错问题。
* 连接微信平台方法详见微信公众平台开发API。

##PS
* 附赠去年代码打包。