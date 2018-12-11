# laravel_12306
微信小程序 12306抢票系统
##系统组成

| 程序 | 版本 | 
| --- | --- |
| Linux |  Centos7.5 | 
| Mysql | 5.6 | 
|  PHP| 7.2| 
|  Python| 3.6 | 
|微信小程序|官方|
 
##整体流程
1. 在微信小程序获取列车表
2. 登陆12306账号->获取联系人->选择需要订票的人(多选)
3. 将选择的车次、座位、联系人...信息POST到PHP
4. php做校验和关联储存，将该条主键POST到Python抢票接口
5. Python根据ID查询mysql,进行Python流程


##Python流程

1. 用户登陆
1. 正在初始化订票页面...
2. 查询余票...
3. 正在确认用户登录状态...
4. 正在提交车票预定信息...
5. 正在确认预订信息...
6. 正在确认订单信息...
7. 正在提交预订请求...
8. 正在确认配置信息...
9. 排队等待中获取Orderid...
10. 抢票成功!
11. 正在请求预订结果... 
12. 模拟支付界面，订单已经完成提交
13. 通知PHP订票成功



##PHP接口
1. 订票成功 Success ，发短信 or 小程序推送
2. 订票失败 Error 原因, 发短信 or 小程序推送
3. 新增 or 更新 订票相关信息储存
未完待续


##小程序界面
![](https://ws1.sinaimg.cn/large/006Ziquvly1fy3avosla1j30760763zh.jpg)


