<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\News;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


$app->get('/', function() use ($app) {
    if (Auth::check()) {
        $user = Auth::user();
        $news = News::all();
        return view('index')->withNews($news)->withUser($user);
    }
    return redirect('/login');
});


$app->get('/login', function () use ($app) {
    return view('login');
});

$app->post('/login', function(Request $request) use ($app) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials, true)) {
        return ['result'=>'success'];
    }
    return redirect('/');
});

$app->get('/news', function() {
    $news = News::all();
    return response()->json($news);
});

$app->post('/news', function(Request $request) {
   if (!Auth::check()) {
       return redirect('/login');
   }
   $rules = [
       'title' => 'required|max:256',
       'ship' => 'max:1024',
       'quest' => 'max:1024',
       'content' => 'max:1024',
       'equip' => 'max:1024'
   ];
   $validator = Validator::make($request->all(), $rules);
   if ($validator->fails()) {
       return redirect('/')->withErrors($validator);
   }
   News::create([
      'title' => $request->input('title'),
      'ship' => $request->input('ship'),
      'equip' => $request->input('equip'),
      'quest' => $request->input('quest'),
      'content' => $request->input('content')
   ]);
   $successMessage = '新闻创建成功';
   return redirect('/')->withSuccess($successMessage);
});

$app->post('/news/{id}', function($id, Request $request) {
    if (!Auth::check()) {
        return redirect('/login');
    }
    $rules = [
        'title' => 'required|max:256',
        'ship' => 'max:1024',
        'quest' => 'max:1024',
        'content' => 'max:1024',
        'equip' => 'max:1024'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return redirect('/')->withErrors($validator);
    }
    $news = News::findOrFail($id);
    $news->fill([
        'title' => $request->input('title'),
        'ship' => $request->input('ship'),
        'equip' => $request->input('equip'),
        'quest' => $request->input('quest'),
        'content' => $request->input('content')
    ]);
    $news->save();
    $successMessage = '新闻更新成功';
    return redirect('/')->withSuccess($successMessage);
});

$app->delete('/news/{id}', function($id) {
   if (!Auth::check()) {
       return redirect('/login');
   }
   $ret = News::destroy($id);
   if ($ret) {
       return redirect('/')->withSuccess('新闻删除成功');
   }
   return response()->withErrors(['新闻删除失败']);
});