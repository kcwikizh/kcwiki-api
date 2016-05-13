<?php
use Illuminate\Support\Facades\Storage;

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