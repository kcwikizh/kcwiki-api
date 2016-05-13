<?php
use Illuminate\Support\Facades\Storage;
use App\SubtitleCache;

$app->get('/ships', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('ship/all.json');
    return response($raw);
}]);

$app->get('/ship/{id:\d{1,3}}', ['middleware' => 'cache', function($id) {
   $raw = Storage::disk('local')->get("ship/$id.json");
   return response($raw);
}]);

$app->get('/ships/detail', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('ship/detailed/all.json');
    return response($raw);
}]);

$app->get('/ship/detail/{id:\d{1,3}}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("ship/detailed/$id.json");
    return response($raw);
}]);

$app->get('/ships/filename', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('ship/filename/all.json');
    return response($raw);
}]);

$app->get('/ship/filename/{id:\d{1,3}}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("ship/filename/$id.json");
    return response($raw);
}]);

$app->get('/ships/stats', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('ship/stats/all.json');
    return response($raw);
}]);

$app->get('/ship/stats/{id:\d{1,3}}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("ship/stats/$id.json");
    return response($raw);
}]);

$app->get('/ship/detail/{name:.{1,50}}', ['middleware' => 'cache', function($name) {
    $ships = json_decode(SubtitleCache::remember('ships/detail', function() { return Storage::disk('local')->get('ship/detailed/all.json');}), true);
    $name = urldecode($name);
    foreach($ships as $i => $ship)
        if ($ship['name'] === $name || $ship['chinese_name'] === $name) {
            return response()->json($ship);
        }
    return response()->json(['result'=>'error', 'reason' => 'ship not found']);
}]);

$app->get('/ship/{name:.{1,50}}', ['middleware' => 'cache', function($name) {
    $ships = json_decode(SubtitleCache::remember('ships', function() { return Storage::disk('local')->get('ship/all.json');}), true);
    $name = urldecode($name);
    foreach($ships as $i => $ship)
        if ($ship['name'] === $name || $ship['chinese_name'] === $name) {
            return response()->json($ship);
        }
    return response()->json(['result'=>'error', 'reason' => 'ship not found']);
}]);

