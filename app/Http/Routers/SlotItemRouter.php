<?php
use Illuminate\Support\Facades\Storage;

$app->get('/slotitems/type', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('slotitem/type/all.json');
    return response($raw);
}]);