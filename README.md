### 开源的极光主题，做了前端分离部署的简易化处理

部署流程>

1.新建网站，将源码放在根目录下解压

2.访问域名，填入后端网址

3.logo图片命名为facicon.png放在根目录下

4.本地验证码无法加载，只适配google和cloudflare

5.更换v2board支付回调页面以适配前端分离>
  1>文件位于app/Services/PaymentService.php
  2>找到>>>'return_url' => url('/#/order/' . $order['trade_no']),
  3>改为>>>
  
```
            //'return_url' => url('/#/order/' . $order['trade_no']),    //返回订单页(原主题)
            'return_url' => $_SERVER['HTTP_ORIGIN'] . "/index.html#/stage/dashboard",    //返回用户首页(aurora主题)
```
  4>测试回调
