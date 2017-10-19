<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Enemy;
use App\Optime;
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
$app->get('/tweet/info', ['uses' => 'TweetController@getInfo']);
$app->get('/tweet/{count:\d{1,3}}', ['uses' => 'TweetController@getExtracted']);
$app->get('/tweet/html/{count:\d{1,3}}', ['uses' => 'TweetController@getHtml']);
$app->get('/tweet/plain/{count:\d{1,3}}', ['uses' => 'TweetController@getPlain']);
$app->get('/tweet/date/{date:\d{8}}', ['uses' => 'TweetController@getDatePosts']);



$app->get('/servers',function(){
    try {
        $data = Storage::disk('local')->get('api_servers.json');
        return response($data)->header('Content-Type', 'application/json');
    } catch (FileNotFoundException $e) {
        return response()->json(['result' => 'error', 'reason' => "server information not found in server"]);
    }
});

$app->get('/avatar/latest', ['middleware' => 'cache', function() {
    $raw = file_get_contents('https://static.kcwiki.moe/Avatar/archives.json');
    $archives = json_decode($raw, true);
    $base = 'http://static.kcwiki.moe/Avatar/';
    $latest = array_slice($archives, -1)[0];
    return response()->json(['latest' => $base.$latest]);
}]);

$app->get('/avatars', ['middleware' => 'cache', function() {
    $raw = file_get_contents('https://static.kcwiki.moe/Avatar/archives.json');
    $archives = json_decode($raw, true);
    $base = 'http://static.kcwiki.moe/Avatar/';
    return response()->json([
        'base' => $base,
        'archives' => $archives
    ]);
}]);

$app->post('/optime', function(Request $request){
    $rules = [
        'start-time' => 'date_format:"Y-m-d H:i"',
        'comment' => 'required',
        'password' => 'required|alpha_num',
        'type' => 'required|in:server,account'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid'])->header('Access-Control-Allow-Origin', '*');
    }
    if (env('OPTIME_PASSWORD', 'somepassword') !== $request->input('password'))
        return response()->json(['result' => 'error', 'reason' => 'Incorrect password'])->header('Access-Control-Allow-Origin', '*');
    $type = $request->input('type');
    Optime::create([
        'start_time' => $request->input('start-time'),
        'comment' => $request->input('comment'),
        'type' => $type
    ]);
    Cache::forget('optime/server');
    Cache::forget('optime/account');
    return response()->json(['result'=>'success'])->header('Access-Control-Allow-Origin', '*');
});

$app->get('/optime/{type}', ['as' => 'optime', 'middleware' => 'cache', function($type) {
    if ($type !== 'account' && $type !== 'server') return response()->json(['result' => 'error', 'reason' => 'invalid type']);
    try {
        $optime = Optime::where('type', $type)->orderBy('id', 'desc')->firstOrFail();
        return response()->json([
            'time' => $optime['start_time'],
            'comment' => $optime['comment']
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['result' => 'error', 'reason' => 'record not found']);
    }
}]);

//Purge cache
$app->get('/purge/{option}', function($option) {
     if($option=="all")
         Cache::flush();
     else
         Cache::tags($option)->flush();
    return $option." cache has been purged!";
});

// Auto include router files
$router_files = scandir(dirname(__FILE__).'/Routers');
foreach ($router_files as $i => $file)
    if (strpos($file, '.php') > 0)
        include_once("Routers/$file");
