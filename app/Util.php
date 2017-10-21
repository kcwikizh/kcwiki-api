<?php
namespace App;
use App\Console\Commands\ParseDB;
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

    static public $vcScenesAlias = ['21'=>'MedDmg','23'=>'MVP','43'=>'1300','11'=>'DockLightDmg',
        '42'=>'1200','2'=>'Sec1','4'=>'Sec3','3'=>'Sec2','24'=>'Proposal','51'=>'2100','49'=>'1900',
        '34'=>'0400','28'=>'SecWed','7'=>'Return','35'=>'0500','14'=>'Sortie','50'=>'2000',
        '25'=>'LibIntro','45'=>'1500','44'=>'1400','32'=>'0200','19'=>'LightDmg1','20'=>'LightDmg2',
        '8'=>'Achievement','18'=>'NightBattle','10'=>'Equip2','26'=>'Equip3','9'=>'Equip1',
        '30'=>'0000','13'=>'FleetOrg','31'=>'0100','37'=>'0700','12'=>'DockMedDmg','29'=>'Idle',
        '39'=>'0900','17'=>'Atk2','5'=>'ConstComplete','16'=>'Atk1','47'=>'1700','6'=>'DockComplete',
        '48'=>'1800','46'=>'1600','38'=>'0800','52'=>'2200','22'=>'Sunk','36'=>'0600','1'=>'Intro',
        '27'=>'Resupply','41'=>'1100','53'=>'2300','15'=>'Battle','33'=>'0300','40'=>'1000'];

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


    static public function exists($path, $driver='local') {
        return Storage::disk($driver)->exists($path);
    }

    static public function copy($src, $dst) {
        Util::dump($dst, Util::load($src));
    }

    /**
     * Retrieve the json object from the disk
     *
     * @param string $path
     * @param string $driver [optional]
     * @return mixed json object
    */
    static public function load($path, $driver='local') {
        return json_decode(Storage::disk($driver)->get($path), true);
    }

    /**
     * Store the json object on the disk
     *
     * @param string $path
     * @param mixed $data
     * @param string $driver [optional]
    */
    static public function dump($path, $data, $driver='local') {
        Storage::disk($driver)->put($path, json_encode($data));
    }

    /**
     * Get raw file contents
     *
     * @param string $path
     * @param string $driver [optional]
     * @return string $contents
    */
    static public function read($path, $driver='local') {
        return Storage::disk($driver)->get($path);
    }

    /**
     * Store raw file contents
     *
     * @param string $path
     * @param string $raw
     * @param string $driver [optional]
    */
    static public function write($path, $raw, $driver='local') {
        Storage::disk($driver)->put($path, $raw);
    }


    static $trace = [];
    /**
     * @param mixed $srcJson
     * @param mixed $dstJson
     * @param integer $depth
     * @return bool
    */
    static public function compareJson($srcJson, $dstJson, $depth=0) {
        if (!is_array($srcJson) && !is_array($dstJson)) return ($srcJson === $dstJson);
        if (!is_array($srcJson) && is_array($dstJson) || is_array($srcJson) && !is_array($dstJson))
            return false;
        if ($depth < 1)
            self::$trace = [];
        foreach ($dstJson as $key => $value) {
            if (!array_key_exists($key, $srcJson)) {
                array_push(self::$trace, 'dst.'.$key);
                return false;
            }
        }
        foreach ($srcJson as $key => $value) {
            array_push(self::$trace, 'src.'.$key);
            if (!array_key_exists($key, $dstJson))
                return false;
            if (is_array($value)) {
                if (!is_array($dstJson[$key])) return false;
                if (Util::compareJson($srcJson[$key], $dstJson[$key], $depth + 1)) {
                    array_pop(self::$trace);
                    continue;
                } else {
                    return false;
                }
            }
            if ($value !== $dstJson[$key])
                return false;
            array_pop(self::$trace);
        }
        return true;
    }

    static public function successResponse() {
        return response()->json(['result' => 'success']);
    }

    static public function errorResponse($reason) {
        return response()->json(['result' => 'error', 'reason' => $reason]);
    }
}

