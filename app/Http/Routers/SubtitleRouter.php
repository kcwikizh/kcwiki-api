<?php
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\SubtitleCache;
use App\Util;

// Subtitles API
$app->get('/subtitles', function() {
  $subtitles = SubtitleCache::get();
  return response()->json($subtitles);
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

$app->get('/subtitles/jp/{id:\d{1,4}}', function($id) {
    $subtitles = SubtitleCache::getByShip($id, 'jp');
    if ($subtitles) {
        return $subtitles;
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Subtitles not found']);
    }
});

$app->get('/subtitles/{id:\d{1,4}}', function($id) {
    $subtitles = SubtitleCache::getByShip($id);
    if ($subtitles) {
        return $subtitles;
    } else {
        return response()->json(['result' => 'error', 'reason' => 'Subtitles not found']);
    }
});

$app->get('/subtitles/detail', ['middleware' => 'cache', function() {
    $subtitlesRaw = Util::remember('subtitles/distinct', function() {
        return json_decode(Storage::disk('local')->get('subtitles_distinct.json'), true);
    });
    $subtitles = $subtitlesRaw['zh'];
    $subtitlesJP = $subtitlesRaw['jp'];
    $ships = json_decode(Util::getShips(), true);
    $results = [];
    foreach ($ships as $ship) {
        $id = $ship['id'];
        if (!array_key_exists($id, $subtitles) && !array_key_exists($id, $subtitlesJP)) continue;
        $result = [];
        for ($voiceId = 1; $voiceId < 54; $voiceId ++) {
            if (array_key_exists($id, $subtitles) && array_key_exists($voiceId, $subtitles[$id])) {
                $item = [];
                $item['zh'] = $subtitles[$id][$voiceId];
                if (array_key_exists($id, $subtitlesJP) && array_key_exists($voiceId, $subtitlesJP[$id]))
                    $item['jp'] = $subtitlesJP[$id][$voiceId];
                $wikid = $ship['wiki_id'];
                $alias = Util::$vcScenesAlias[$voiceId];
                $filename = "$wikid-$alias.mp3";
                $md5 = md5($filename);
                $dir = substr($md5,0,1);
                $subdir = substr($md5,0,2);
                $item['url'] = "https://kc.6candy.com/commons/$dir/$subdir/$filename";
                $item['scene'] = Util::$vcScenes[$voiceId];
                $item['voiceId'] = $voiceId;
                array_push($result, $item);
            }
        }
        $results[$id] = $result;
    }
    return response()->json($results);
}]);

$app->get('/subtitle/detail/{id:\d{1,4}}', ['middleware' => 'cache', function($id) {
    $subtitlesRaw = Util::remember('subtitles/distinct', function() {
        return json_decode(Storage::disk('local')->get('subtitles_distinct.json'), true);
    });
    $subtitles = $subtitlesRaw['zh'];
    $subtitlesJP = $subtitlesRaw['jp'];
    $ship = json_decode(Util::getShipById($id), true);
    $id = $ship['id'];
    $result = [];
    if (!array_key_exists($id, $subtitles) && !array_key_exists($id, $subtitlesJP)) return response()->json($result);
    for ($voiceId = 1; $voiceId < 54; $voiceId ++) {
        if (array_key_exists($id, $subtitles) && array_key_exists($voiceId, $subtitles[$id])) {
            $item = [];
            $item['zh'] = $subtitles[$id][$voiceId];
            if (array_key_exists($id, $subtitlesJP) && array_key_exists($voiceId, $subtitlesJP[$id]))
                $item['jp'] = $subtitlesJP[$id][$voiceId];
            $wikid = $ship['wiki_id'];
            $alias = Util::$vcScenesAlias[$voiceId];
            $filename = "$wikid-$alias.mp3";
            $md5 = md5($filename);
            $dir = substr($md5,0,1);
            $subdir = substr($md5,0,2);
            $item['url'] = "http://kc.6candy.com/commons/$dir/$subdir/$filename";
            $item['scene'] = Util::$vcScenes[$voiceId];
            $item['voiceId'] = $voiceId;
            array_push($result, $item);
        }
    }
    return response()->json($result);
}]);

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
