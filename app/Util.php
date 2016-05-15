<?php
namespace App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class Util {

    static public $vcKey = [604825,607300,613847,615318,624009,631856,635451,637218,640529,
        643036,652687,658008,662481,669598,675545,685034,687703,696444,702593,703894,711191,
        714166,720579,728970,738675,740918,743009,747240,750347,759846,764051,770064,773457,
        779858,786843,790526,799973,803260,808441,816028,825381,827516,832463,837868,843091,
        852548,858315,867580,875771,879698,882759,885564,888837,896168];

    static public $vcScenes = ["", "入手/登入时", "秘书舰1", "秘书舰2", "秘书舰3", "建造完成", "修复完成",
        "归来", "战绩", "装备/改修/改造1", "装备/改修/改造2", "小破入渠", "中破入渠", "编成", "出征",
        "战斗开始", "攻击1", "攻击2", "夜战", "小破1", "小破2", "中破", "击沉", "MVP", "结婚", "图鉴介绍",
        "装备（与远征开始、地图资源点、快速修复和快速建造同）", "补给", "秘书舰（婚后）", "放置", "报时00",
        "报时01", "报时02", "报时03", "报时04", "报时05", "报时06", "报时07", "报时08", "报时09", "报时10",
        "报时11", "报时12", "报时13", "报时14", "报时15", "报时16", "报时17", "报时18", "报时19", "报时20",
        "报时21", "报时22", "报时23"];

    static public function converFilename($shipId, $voiceId) {
        return ($shipId + 7) * 17 * (self::$vcKey[$voiceId] - self::$vcKey[$voiceId - 1]) % 99173 + 100000;
    }

    static public function getShips() {
        return self::remember('ships', function() {
            return Storage::disk('local')->get('ship/all.json');
        });
    }

    static public function getShipById($id) {
        return self::remember("ship/$id", function() use ($id) {
            return Storage::disk('local')->get("ship/$id.json");
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