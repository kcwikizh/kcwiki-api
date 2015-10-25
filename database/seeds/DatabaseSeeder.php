<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\News;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('UserTableSeeder');
        $this->call('NewsTableSeeder');

        Model::reguard();
    }
}


class UserTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();
        User::create([
            'email'=> env('ADMIN_USERNAME', 'admin@xxx.xxx'),
            'password'=> bcrypt(env('ADMIN_PASSWORD', 'admin'))
        ]);
    }
}

class NewsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('news')->delete();
        News::create([
            'title' => '9.25 更新内容',
            'ship' => '110,102,103,36,37,405',
            'equip' => '148,149',
            'quest' => '1056,1057,1058,2050,2051,2052,4019,6019',
            'content' => '更新完后建议重新启动App\n\n1、更新舰船：\n【翔鹤改二】'
        ]);
    }
}