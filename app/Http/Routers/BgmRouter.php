<?php
use Illuminate\Support\Facades\Storage;

$app->get('/bgm', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('bgm/all.json');
    return response($raw);
}]);

$app->get('/mapbgm', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('mapbgm/all.json');
    return response($raw);
}]);
