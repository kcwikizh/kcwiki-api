# Kcwiki API 服务

Powered By Lumen

## Installation

安装PHP依赖库：

	composer install

## Configuration

### .env

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

### Writable folder

需要将`storage`文件夹赋予写权限

	chmod -R a+w storage/

### Migrations

在`.env`设置好数据库后，使用`php artisan migrate`来迁移数据库

	php artisan migrate

迁移的其他命令行选项请[参考](http://laravel.com/docs/migrations)
