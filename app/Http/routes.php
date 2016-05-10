<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPHtmlParser\Dom;
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

// Tweet
$app->get('/tweet/{count:\d{1,3}}', function($count) {
    $key = "tweet.$count";
    if (Cache::has($key)) return response(Cache::get($key))->header('Content-Type', 'application/json')->header('Access-Control-Allow-Origin', '*');
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
        Cache::put($key, $output, 5);
        return response($output)->header('Content-Type', 'application/json')->header('Access-Control-Allow-Origin', '*');
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Getting tweets failed.']);
    }
});

// Api Start2
$app->post('/start2/upload', function(Request $request) {
    $rules = [
        'password' => 'required|alpha_dash|between:5,50',
        'data' => 'required|json'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    $inputs = $request->all();
    if (env('ADMIN_PASSWORD', 'admin') !== $inputs['password'])
        return response()->json(['result' => 'error', 'reason' => 'Incorrect password']);
    Storage::disk('local')->put('api_start2.json', $inputs['data']);
    return response()->json(['result' => 'success']);
});

$app->get('/start2', function() {
   try {
      $data = Storage::disk('local')->get('api_start2.json');
      return response($data)->header('Content-Type', 'application/json');
   } catch (FileNotFoundException $e) {
      return response()->json(['result' => 'error', 'reason' => 'api_start2.json not found in server']);
   }
});

include_once('Routers/NewsRouter.php');
include_once('Routers/SubtitleRouter.php');
include_once('Routers/ReportRouter.php');