<?php
use Illuminate\Support\Facades\Storage;

$app->get('/missions', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('mission/all.json');
    return response($raw);
}]);