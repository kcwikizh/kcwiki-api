# Kcwiki API 服务

Powered By Lumen

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
  - [4.1 Error response](#41-error-response)
  - [4.2 Subtitle](#42-subtitle)
    - [4.2.1 Version](#421-version)
    - [4.2.2 ShipID](#422-shipid)
    - [4.2.3 Diff](#423-diff)
    - [4.2.4 I18n](#424-i18n)
  - [4.3 Twitter](#43-twitter)
    - [4.3.1 Plain Text](#431-plain-text)
  - [4.4 Start2 Data](#44-start2-data)
  - [4.5 Report API](#45-report-api)
  - [4.6 Maintenance Mode](#46-maintenance-mode)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## 1. Installation

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

需要在使用[poi-plugin-dev-helper](https://github.com/grzhan/poi-plugin-dev-helper)等方法上传start2数据文件后使用，或者手工将`api_start2.json`放到`storage/app/`目录下

这个命令将会影响`ship/`、`slotitem/`、`map/`等API的使用与更新


### 3.3 parse:db

	php artisan parse:db {option}

基于数据库（主要为插件报告数据）生成API源数据

目前主要是插件采集的初始装备数据

`option` 对应的可选参数如下：

+ `initequip` : 根据数据库生成舰娘初始装备数据（对应 API `init/equip`）
+ `enemy` : 根据数据库生成深海舰船数据（现在主要也是初始装备，对应 API `init/equip/enemy`）

### 3.4 parse:lua

	parse:lua {option}

基于舰娘百科 Mediawiki Lua Table 生成API源数据

`option` 对应的参数如下：

+ `slotitem` ： 根据舰娘百科[[模块:舰娘装备数据]]获取装备的中文译名


## 4. API Doc

### 4.1 Error response

API调用出现错误时的返回格式：

	{
		"result": "error",
		"reason": "xxx"
	}

**注：如果调用成功，返回结果并不一定带有result:success键值对**

### 4.2 Subtitle

	http://api.kcwiki.moe/subtitles

数据默认是简体中文，返回格式：  

	{
		"1": {									// 舰娘ID（非sortno）
			"1": "我是睦月！一鼓作气向前冲吧！", // 键值为voiceId
			...
		},
		...
		"version": "2016050905"				// 数据版本号
	}

#### 4.2.1 Version

	http://api.kcwiki.moe/subtitles/version

返回最新的字幕数据版本号（数字对应字幕数据生成的年月日时），返回格式：

	{ "version": "2016050905" }
	
#### 4.2.2 ShipID

根据舰娘编号(`api_id`)来获取字幕数据
	
	http://api.kcwiki.moe/subtitles/{shipID}

+ shipID: 舰娘编号ID（例如`1`返回睦月的语音）

返回格式：

#### 4.2.3 Diff

返回目标版本与最新版本的差分结果（POI字幕插件更新数据用）

	http://api.kcwiki.moe/subtitles/diff/{version}

+ version: 字幕数据版本号

返回格式同`/subtitles`

#### 4.2.4 I18n

字幕数据支持多语言，其接口如下（每个接口含义参考前述）：

	http://api.kcwiki.moe/subtitles/{lang}
	http://api.kcwiki.moe/subtitles/{lang}/{shipID}
	http://api.kcwiki.moe/subtitles/{lang}/diff/{version}

+ lang: 语言，目前暂时仅支持日语，值为`jp`

返回格式与简体中文的返回格式相同

### 4.3 Twitter

返回舰娘官推数据（带翻译）

	http://api.kcwiki.moe/tweet/{count}

+ count: 请求的官推条数，例如20

返回格式：

	{
		{
			"zh": "xxx",					// 推特内容（中文）
			"jp": "xxx",					// 推特内容（日文）
			"date": "2016-05-06 08:55:30"	// 日期
		}
		...
	}

#### 4.3.1 Plain Text

默认官推的输出格式为HTML，如果想要提取后的纯文本，可以请求：

	http://api.kcwiki.moe/tweet/plain/{count}

返回格式与之前相同

### 4.4 Start2 Data

返回`api_start2.json`的原始数据

	http://api.kcwiki.moe/start2

### 4.5 Report API

Kcwiki的poi报告插件的上传数据API，[详情见](https://github.com/kcwikizh/kcwiki-report)

### 4.6 Maintenance Mode

开启/关闭维护模式，若开启，则字幕差分更新（`/subtitles/diff`）接口返回空值

	http://api.kcwiki.moe/maintenance/on/{password}
	http://api.kcwiki.moe/maintenance/off/{password}

+ `password`：对应.env配置文件中的`ADMIN_PASSWORD`