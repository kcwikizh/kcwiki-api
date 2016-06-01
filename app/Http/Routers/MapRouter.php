<?php
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\MapRecord;

$app->get('/maps', ['middleware' => 'cache', function() {
    $raw = Storage::disk('local')->get('map/all.json');
    return response($raw);
}]);

$app->get('/map/area/{id:\d{1,3}}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("map/area/$id.json");
    return response($raw);
}]);

$app->get('/map/{id:\d{1,3}}', ['middleware' => 'cache', function($id) {
    $raw = Storage::disk('local')->get("map/info/$id.json");
    return response($raw);
}]);

$app->get('/map/{areaId:\d{1,2}}/{infoId:\d{1,2}}', ['middleware' => 'cache', function($areaId, $infoId) {
    $id = $areaId . $infoId;
    $raw = Storage::disk('local')->get("map/info/$id.json");
    return response($raw);
}]);

$app->post('/map/cell', function(Request $request) {
    $rules = [
        'mapArea' => 'required|digits_between:1,3',
        'mapInfo' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3',
        'cellNo' => 'required|string'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }

    MapRecord::create([
        'mapAreaId' => $request->input('mapArea'),
        'mapId' => $request->input('mapInfo'),
        'cellId' => $request->input('cellId'),
        'cellNo' => $request->input('cellNo')
    ]);

    return response()->json(['result' => 'success']);
});