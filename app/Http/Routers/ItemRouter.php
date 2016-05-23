<?php
use Illuminate\Support\Facades\Storage;

$app->get('/furnitures', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('furniture/all.json');
    return response($raw);
}]);

$app->get('/furniture/graphs', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('furniture/graph/all.json');
    return response($raw);
}]);

$app->get('/useitems', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('useitem/all.json');
    return response($raw);
}]);

$app->get('/payitems', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('payitem/all.json');
    return response($raw);
}]);

$app->get('/shop', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('shop/all.json');
    return response($raw);
}]);
