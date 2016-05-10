<?php
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\SubtitleCache;

// Subtitles API
$app->get('/subtitles', function() {
  $subtitles = SubtitleCache::get();
  return response()->json($subtitles);
});

$app->get('/purge', function() {
    Cache::flush();
    return 'Purge Success';
});

$app->get('/subtitles/version', function() {
   return response()->json(['version' => SubtitleCache::getLatest()])->header('Access-Control-Allow-Origin', '*');
});

$app->get('/subtitles/diff/{version:\d{8}[\dA-Z]{0,2}}', function($version) {
    if (Cache::has('maintenance') and Cache::get("maintenance") == 'true')
        return response()->json([]);
    try {
        $diff = SubtitleCache::getDiff($version);
    } catch (FileNotFoundException $e) {
        return response()->json(['result' => 'error', 'reason' => 'Version not found']);
    }
    return response()->json($diff);
});

$app->get('/subtitles/jp/diff/{version:\d{8}[\dA-Z]{0,2}}', function($version) {
    if (Cache::has('maintenance') and Cache::get("maintenance") == 'true')
        return response()->json([]);
    try {
        $diff = SubtitleCache::getDiff($version, 'jp');
    } catch (FileNotFoundException $e) {
        return response()->json(['result' => 'error', 'reason' => 'Version not found']);
    }
    return response()->json($diff);
});

$app->get('/subtitles/jp', function() {
    $subtitles = SubtitleCache::get('latest', 'jp');
    if ($subtitles) {
        return $subtitles;
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Subtitles not found']);
    }
});

$app->get('/subtitles/jp/{id}', function($id) {
    $subtitles = SubtitleCache::getByShip($id, 'jp');
    if ($subtitles) {
        return $subtitles;
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Subtitles not found']);
    }
});

$app->get('/subtitles/{id}', function($id) {
    $subtitles = SubtitleCache::getByShip($id);
    if ($subtitles) {
        return $subtitles;
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Subtitles not found']);
    }
});

$app->get('/maintenance/on/{key}', function($key) {
   if ($key != env('ADMIN_PASSWORD', 'admin')) return 'Oops';
   Cache::put('maintenance', 'true', 3600);
   return Cache::get("maintenance");
});

$app->get('/maintenance/off/{key}', function($key) {
    if ($key != env('ADMIN_PASSWORD', 'admin')) return 'Oops';
    Cache::put('maintenance', 'false', 3600);
    return 'Maintenance Off';
});
