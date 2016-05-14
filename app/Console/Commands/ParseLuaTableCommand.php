<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\LuaParser;

class ParseLuaTable extends Command
{

    protected $signature = 'parse:lua {option}';

    protected $description = 'Generate data from kcwiki lua table';

    private $slotitem_url = 'http://zh.kcwiki.moe/api.php?action=query&prop=revisions&rvlimit=1&rvprop=content&format=json&titles=%E6%A8%A1%E5%9D%97:%E8%88%B0%E5%A8%98%E8%A3%85%E5%A4%87%E6%95%B0%E6%8D%AE';

    public function handle()
    {
        switch ($this->argument('option')) {
            case 'slotitem':
                $this->info('Fetching lua table in kcwiki.moe...');
                $data = json_decode(file_get_contents($this->slotitem_url), true);
                $content = $data['query']['pages'][5462]['revisions'][0]['*'];
                $re = "/p.equipDataTb = \\{.*\\}/s";
                preg_match($re, $content, $match);
                $result =  $match[0];
                $parsed = new LuaParser($result);
                $slotitems = $parsed->toArray()['p.equipDataTb'];
                $table = [];
                foreach ($slotitems as $i => $item) {
                  if (array_key_exists('中文名称', $item)) {
                      $table[intval($i)] = $item['中文名称'];
                  } else {
                      $name = array_key_exists('日文名称', $item) ? $item['日文名称'] : $i;
                      echo $name . " missing!\n";
                  }
                }
                Storage::disk('local')->put('slotitem/chinese_name/all.json', json_encode($table));
                $this->info('Done.');
                break;
        }

    }
}