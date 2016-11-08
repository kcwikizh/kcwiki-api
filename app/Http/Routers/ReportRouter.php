<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Tyku, App\Path, App\EnemyFleet, App\Enemy, App\ShipAttr, App\InitEquip, App\MapEvent, App\MapMaxHp;
use App\Util;

// Reporter API
$app->post('/tyku', ['middleware' => 'report-cache',function(Request $request){
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3',
        'maxTyku' => 'required|digits_between:1,4',
        'minTyku' => 'required|digits_between:1,4',
        'seiku' => 'required|digits_between:1,2',
        'rank' => 'required|size:1',
        'version' => 'required'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
   Tyku::create([
      'mapAreaId' => $request->input('mapAreaId'),
      'mapId' => $request->input('mapId'),
      'cellId' => $request->input('cellId'),
      'maxTyku' => $request->input('maxTyku'),
      'minTyku' => $request->input('minTyku'),
      'seiku' => $request->input('seiku'),
      'rank' => $request->input('rank')
   ]);
    return response()->json(['result'=>'success']);
}]);

$app->post('/path', ['middleware' => 'report-cache', function(Request $request) {
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'path' => 'required|array',
        'decks' => 'required|array'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
//    Path::create([
//       'mapAreaId' => $request->input('mapAreaId'),
//       'mapId' => $request->input('mapId'),
//       'path' => json_encode($request->input('path')),
//       'decks' => json_encode($request->input('decks'))
//    ]);
    return response()->json(['result'=>'success']);
}]);

$app->post('/enemy', ['middleware' => 'report-cache', function(Request $request) {
    $rules = [
        'enemyId' => 'required|array',
        'maxHP' => 'required|array',
        'slots' => 'required|array',
        'param' => 'required|array',
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    EnemyFleet::create([
        'mapAreaId' => $request->input('mapAreaId'),
        'mapId' => $request->input('mapId'),
        'cellId' => $request->input('cellId'),
        'fleets' => json_encode($request->input('enemyId'))
    ]);
    $enemies = $request->input('enemyId');
    $maxHP = $request->input('maxHP');
    $slots = $request->input('slots');
    $param = $request->input('param');
    for ($i = 0; $i < count($enemies); $i++) {
        if ($enemies[$i] == -1) continue;
        $row = [
           'enemyId' => $enemies[$i],
           'maxHP' => $maxHP[$i],
           'slot1' => $slots[$i][0],
           'slot2' => $slots[$i][1],
           'slot3' => $slots[$i][2],
           'slot4' => $slots[$i][3],
           'slot5' => $slots[$i][4],
           'houg' => $param[$i][0],
           'raig' => $param[$i][1],
           'tyku' => $param[$i][2],
           'souk' => $param[$i][3]
        ];
        $hash = md5(json_encode($row));
        if (Cache::has($hash)) continue;
        Cache::forever($hash, 1);
        Enemy::create($row);
    }
    return response()->json(['result'=>'success']);
}]);

$app->post('/shipAttr', ['middleware' => 'report-cache', function(Request $request) {
    $rules = [
        'sortno' => 'required|digits_between:1,4',
        'taisen' => 'required|digits_between:1,3',
        'kaihi' => 'required|digits_between:1,3',
        'sakuteki' => 'required|digits_between:1,3',
        'luck' => 'required|digits_between:1,3',
        'level' => 'required|digits_between:1,3'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    ShipAttr::Create([
       'sortno' => $request->input('sortno'),
       'taisen' => $request->input('taisen'),
       'kaihi' => $request->input('kaihi'),
       'sakuteki' => $request->input('sakuteki'),
       'luck' => $request->input('luck'),
       'level' => $request->input('level')
    ]);
    return response()->json(['result'=>'success']);
}]);

$app->post('/initEquip', ['middleware' => 'report-cache', function(Request $request) {
    $rules = [
        'ships' => 'required|array'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    $ships = $request->input('ships');
    foreach ($ships as $sortno => $ship) {
        $row = ['sortno' => $sortno];
        for ($i=0; $i<count($ship); $i++) {
            $j = $i + 1;
            $row["slot$j"] = $ship[$i];
        }
        InitEquip::create($row);
    }
    return response()->json(['result'=>'success']);
}]);

$app->post('/mapEvent', ['middleware' => 'report-cache', function(Request $request) {
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3',
        'eventId' => 'required|array',
        'eventType' => 'required|digits_between:1,2',
        'count' => 'required|array',
        'dantan' => 'boolean'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    $inputs = $request->all();
    $dantan = array_key_exists('dantan', $inputs) ? $inputs['dantan'] : false;
    foreach ($inputs['eventId'] as $eventno => $event) {
        MapEvent::create([
            'mapAreaId' => $inputs['mapAreaId'],
            'mapId' => $inputs['mapId'],
            'cellId' => $inputs['cellId'],
            'eventId' => $event,
            'eventType' => $inputs['eventType'],
            'count' => $inputs['count'][$eventno],
            'dantan' => $dantan
        ]);
    }
    return response()->json(['result' => 'success']);
}]);

$app->get('/map/max/hp', ['middleware' => 'report-cache', function(Request $request) {
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'maxHp' => 'required|digits_between:1,10',
        'lv' => 'required|digits_between:1,4'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    $inputs = $request->all();
    MapMaxHp::create([
        'mapAreaId' => $inputs['mapAreaId'],
        'mapId' => $inputs['mapId'],
        'maxHp' => $inputs['maxHp'],
        'lv' => $inputs['lv']
    ]);
    return response()->json(['result' => 'success']);
}]);

// Report Results
$app->get('/report/enemies', ['middleware' => 'cache', function() {
    $raw = Util::load('report/enemy.json');
    return $raw;
}]);

$app->get('/report/new', ['middleware' => 'cache', function() {
    $raw = Util::load('report/new.json');
    return $raw;
}]);