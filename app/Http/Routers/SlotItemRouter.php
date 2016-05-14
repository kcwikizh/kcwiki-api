<?php
use Illuminate\Support\Facades\Storage;

$app->get('/slotitems/type', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('slotitem/type/all.json');
    return response($raw);
}]);

$app->get('/slotitems', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('slotitem/all.json');
    return response($raw);
}]);

$app->get('/slotitem/{id}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("slotitem/$id.json");
    return response($raw);
}]);

$app->get('/slotitems/detail', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('slotitem/detail/all.json');
    return response($raw);
}]);

$app->get('/slotitem/detail/{id}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("slotitem/detail/$id.json");
    return response($raw);
}]);