<?php
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Util;

// Api Start2
$app->post('/start2/upload', function(Request $request) {
    $rules = [
        'password' => 'required|alpha_dash|between:5,50',
        'data' => 'required|json'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return Util::errorResponse('data invalid');
    }
    $inputs = $request->all();
    if (env('ADMIN_PASSWORD', 'admin') !== $inputs['password'])
        return Util::errorResponse('incorrect password');
    $start2 = json_decode($inputs['data'], true);
    if (Util::exists('api_start2.json')) {
        $data = Util::load('api_start2.json');
        if (Util::compareJson($data, $start2))
            return Util::errorResponse('duplicate start2 data');
    }
    Util::dump('api_start2.json', $start2);
    $datetime = new DateTime();
    $today = $datetime->format('YmdHi');
    Util::dump("start2/$today.json", $start2);
    Queue::push(function ($job) {
        Artisan::call('parse:start2');
        $job->delete();
    });
    return Util::successResponse();
});

$app->get('/start2', function() {
    try {
        $data = Util::read('api_start2.json');
        return response($data)->header('Content-Type', 'application/json');
    } catch (FileNotFoundException $e) {
        return Util::errorResponse('api_start2.json not found in server');
    }
});

$app->get('/start2/archives', function() {
    $files = Storage::disk('local')->files('start2');
    $list = [];
    $matches = [];
    foreach($files as $file) {
        preg_match('/\d{8,12}/', $file, $matches);
        if (count($matches) > 0) array_push($list, $matches[0]);
    }
    return response()->json($list);
});

$app->get('/start2/prev', function() {
    $files = Storage::disk('local')->files('start2');
    if (count($files) < 1) return Util::errorResponse('There is no start2 data in server');
    $file = $files[max(count($files) - 2, 0)];
    $data = Storage::disk('local')->get($file);
    return response($data)->header('Content-Type', 'application/json');
});

$app->get('/start2/{version:\d{8,12}}', function($version) {
    $file = 'start2/'.$version.'.json';
    try {
        $data = Util::read($file);
        return response($data)->header('Content-Type', 'application/json');
    } catch (FileNotFoundException $e) {
        return Util::errorResponse("start2($version) not found in server");
    }
});