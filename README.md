### 开源的极光主题，做了前端分离部署的简易化处理

### 项目原地址>

 https://github.com/krsunm/Aurora

 致谢KX DESIGN开源主题代码



### 部署流程>

1.新建网站，将源码放在根目录下解压

2.访问域名，填入后端网址

3.logo图片命名为facicon.png放在根目录下

4.本地验证码无法加载，只适配google和cloudflare

5.更换v2board支付回调页面以适配前端分离

  1>文件位于app/Services/PaymentService.php
  
  2>找到
  
```
'return_url' => url('/#/order/' . $order['trade_no']),
```

  3>改为
  
```
//'return_url' => url('/#/order/' . $order['trade_no']),    //返回订单页(原主题)
'return_url' => $_SERVER['HTTP_ORIGIN'] . "/index.html#/stage/dashboard",    //返回用户首页(aurora主题)
```

  4>测试回调

6.更换icon后无变化时修改 index.html 中 "<link rel="icon" href="/favicon.png?v=20231102012645" />"的版本号

7.自定义api路径时，搜索 /static/js 路径下全部文件的 /api/v1 字段并修改为自定义字段，例如 /aurora

8.需要将订阅链接的api路径改为和前端显示同步时，更改 /static/custom.js 中的 "const NEW_PREFIX = '';"， 在单引号中填入自定义字段，例如 /aurora。之后修改 index.html 中 custom.js 的版本号。
