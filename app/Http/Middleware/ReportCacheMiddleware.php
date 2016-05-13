<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 16/3/23
 * Time: 上午5:47
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class ReportCacheMiddleware
{
    public function handle($request, Closure $next)
    {
        $input = $request->all();
        $hash = md5(json_encode($input));
        $count = Cache::get($hash, 0);
        if ($count > 5) {
            return response()->json(['result'=>'hit']);
        }
        Cache::forever($hash, $count+1);
        return $next($request);
    }
}