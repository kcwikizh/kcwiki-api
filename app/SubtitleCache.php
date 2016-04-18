<?php namespace App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SubtitleCache {
    static public function get($ver='latest') {
        $latest = self::getLatest();
        $version = $ver == 'latest' ? $latest : $ver;
        return self::remember($version, function() use ($version) {
            return json_decode(Storage::disk('local')->get('subtitles/'.$version.'.json'), true);
        });
    }

    static public function getI18n($lang='JP') {
        return self::remember($lang, function() use ($lang) {
            return json_decode(Storage::disk('local')->get("subtitles$lang.json"), true);
        });
    }

    static public function getI18nByShip($id, $lang='JP') {
        $key = $lang + $id;
        return self::remember($key, function() use ($id, $lang) {
           $subtitles = SubtitleCache::getI18n($lang);
           if (array_key_exists($id, $subtitles)) {
               return $subtitles[$id];
           } else {
               return [];
           }
        });
    }

    static public function getByShip($id) {
        $key = self::getLatest() + '-' + $id;
        return self::remember($key, function() use ($id) {
            $subtitles = SubtitleCache::get();
            if (array_key_exists($id, $subtitles)) {
                return $subtitles[$id];
            } else {
                return [];
            }
        });
    }

    static public function getDiff($ver='') {
        if (!$ver)
            return ['error'=>'Version not found'];
        $latest = self::getLatest();
        if ($latest == $ver) return [];
        $key = $ver.'-'.$latest;
        return self::remember($key, function() use ($ver, $latest) {
            $diff = [];
            $latestSubtitles = SubtitleCache::get();
            $targetSubtitles = SubtitleCache::get($ver);
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

    static public function remember($key, $callback) {
        if (!Cache::has($key)) {
            $value = $callback();
            Cache::put($key, $value, 60);
        }
        return Cache::get($key);
    }
}
