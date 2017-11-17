# Kcwiki API 服务

API for kancolle game data. Powered By Lumen

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->


- [1. Installation](#1-installation)
- [2. Configuration](#2-configuration)
  - [2.1 .env](#21-env)
  - [2.2 Writable folder](#22-writable-folder)
  - [2.3 Migrations](#23-migrations)
- [3. Commands](#3-commands)
  - [3.1 queue:listen](#31-queuelisten)
  - [3.2 parse:start2](#32-parsestart2)
  - [3.3 parse:db](#33-parsedb)
  - [3.4 parse:lua](#34-parselua)
- [4. API Doc](#4-api-doc)
- [5. FAQ](#5-faq)
  - [5.1 在使用 Artisan 时出现形如 `Class App\Console\Commands\ParseStart2 does not exist` 的错误？](#51-%E5%9C%A8%E4%BD%BF%E7%94%A8-artisan-%E6%97%B6%E5%87%BA%E7%8E%B0%E5%BD%A2%E5%A6%82-class-app%5Cconsole%5Ccommands%5Cparsestart2-does-not-exist-%E7%9A%84%E9%94%99%E8%AF%AF%EF%BC%9F)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## 1. Installation

请事先部署 PHP5.4+ 、 Mysql 、 Redis（可选）

安装PHP依赖库：

	composer install

## 2. Configuration

### 2.1 .env

通过在根目录创建`.env`文件进行PHP应用的基本设置

`.env`配置文件示例：


	APP_ENV=local
	APP_DEBUG=true
	APP_KEY=somesecretkey
	APP_HOST=http://api.kcwiki.moe

	AUTH_DRIVER=eloquent
	AUTH_MODEL=\App\User
	AUTH_TABLE=users

	DB_CONNECTION=mysql
	DB_DATABASE=xxx
	DB_USERNAME=xxx
	DB_PASSWORD=xxx
	CACHE_DRIVER=redis
	SESSION_DRIVER=redis
	QUEUE_DRIVER=redis

	ADMIN_USERNAME=foobar@kcwiki.moe
	ADMIN_PASSWORD=somepassword

有关配置文件的说明请[参考](https://lumen.laravel.com/docs/5.2/configuration#environment-configuration)

> 注意 APP_KEY 的配置，默认可能需要 32 位。

### 2.2 Writable folder

需要将`storage`文件夹赋予写权限

	chmod -R a+w storage/

### 2.3 Migrations

在`.env`设置好数据库后，使用`php artisan migrate`来迁移数据库

	php artisan migrate

迁移的其他命令行选项请[参考](http://laravel.com/docs/migrations)

## 3. Commands

基于 Laravel Artisan 的命令，用以处理游戏数据、清理缓存、创建定时任务等（其实就是本地跑些PHP脚本）

下面介绍本站自定义的命令，以及日常维护需要用到框架自带命令

所有命令都要求在 Web 网站根目录下运行

在项目迭代时，可能会有运行新增命令但找不到对应依赖类的情况，因此在生产服务器使用 git 拉取更新后，可能需要使用`composer dumpautoload -o`来刷新PHP类映射表

详细[参考](http://www.golaravel.com/laravel/docs/5.1/artisan/)

### 3.1 queue:listen

	php artisan queue:listen

监听并处理异步任务队列，例如在上传完start2数据结束后，服务器将会把`parse:start2`作为任务加入到队列中，等待之后异步执行

可以在`.env`设置任务队列的驱动（`QUEUE_DRIVER`），默认为 redis

建议把本命令丢到`supervisor`里长期监听

详细[参考](http://lumen.laravel-china.org/docs/queues)


### 3.2 parse:start2

	php artisan parse:start2

结合 [kcdata](https://github.com/kcwikizh/kcdata) 与`api_start2.json`生成各个API需要的ship、slotitem、map等源数据

需要从`api.kcwiki.moe/start2`或者使用[kcwiki-scripts](https://github.com/grzhan/kcwiki-scripts)来获取start2数据文件，并以`api_start2.json`命名放在`storage/app`文件夹下。

这个命令将会影响`ship/`、`slotitem/`、`map/`等API的使用与更新


### 3.3 parse:db

	php artisan parse:db {option}

基于数据库（主要为插件报告数据）生成API源数据

目前主要是插件采集的初始装备数据

`option` 对应的可选参数如下：

+ `initequip` : 根据数据库生成舰娘初始装备数据（对应 API `init/equip`）
+ `enemy` : 根据数据库生成深海舰船数据（现在主要也是初始装备，对应 API `init/equip/enemy`）

### 3.4 parse:lua

	php artisan parse:lua {option}

基于舰娘百科 Mediawiki Lua Table 生成API源数据

`option` 对应的参数如下：

+ `slotitem` ： 根据舰娘百科[[模块:舰娘装备数据]]获取装备的中文译名


## 4. API Doc

本服务的具体开放API请[参考](https://github.com/kcwikizh/kcwiki-api/wiki)

## 5. FAQ

### 5.1 在使用 Artisan 时出现形如 `Class App\Console\Commands\ParseStart2 does not exist` 的错误？

尝试运行命令 `composer dumpautoload -o`


## __重要__：expedition 就是 battle （目前
