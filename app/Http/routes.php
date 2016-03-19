<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\News;
use App\SubtitleCache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

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
  $subtitles = SubtitleCache::get();
  return response()->json($subtitles);
});

$app->get('/subtitles/purge', function() {
    Cache::flush();
    return 'Purge Success';
});

$app->get('/subtitles/diff/{version:\d{8}[A-Z]?}', function($version) {
    if (Cache::has('maintenance') and Cache::get("maintenance") == 'true')
        return response()->json([]);
    try {
        $diff = SubtitleCache::getDiff($version);
    } catch (FileNotFoundException $e) {
        return response()->json(['error' => 'Version not found']);
    }
    return response()->json($diff);
});

$app->get('/subtitles/{id}', function($id) {
    $subtitles = SubtitleCache::get();
    if (array_key_exists($id, $subtitles)) {
        return response()->json($subtitles[$id]);
    } else {
        return response()->json(['error' => 'Subtitles not found']);
    }
});

$app->get('/maintenance/on/{key}', function($key) {
   if ($key != env('ADMIN_PASSWORD', 'admin')) return 'Oops';
   Cache::put('maintenance', 'true', 3600);
   return Cache::get("maintenance");
});

$app->get('/maintenance/off/{key}', function($key) {
    if ($key != env('ADMIN_PASSWORD', 'admin')) return 'Oops';
    Cache::put('maintenance', 'false', 3600);
    return 'Maintenance Off';
});