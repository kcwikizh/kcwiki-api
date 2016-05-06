# Kcwiki API 服务

Powered By Lumen

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->


- [1. Installation](#1-installation)
- [2. Configuration](#2-configuration)
  - [2.1 .env](#21-env)
  - [2.2 Writable folder](#22-writable-folder)
  - [2.3 Migrations](#23-migrations)
- [3. API Doc](#3-api-doc)
  - [3.1 Error response](#31-error-response)
  - [3.2 Subtitle](#32-subtitle)
    - [3.2.1 Version](#321-version)
    - [3.2.2 ShipID](#322-shipid)
    - [3.2.3 Diff](#323-diff)
    - [3.2.4 I18n](#324-i18n)
  - [3.3 Twitter](#33-twitter)
  - [3.4 Report API](#34-report-api)
  - [3.5 Maintenance](#35-maintenance)

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
	
	DB_CONNECTION=sqlite
	CACHE_DRIVER=file
	SESSION_DRIVER=file
	
	ADMIN_USERNAME=admin@xxx.xxx
	ADMIN_PASSWORD=somepassword

有关配置文件的说明请[参考](https://lumen.laravel.com/docs/5.2/configuration#environment-configuration)

### 2.2 Writable folder

需要将`storage`文件夹赋予写权限

	chmod -R a+w storage/

### 2.3 Migrations

在`.env`设置好数据库后，使用`php artisan migrate`来迁移数据库

	php artisan migrate

迁移的其他命令行选项请[参考](http://laravel.com/docs/migrations)


## 3. API Doc

### 3.1 Error response

API调用出现错误时的返回格式：

	{
		"result": "error",
		"reason": "xxx"
	}

**注：如果调用成功，返回结果并不一定带有result:success键值对**

### 3.2 Subtitle

	http://api.kcwiki.moe/subtitles

数据默认是简体中文，返回格式：  

	{
		"1": {								// 舰娘ID（非sortno）
			"1": "我是睦月！一鼓作气向前冲吧！", // 键值为voiceId
			...
		},
		...
		"version": "20160317"				// 数据版本号
	}

#### 3.2.1 Version

	http://api.kcwiki.moe/subtitles/version

返回最新的字幕数据版本号（数字对应字幕数据生成的年月日时），返回格式：

	{ "version": "20160317" }
	
#### 3.2.2 ShipID

根据舰娘编号(`api_id`)来获取字幕数据
	
	http://api.kcwiki.moe/subtitles/{shipID}

+ shipID: 舰娘编号ID（例如`1`返回睦月的语音）

返回格式：

#### 3.2.3 Diff

返回目标版本与最新版本的差分结果（POI字幕插件更新数据用）

	http://api.kcwiki.moe/subtitles/diff/{version}

+ version: 字幕数据版本号

返回格式同`/subtitles`

#### 3.2.4 I18n

字幕数据支持多语言，其接口如下（每个接口含义参考前述）：

	http://api.kcwiki.moe/subtitles/{lang}
	http://api.kcwiki.moe/subtitles/{lang}/{shipID}
	http://api.kcwiki.moe/subtitles/{lang}/diff/{version}

+ lang: 语言，目前暂时仅支持日语，值为`jp`

返回格式与简体中文的返回格式相同

### 3.3 Twitter

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

### 3.4 Report API

Kcwiki的poi报告插件的上传数据API，[详情见](https://github.com/kcwikizh/kcwiki-report)

### 3.5 Maintenance

开启/关闭维护模式，若开启，则字幕差分更新（`/subtitles/diff`）接口返回空值

	http://api.kcwiki.moe/maintenance/on/{password}
	http://api.kcwiki.moe/maintenance/off/{password}

+ `password`：对应.env配置文件中的`ADMIN_PASSWORD`