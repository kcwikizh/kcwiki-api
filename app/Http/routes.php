<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\News;
use App\SubtitleCache;
use PHPHtmlParser\Dom;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Tyku, App\Path, App\EnemyFleet, App\Enemy, App\ShipAttr, App\InitEquip, App\MapEvent;

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

// Subtitles API
$app->get('/subtitles', function() {
  $subtitles = SubtitleCache::get();
  return response()->json($subtitles);
});

$app->get('/purge', function() {
    Cache::flush();
    return 'Purge Success';
});

$app->get('/subtitles/version', function() {
   return response()->json(['version' => SubtitleCache::getLatest()]);
});

$app->get('/subtitles/diff/{version:\d{8}[\dA-Z]{0,2}}', function($version) {
    if (Cache::has('maintenance') and Cache::get("maintenance") == 'true')
        return response()->json([]);
    try {
        $diff = SubtitleCache::getDiff($version);
    } catch (FileNotFoundException $e) {
        return response()->json(['result' => 'error', 'reason' => 'Version not found']);
    }
    return response()->json($diff);
});

$app->get('/subtitles/jp/diff/{version:\d{8}[\dA-Z]{0,2}}', function($version) {
    if (Cache::has('maintenance') and Cache::get("maintenance") == 'true')
        return response()->json([]);
    try {
        $diff = SubtitleCache::getDiff($version, 'jp');
    } catch (FileNotFoundException $e) {
        return response()->json(['result' => 'error', 'reason' => 'Version not found']);
    }
    return response()->json($diff);
});

$app->get('/subtitles/jp', function() {
    $subtitles = SubtitleCache::get('latest', 'jp');
    if ($subtitles) {
        return $subtitles;
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Subtitles not found']);
    }
});

$app->get('/subtitles/jp/{id}', function($id) {
    $subtitles = SubtitleCache::getByShip($id, 'jp');
    if ($subtitles) {
        return $subtitles;
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Subtitles not found']);
    }
});

$app->get('/subtitles/{id}', function($id) {
    $subtitles = SubtitleCache::getByShip($id);
    if ($subtitles) {
        return $subtitles;
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Subtitles not found']);
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

$app->get('/tweet/{count:\d{1,3}}', function($count) {
    $rep = file_get_contents("http://t.kcwiki.moe/?json=1&count=$count");
    if ($rep) {
        $result = json_decode($rep, true);
        $posts = $result['posts'];

        $output = [];
        foreach ($posts as $post) {
            $dom = new Dom;
            $dom->load($post['content']);
            $p = $dom->find('p');
            $plength = count($p);
            $new_post = [];
            $new_post['jp'] = $p[0]->outerHtml;
            $new_post['zh'] = '';
            for ($i=1; $i < $plength; $i++) {
                $new_post['zh'] .= $p[$i]->outerHtml;
            }
            $new_post['date'] = $post['date'];
            array_push($output, $new_post);
        }
        return response($output)->header('Content-Type', 'application/json')->header('Access-Control-Allow-Origin', '*');
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Getting tweets failed.']);
    }
});

// Reporter API
$app->post('/tyku', ['middleware' => 'cache',function(Request $request){
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3',
        'tyku' => 'required|digits_between:1,4',
        'rank' => 'required|size:1'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    Tyku::create([
       'mapAreaId' => $request->input('mapAreaId'),
       'mapId' => $request->input('mapId'),
       'cellId' => $request->input('cellId'),
       'tyku' => $request->input('tyku'),
       'rank' => $request->input('rank')
    ]);
    return response()->json(['result'=>'success']);
}]);

$app->post('/path', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'path' => 'required|array',
        'decks' => 'required|array'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    Path::create([
       'mapAreaId' => $request->input('mapAreaId'),
       'mapId' => $request->input('mapId'),
       'path' => json_encode($request->input('path')),
       'decks' => json_encode($request->input('decks'))
    ]);
    return response()->json(['result'=>'success']);
}]);

$app->post('/enemy', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'enemyId' => 'required|array',
        'maxHP' => 'required|array',
        'slots' => 'required|array',
        'param' => 'required|array',
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    EnemyFleet::create([
        'mapAreaId' => $request->input('mapAreaId'),
        'mapId' => $request->input('mapId'),
        'cellId' => $request->input('cellId'),
        'fleets' => json_encode($request->input('enemyId'))
    ]);
    $enemies = $request->input('enemyId');
    $maxHP = $request->input('maxHP');
    $slots = $request->input('slots');
    $param = $request->input('param');
    for ($i = 0; $i < count($enemies); $i++) {
        if ($enemies[$i] == -1) continue;
        $row = [
           'enemyId' => $enemies[$i],
           'maxHP' => $maxHP[$i],
           'slot1' => $slots[$i][0],
           'slot2' => $slots[$i][1],
           'slot3' => $slots[$i][2],
           'slot4' => $slots[$i][3],
           'slot5' => $slots[$i][4],
           'houg' => $param[$i][0],
           'raig' => $param[$i][1],
           'tyku' => $param[$i][2],
           'souk' => $param[$i][3]
        ];
        $hash = md5(json_encode($row));
        if (Cache::has($hash)) continue;
        Cache::forever($hash, 1);
        Enemy::create($row);
    }
    return response()->json(['result'=>'success']);
}]);

$app->post('/shipAttr', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'sortno' => 'required|digits_between:1,4',
        'taisen' => 'required|digits_between:1,3',
        'kaihi' => 'required|digits_between:1,3',
        'sakuteki' => 'required|digits_between:1,3',
        'luck' => 'required|digits_between:1,3',
        'level' => 'required|digits_between:1,3'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    ShipAttr::Create([
       'sortno' => $request->input('sortno'),
       'taisen' => $request->input('taisen'),
       'kaihi' => $request->input('kaihi'),
       'sakuteki' => $request->input('sakuteki'),
       'luck' => $request->input('luck'),
       'level' => $request->input('level')
    ]);
    return response()->json(['result'=>'success']);
}]);

$app->post('/initEquip', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'ships' => 'required|array'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    $ships = $request->input('ships');
    foreach ($ships as $sortno => $ship) {
        $row = ['sortno' => $sortno];
        for ($i=0; $i<count($ship); $i++) {
            $j = $i + 1;
            $row["slot$j"] = $ship[$i];
        }
        InitEquip::create($row);
    }
    return response()->json(['result'=>'success']);
}]);

$app->post('/mapEvent', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3',
        'eventId' => 'required|digits_between:1,2',
        'eventType' => 'required|digits_between:1,2',
        'count' => 'required|digits_between:1,3',
        'dantan' => 'boolean'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    $inputs = $request->all();
    $dantan = array_key_exists('dantan', $inputs) ? $inputs['dantan'] : false;
    MapEvent::create([
        'mapAreaId' => $inputs['mapAreaId'],
        'mapId' => $inputs['mapId'],
        'cellId' => $inputs['cellId'],
        'eventId' => $inputs['eventId'],
        'eventType' => $inputs['eventType'],
        'count' => $inputs['count'],
        'dantan' => $dantan
    ]);
    return response()->json(['result' => 'success']);
}]);