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
        preg_match_all("/(.*?)\//",$uri,$raw);
        if(isset($raw[1][0]))
            $tag=$raw[1][0];
        else
            $tag=$uri;
        if (Cache::tags($tag)->has($uri))
            return response(Cache::tags($tag)->get($uri))->header('Content-Type', 'application/json')->header('Access-Control-Allow-Origin', '*');
        try {
            $response = $next($request);
            Cache::tags($tag)->forever($uri, $response->getContent());
        } catch (FileNotFoundException $e) {
            $response = response()->json(['result'=>'error', 'reason'=>'data not found.']);
        }
        return $response->header('Content-Type', 'application/json')->header('Access-Control-Allow-Origin', '*');
    }
}