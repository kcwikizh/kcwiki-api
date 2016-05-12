<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;

class ParseStart2 extends Command
{

    protected $signature = 'parse:start2';

    protected $description = 'Generate game data based on api_start2.json and kcdata';

    private $ship_map = [
        'id' => 'api_id',
        'sort_no' => 'api_sortno',
        'name' => 'api_name',
        'yomi' => 'api_yomi',
        'stype' => 'api_stype',
        'after_lv' => 'api_afterlv',
        'after_ship_id' => 'api_aftershipid',
        'get_mes' => 'api_getmes',
        'voice_f' => 'api_voicef'
        // 'ctype'
        // 'cnum'
    ];

    private $ship_stats_map = [
        'taik' => 'api_taik',
        'souk' => 'api_souk',
        'houg' => 'api_houg',
        'raig' => 'api_raig',
        'tyku' => 'api_tyku',
        'luck' => 'api_luck',
        'soku' => 'api_soku',
        'leng' => 'api_leng',
        'slot_num' => 'api_slot_num',
        'max_eq' => 'api_maxeq',
        'after_fuel' => 'api_afterfuel',
        'after_bull' => 'api_afterbull',
        'fuel_max' => 'api_fuel_max',
        'bull_max' => 'api_bull_max',
        'broken' => 'api_broken',
        'power_up' => 'api_powerup',
        'build_time' => 'api_buildtime'
    ];

    private $ship_graph_map = ['filename' =>'api_filename', 'file_version' => 'api_version'];
    private $ship_graph_detailed_map = [
        "boko_n" => "api_boko_n",
        "boko_d" => "api_boko_d",
        "kaisyu_n" => "api_kaisyu_n",
        "kaisyu_d" => "api_kaisyu_d",
        "kaizo_n" => "api_kaizo_n",
        "kaizo_d" => "api_kaizo_d",
        "map_n" => "api_map_n",
        "map_d" => "api_map_d",
        "ensyuf_n" => "api_ensyuf_n",
        "ensyuf_d" => "api_ensyuf_d",
        "ensyue_n" => "api_ensyue_n",
        "battle_n" => "api_battle_n",
        "battle_d" => "api_battle_d",
        "wed_a" => "api_weda",
        "wed_b" => "api_wedb"
    ];
    private $ship_common_keys = ['id', 'name', 'sort_no', 'stype', 'after_ship_id',
        'filename', 'wiki_id', 'chinese_name', 'stype_name', 'stype_name_chinese'];
    private $ship_type_chinese = ["海防舰", "驱逐舰", "轻巡洋舰", "重雷装巡洋舰", "重巡洋舰", "航空巡洋舰",
        "轻空母", "战舰", "战舰", "航空战舰", "正规空母", "超弩级战舰", "潜水舰", "潜水空母", "补给舰",
        "水上机母舰", "扬陆舰", "装甲空母", "工作舰", "潜水母舰", "练习巡洋舰", "补给舰"];

    public function handle()
    {
        $this->info('Fetching http://kcwikizh.github.io/kcdata/ship/all.json ...');
        $kcdata = $this->sort(json_decode(file_get_contents('http://kcwikizh.github.io/kcdata/ship/all.json'), true), 'id');
        try {
            $start2data = json_decode(Storage::disk('local')->get('api_start2.json'), true);
        } catch (FileNotFoundException $e) {
            $this->error('api_start2.json not found.');
        }
        $this->info('Parsing...');
        $start2ship = $this->sort($start2data['api_mst_ship'], 'api_id');
        $start2shipgraph = $this->sort($start2data['api_mst_shipgraph'], 'api_id');
        $start2shiptype = $start2data['api_mst_stype'];
        // update kcdata from api_mst_ship
        $this->update($kcdata, $start2ship, $this->ship_map);
        $this->updateInKey($kcdata, $start2ship, $this->ship_stats_map, 'stats');
        // update kcdata from api_mst_shipgraph
        $this->update($kcdata, $start2shipgraph, $this->ship_graph_map);
        $this->updateInKey($kcdata, $start2shipgraph, $this->ship_graph_detailed_map, 'graph');
        $kcdata = $this->toList($kcdata);
        foreach ($kcdata as $i => $ship)
            if (array_key_exists('stype', $ship) && $ship['stype'] > 0) {
                $id = $ship['stype'];
                echo "{$ship['id']} $id  {$start2shiptype[$id-1]['api_name']}  {$this->ship_type_chinese[$id-1]}\n";
                $kcdata[$i]['stype_name'] = $start2shiptype[$id - 1]['api_name'];
                $kcdata[$i]['stype_name_chinese'] = $this->ship_type_chinese[$id - 1];
            }
        Storage::disk('local')->put('ship/detailed/all.json', json_encode($kcdata));
        $common_lists = [];
        // extract common use data from the previous results
        foreach ($kcdata as $i => $ship)
            if (array_key_exists('id', $ship)) {
                $id = $ship['id'];
                Storage::disk('local')->put("ship/detailed/$id.json", json_encode($ship));
                $common = [];
                foreach($this->ship_common_keys as $j => $key)
                    if (array_key_exists($key, $ship))
                        $common[$key] = $ship[$key];
                if (count($common) > 0) array_push($common_lists, $common);
                Storage::disk('local')->put("ship/$id.json", json_encode($common));
            }
        Storage::disk('local')->put('ship/all.json', json_encode($common_lists));
        $this->info('Completed.');
    }

    private function sort($data, $key)
    {
        $result = [];
        foreach($data as $i => $v) {
            $j = $v[$key];
            $result[$j] = $v;
        }
        return $result;
    }

    private function update(&$dst, &$src, &$map)
    {
        foreach($src as $i => $value)
            foreach($map as $dkey => $skey)
                if (array_key_exists($skey, $value))
                    $dst[$i][$dkey] = $value[$skey];
    }

    private function updateInKey(&$dst, &$src, &$map, $inkey)
    {
        foreach($src as $i => $value)
            foreach($map as $dkey => $skey)
                if (array_key_exists($skey, $value) && array_key_exists($inkey, $dst[$i]))
                    $dst[$i][$inkey][$dkey] = $value[$skey];
    }

    private function toList(&$map)
    {
        $list = [];
        foreach ($map as $k => $v)
            array_push($list, $v);
        return $list;
    }
}