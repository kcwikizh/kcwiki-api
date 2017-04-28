<?php namespace App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SubtitleCache {

    const TAG="subtitles";

    static public function get($ver='latest', $lang='zh-cn') {
        $latest = self::getLatest();
        $version = $ver == 'latest' ? $latest : $ver;
        return self::remember($lang.$version, function() use ($version, $lang) {
            return json_decode(Storage::disk('local')->get("subtitles/$lang/$version.json"), true);
        });
    }

    static public function getByShip($id, $lang='zh-cn') {
        $key = $lang.self::getLatest().'-'.$id;
        return self::remember($key, function() use ($id, $lang) {
            $subtitles = SubtitleCache::get('latest', $lang);
            if (array_key_exists($id, $subtitles)) {
                return $subtitles[$id];
            } else {
                return [];
            }
        });
    }

    static public function getDiff($ver='', $lang='zh-cn') {
        if (!$ver)
            return ['error'=>'Version not found'];
        $latest = self::getLatest();
        if ($latest == $ver) return [];
        $key = $lang.$ver.'-'.$latest;
        return self::remember($key, function() use ($ver, $latest, $lang) {
            $diff = [];
            $latestSubtitles = SubtitleCache::get('latest', $lang);
            $targetSubtitles = SubtitleCache::get($ver, $lang);
            foreach ($latestSubtitles as $shipId => $voices) {
                if ($shipId == 'version' || !$shipId) continue;
                if (array_key_exists($shipId, $targetSubtitles)) {
                    foreach ($voices as $voiceId => $text)
                        if (!array_key_exists($voiceId, $targetSubtitles[$shipId])
                            || $targetSubtitles[$shipId][$voiceId] != $text) {
                            if (!array_key_exists($shipId, $diff)) $diff[$shipId] = [];
                            $diff[$shipId][$voiceId] = $text;
                        }
                } else {
                    $diff[$shipId] = $voices;
                }
            }
            $diff['version'] = $latest;
            return $diff;
        });
    }

    static public function getLatest() {
        return self::remember('latest', function() {
            $meta = json_decode(Storage::disk('local')->get('subtitles/meta.json'), true);
            return $meta['latest'];
        });
    }

    static public function getSeasonal() {
        return self::remember('seasonal',function() {
            return json_decode(Storage::disk('local')->get('subtitles/subtitles_seasonal.json'), true);
        });
    }

    static public function remember($key, $callback) {
        if (!Cache::has($key)) {
            $value = $callback();
            Cache::tags(self::TAG)->put($key, $value, 60);
        }
        return Cache::tags(self::TAG)->get($key);
    }
}
