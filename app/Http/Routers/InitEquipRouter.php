<?php
use Illuminate\Support\Facades\Storage;

$app->get('/init/equip/enemy', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('initequip/enemy.json');
    return response($raw);
}]);

$app->get('/init/equip/enemy/missing', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('initequip/enemy_missing.json');
    return response($raw);
}]);

$app->get('/init/equips', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('initequip/all.json');
    return response($raw);
}]);

$app->get('/init/equip/{id:\d{1,3}}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("initequip/$id.json");
    return response($raw);
}]);

$app->get('/init/equip/missing', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('initequip/missing.json');
    return response($raw);
}]);
