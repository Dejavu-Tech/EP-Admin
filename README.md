
<img src="https://image.ch871.com/backscreen.png"/>
<div align="center">
  <br/>
  <img align="center" src="https://image.ch871.com/new_logo.png" style="width:100px"/>
  <h2>EATER PLANET<br/>吃货星球 v5.x</h2>
</div>
<h1 align="center">社区团购后端/CMS</h1>
<p align="center">
  <a href="http://wpa.qq.com/msgrd?v=3&uin=142997&site=qq&menu=yes"><img alt="Albert.Z" src="https://img.shields.io/badge/Author-Albert.Z-blue.svg"></a>
  <a href="https://github.com/Dejavu-Tech/EP-Admin/License"><img alt="GPL-v3协议" src="https://img.shields.io/badge/GPL-v3-red"></a>
  <a href="https://github.com/Dejavu-Tech/EP-Admin/releases"><img alt="最新版本" src="https://img.shields.io/badge/version-5.0.0-brightgreen"></a>
  <a href="https://img.shields.io/github/stars/Dejavu-Tech/EP-Admin.svg?style=social&label=Stars"><img alt="笔芯" src="https://img.shields.io/github/stars/Dejavu-Tech/EP-Admin.svg?style=social&label=Stars"></a>

  <br/>
  <a href="https://github.com/Dejavu-Tech/EP-Admin/issues/new/choose">报告 Bug</a>&nbsp;·&nbsp;<a href="https://github.com/Dejavu-Tech/EP-Admin/new/choose">性能优化建议</a>
</p>


## 🌻前端传送门 ╰( ´・ω・)つ──☆👉🏻️➡<a href="https://github.com/Dejavu-Tech/EP-WechatApp">EP-WechatApp</a>


## 💾组织结构
``` lua
EATER-PLANET
├── EP-WechatApp -- 微信小程序前端 5.x
├── EP-Admin -- 微信小程序后端/CMS 5.x
├── EP-WeApp -- 新版微信小程序前端 ^6.0（即将发布）
├── EP-AliApp -- 新版支付宝小程序前端 ^6.0（即将发布）
├── EP-Commerce -- 新版后端 ^6.0（重构）
├── EP-Documents -- 部署及运营文档
├── Rexo-UI -- 新版后端CMS模板
└── EP-UI -- 移动端组件库（即将发布）
```

## 🧬后端项目结构
``` lua
EP-Admin
├── assets -- 公共样式资源
├── Common -- 公共函数
├── Data -- 支付日志
├── Modules -- 后台模块/控制器/配置文件
├── Runtime -- 缓存目录
├── Themes -- CMS前台
├── ThinkPHP -- TP框架核心目录
├── Uploads -- 上传文件及二维码生成目录
├ ep.php CMS入口
├ index.php 公共入口
├ install.php 安装入口
└ wxapp.php 前端入口
```

## ✨功能特性
- 限时秒杀、整点秒杀、优惠券、兑换码、礼品卡、预售、接龙、签到、积分、拼团、菜谱、礼品卡、虚拟销量、虚拟评价等营销模式
- 商品自定义售卖时间、多规格、多分类、标签、置顶、送达时间、满减、新人、会员专享、限购、限制地域/距离、门店、团长、自提点等多种功能
- 自定义专题活动、单团长（非社区团购模式）、快递模式
- 团长多等级、分销、分佣、提成等多功能
- 客户等级、充值、会员卡、多级分销、分享、邀请新人、加群、海报等裂变功能
- 免登陆后置模式
- 支持易联云、飞鹅、美团云小票打印机
- 支持快递鸟、蜂鸟、美团、顺丰同城、达达等第三方配送/快递API
- 子商户自动生成独立后台可独立上货、结算、平台服务费提取
- 类美团/联联周边游到店核销二维码（虚拟商品）功能
- 后台订单统计、财务数据、分销列表、佣金提成统计、门店核销统计
- 订单一键发货、配送单一键生成、货物一键送达、运费模板自定义
- 独立的配送员、核销员、配送路线模块
- 支持公众号关注组件、抖音/本地视频链接
- 支持redis解决高并发场景稳定输出
- 支持微信支付、余额支付、企业付款
- 七牛云、腾讯云COS、阿里云OSS对象存储远程附件
- 前端一体化集成商户、团长、配送、拼团、接龙模块
- 微信订阅消息推送
- 支持小程序直播、交易组件、微信商店、微信视频号

## 🐶流程简图
<div align="center">
  <img src="https://image.ch871.com/flow.png"/>
</div>


## 📺DEMO
#### 后端 ➡️<a href="https://demo.ch871.com">https://demo.ch871.com</a>


#### 前端 微信搜索`霸气妖吃货星球`或扫码：
<img src="https://image.ch871.com/ep-qrcode.png" width="300px" /> 


## 🖼️截图
<img src="https://image.ch871.com/backall-screen2.png"/>

- 原图猛戳 👉🏻 <a target="_blank" href="https://image.ch871.com/backall-screen2.png">✿✿✿(ノ◕ω◕)ノ</a>

## 🦍部署
- 后端基础预览需求
#### LNMP一键安装包
````
yum install screen
screen -S lnmp
````
#### 或宝塔Linux面板
````
yum install -y wget && wget -O install.sh http://download.bt.cn/install/install_6.0.sh && sh install.sh ed099927
````
- 选择PHP版本为`5.6`,MySQL版本>=`5.6`
- 网站目录上传本仓库源码或
````
git@github.com:Dejavu-Tech/EP-Admin.git
````
- 打开浏览器输入网址或IP地址开始安装即可
### ⭕正常商用运营请参阅<a href="https://docs.ch871.com">部署文档</a>

## 🔎文档源码
<a target="_blank" href="https://github.com/Dejavu-Tech/EP-Documents">EP-Documents</a>


## 🔨版本说明
本项目源码为基础版，未包含拼团、分销、接龙、预售等营销功能，点⭐并同意我公司《服务协议》和《隐私条款》即可商用

## 👽联系
全功能版商用授权及部署请联系
- QQ:`142997`<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=142997&site=qq&menu=yes"><img width=80px align=center src="https://image.ch871.com/qq-contact .png"/></a>
- 企业微信:
<br/>
<img src="https://image.ch871.com/qywx-contact .png" width="300px" />

## 📜许可证 [GPL-3.0](https://github.com/Dejavu-Tech/EP-Admin/License)

## 🌎️软件著作权及其他版权所有
<img src="https://image.ch871.com/rexotech.png" width="25px" align="left"/> 
&nbsp;Copyright © 2019-2023 Dejavu Tech. (YN) Co., Ltd. <a href="https://www.rexotech.cn">官网</a>
