<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use PHPHtmlParser\Dom;

class TweetController extends BaseController
{
    public function getHtml($count)
    {
        return $this->handle($count, 'html');
    }

    public function getExtracted($count)
    {
        return $this->handle($count, 'extracted');
    }

    public function getPlain($count)
    {
        return $this->handle($count, 'plain');
    }

    private function handle($count, $option)
    {
        $key = "tweet.$option.$count";
        $tag = "tweet";
        if (Cache::tags($tag)->has($key)) return response(Cache::tags($tag)->get($key))->header('Content-Type', 'application/json')->header('Access-Control-Allow-Origin', '*');
        $rep = file_get_contents("https://t.kcwiki.moe/?json=1&count=$count");
        if ($rep) {
            $result = json_decode($rep, true);
            $posts = $result['posts'];
            $output = [];
            foreach ($posts as $post) {
                $dom = new Dom;
                $dom->load($post['content']);
                $new_post = [];
                if (array_key_exists('ozh_ta_id', $post['custom_fields']) && is_array($post['custom_fields']['ozh_ta_id']))
                    $new_post['id'] = $post['custom_fields']['ozh_ta_id'][0];
                else
                    $new_post['id'] = '';
                $img = $dom->find('img');
                if (count($img) > 0 && $option != 'html') {
                    $new_post['img'] = $img[0]->getAttribute('src');
                    foreach ($img as $x) {
                        $parent = $x->getParent();
                        $parentTagName = $parent->getTag()->name();
                        if ($parentTagName == 'a') {
                            $parent->delete();
                        } else {
                            $x->delete();
                        }
                    }
                } else if ($option != 'html') {
                    $new_post['img'] = '';
                }
                $p = $dom->find('p, div');
                $plength = count($p);
                $new_post['jp'] = $p[0]->innerHtml;
                $new_post['zh'] = '';
                for ($i=1; $i < $plength; $i++) {
                    $new_post['zh'] .= $p[$i]->innerHtml;
                }
                $new_post['date'] = $post['date'];
                if ($option == 'plain') {
                    $new_post['zh'] = strip_tags($new_post['zh']);
                    $new_post['jp'] = strip_tags($new_post['jp']);
                }
                array_push($output, $new_post);
            }
            Cache::tags($tag)->put($key, $output, 5);
            return response($output)->header('Content-Type', 'application/json')->header('Access-Control-Allow-Origin', '*');
        } else {
            return response()->json(['result' => 'error', 'reason' => 'Getting tweets failed.']);
        }
    }

}
