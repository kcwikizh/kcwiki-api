<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;


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


// Homepage
$app->get('/', function() {
   return view('link');
});


$app->get('/panel', function() use ($app) {
    if (Auth::check()) {
        $user = Auth::user();
        $news = News::all();
        $ships = json_decode(Storage::disk('local')->get('api_ships.json'), false)->results;
        $maps = json_decode(Storage::disk('local')->get('api_maps.json'), false)->results;
        $equips = json_decode(Storage::disk('local')->get('api_equips.json'), false)->results;
        return view('index')->withNews($news)->withUser($user)->withShips($ships)->withMaps($maps)->withEquips($equips);
    }
    return redirect('/login');
});


// Login
$app->get('/login', function () use ($app) {
    return view('login');
});

$app->post('/login', function(Request $request) use ($app) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials, true)) {
        return redirect('/panel');
    }
    return redirect('/login');
});

$app->get('/logout', function(){
    Auth::logout();
    return redirect('/login');
});

// Meta
$app->get('/meta', function() {
    $ships = json_decode(Storage::disk('local')->get('api_ships.json'), false)->results;
    $maps = json_decode(Storage::disk('local')->get('api_maps.json'), false)->results;
    $equips = json_decode(Storage::disk('local')->get('api_equips.json'), false)->results;
    return view('meta')->withShips($ships)->withMaps($maps)->withEquips($equips);
});

// API
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
       'ship' => 'array',
       'quest' => 'array',
       'content' => 'max:1024',
       'equip' => 'array'
   ];
   $validator = Validator::make($request->all(), $rules);
   if ($validator->fails()) {
       return redirect('/')->withErrors($validator);
   }
   $ship = $request->input('ship') ? join(',',$request->input('ship')) : '';
   $quest = $request->input('quest') ? join(',',$request->input('quest')) : '';
   $equip = $request->input('equip') ? join(',',$request->input('equip')) : '';
   News::create([
      'title' => $request->input('title'),
      'ship' => $ship,
      'equip' => $equip,
      'quest' => $quest,
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
        'ship' => 'array',
        'quest' => 'array',
        'content' => 'max:1024',
        'equip' => 'array'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return redirect('/')->withErrors($validator);
    }
    $ship = $request->input('ship') ? join(',',$request->input('ship')) : '';
    $quest = $request->input('quest') ? join(',',$request->input('quest')) : '';
    $equip = $request->input('equip') ? join(',',$request->input('equip')) : '';
    $news = News::findOrFail($id);
    $news->fill([
        'title' => $request->input('title'),
        'ship' => $ship,
        'equip' => $equip,
        'quest' => $quest,
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

$app->get('/subtitles', function() {
   if (!Cache::has('subtitles')) {
       $subtitles = Storage::disk('local')->get('subtitles.json');
       Cache::put('subtitles', $subtitles, 60);
   }
   $subtitles = Cache::get('subtitles');
   return $subtitles;
});

$app->get('/subtitles/purge', function() {
    $subtitles = Storage::disk('local')->get('subtitles.json');
    Cache::put('subtitles', $subtitles, 60);
    return 'Purge Success';
});

$app->get('/subtitles/{id}', function($id) {
    if (!Cache::has('subtitles')) {
        $subtitles = Storage::disk('local')->get('subtitles.json');
        Cache::put('subtitles', $subtitles, 60);
    }
    $subtitles = json_decode(Cache::get('subtitles'), true);
    if (array_key_exists($id, $subtitles)) {
        return response()->json($subtitles[$id]);
    } else {
        return response()->json(['error' => 'Subtitles not found']);
    }
});