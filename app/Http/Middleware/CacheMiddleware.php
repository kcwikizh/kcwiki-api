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
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class CacheMiddleware
{
    public function handle($request, Closure $next)
    {
        $uri = $request->path();
        if (Cache::has($uri))
            return response(Cache::get($uri))->header('Content-Type', 'application/json')->header('Access-Control-Allow-Origin', '*');
        try {
            $response = $next($request);
            Cache::forever($uri, $response->getContent());
        } catch (FileNotFoundException $e) {
            $response = response()->json(['result'=>'error', 'reason'=>'data not found.']);
        }
        return $response->header('Content-Type', 'application/json')->header('Access-Control-Allow-Origin', '*');
    }
}