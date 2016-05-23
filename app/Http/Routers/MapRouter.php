<?php
use Illuminate\Support\Facades\Storage;

$app->get('/maps', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('map/all.json');
    return response($raw);
}]);

$app->get('/map/area/{id:\d{1,3}}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("map/area/$id.json");
    return response($raw);
}]);

$app->get('/map/{id:\d{1,3}}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("map/info/$id.json");
    return response($raw);
}]);

$app->get('/map/{areaId:\d{1,2}}/{infoId:\d{1,2}}', ['middleware' => 'cache', function($areaId, $infoId) {
    $id = $areaId . $infoId;
    $raw = Storage::disk('local')->get("map/info/$id.json");
    return response($raw);
}]);
