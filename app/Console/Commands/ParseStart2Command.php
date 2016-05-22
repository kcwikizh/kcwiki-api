<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;
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
    private $slotitem_name_chinese = ['小口径主炮', '中口径主炮', '大口径主炮', '副炮', '鱼雷', '舰上战斗机',
        '舰上爆击机', '舰上攻击机', '舰上侦察机', '水上侦察机', '水上爆击机', '小型电探', '大型电探', '声呐',
        '爆雷', '追加装甲', '结构强化', '对空强化弹', '对舰强化弹', 'VT信管', '对空机枪', '特殊潜航艇', '应急修理要员',
        '登陆艇', '旋翼机', '反潜巡逻机', '追加装甲(中型)', '追加装甲(大型)', '探照灯', '简易输送部材', '舰艇修理设施',
        '潜艇专用鱼雷', '照明弹', '舰队司令部设施', '舰空要员', '高射装置', '对地装备', '大口径主炮（II）', '水上舰要员',
        '大型声呐', '大型飞行艇', '大型探照灯', '战斗粮食', '补给物资', '水上战斗机', '特型内火艇', '陆上攻击机', '局地战斗机',
        '大型电探（II）', '舰上侦察机（II）'];
    private  $slotitem_common_map = [
        'api_id' => 'id',
        'api_name' => 'name',
        'api_sortno' => 'sort_no',
        'api_rare' => 'rare',
        'api_info' => 'info',
        'api_usebull' => 'use_bull'
    ];
    private $furniture_names = ["床","壁紙","窓","壁掛け","家具","机"];


    public function handle()
    {
        $this->info('Fetching http://kcwikizh.github.io/kcdata/ship/all.json ...');
        $kcdata = $this->sort(json_decode(file_get_contents('http://kcwikizh.github.io/kcdata/ship/all.json'), true), 'id');
        try {
            $start2data = json_decode(Storage::disk('local')->get('api_start2.json'), true);
        } catch (FileNotFoundException $e) {
            $this->error('api_start2.json not found.');
        }
        $this->info('Parsing ship data...');
        $start2ship = $this->sort($start2data['api_mst_ship'], 'api_id');
        $start2shipgraph = $this->sort($start2data['api_mst_shipgraph'], 'api_id');
        $start2shiptype = $start2data['api_mst_stype'];
        $start2slottype = $start2data['api_mst_slotitem_equiptype'];
        $start2slotitem = $start2data['api_mst_slotitem'];
        $start2furnitue = $start2data['api_mst_furniture'];
        $start2furnituegraph = $start2data['api_mst_furnituregraph'];
        $start2useitem = $start2data['api_mst_useitem'];
        $start2payitem = $start2data['api_mst_payitem'];

        // update kcdata from api_mst_ship
        $this->update($kcdata, $start2ship, $this->ship_map);
        $this->updateInKey($kcdata, $start2ship, $this->ship_stats_map, 'stats');
        // update kcdata from api_mst_shipgraph
        $this->update($kcdata, $start2shipgraph, $this->ship_graph_map);
        $this->updateInKey($kcdata, $start2shipgraph, $this->ship_graph_detailed_map, 'graph');
        $kcdata = $this->toList($kcdata);
        foreach ($kcdata as $i => $ship) {
            if (array_key_exists('stype', $ship) && $ship['stype'] > 0) {
                $id = $ship['stype'];
                $kcdata[$i]['stype_name'] = $start2shiptype[$id - 1]['api_name'];
                $kcdata[$i]['stype_name_chinese'] = $this->ship_type_chinese[$id - 1];
            }
            if (array_key_exists('filename', $ship) && count($ship['filename']) > 0)
                $kcdata[$i]['swf'] = "/kcs/resources/swf/ships/{$ship['filename']}.swf";
        }
        Storage::disk('local')->put('ship/detailed/all.json', json_encode($kcdata));
        // extract ship filename
        $filename_list = [];
        foreach ($kcdata as $i => $ship)
            if (array_key_exists('filename', $ship)) {
                $filename = [];
                $filename['filename'] = $ship['filename'];
                $filename['file_version'] = $ship['file_version'];
                $id = $ship['id'];
                $filename['id'] = $id;
                Storage::disk('local')->put("ship/filename/$id.json", json_encode($filename));
                array_push($filename_list, $filename);
            }
        Storage::disk('local')->put('ship/filename/all.json', json_encode($filename_list));
        // extract ship stats
        $stats_list = [];
        foreach ($kcdata as $i => $ship)
            if (array_key_exists('stats', $ship) && array_key_exists('name', $ship) && count($ship['name']) > 0) {
                $stats = $ship['stats'];
                $stats['id'] = $ship['id'];
                $id = $ship['id'];
                Storage::disk('local')->put("ship/stats/$id.json", json_encode($stats));
                array_push($stats_list, $stats);
            }
        Storage::disk('local')->put('ship/stats/all.json', json_encode($stats_list));
        // extract common use data from the previous results
        $common_lists = [];
        foreach ($kcdata as $i => $ship)
            if (array_key_exists('id', $ship)) {
                $id = $ship['id'];
                Storage::disk('local')->put("ship/detailed/$id.json", json_encode($ship));
                if (!array_key_exists('name', $ship) || count($ship['name']) < 1)
                    continue;
                $common = [];
                foreach($this->ship_common_keys as $j => $key)
                    if (array_key_exists($key, $ship))
                        $common[$key] = $ship[$key];
                if (count($common) > 0) array_push($common_lists, $common);
                Storage::disk('local')->put("ship/$id.json", json_encode($common));
            }
        Storage::disk('local')->put('ship/all.json', json_encode($common_lists));
        // extract ship graphs
        $graph_list = [];
        foreach ($kcdata as $i => $ship)
            if (array_key_exists('graph', $ship)) {
                $graph = $ship['graph'];
                $id = $ship['id'];
                $graph['id'] = $id;
                Storage::disk('local')->put("ship/graph/$id.json", json_encode($graph));
                array_push($graph_list, $graph);
            }
        Storage::disk('local')->put('ship/graph/all.json', json_encode($graph_list));
        // extract ship types
        $shiptypes = [];
        foreach ($start2shiptype as $i => $type) {
            $shiptype = [];
            foreach($type as $key => $value) {
                $dst_key = substr($key, 4);
                $shiptype[$dst_key] = $value;
            }
            $shiptype['chinese_name'] = $this->ship_type_chinese[$i];
            array_push($shiptypes, $shiptype);
        }
        Storage::disk('local')->put('ship/type/all.json', json_encode($shiptypes));
        $this->info('Parsing slot item data..');
        // extract slotitem types
        $slottypes = [];
        foreach($start2slottype as $i => $type) {
            $slottype = [];
            foreach($type as $key => $value) {
                $dkey = substr($key, 4);
                $slottype[$dkey] = $value;
            }
            $slottype['chinese_name'] = $this->slotitem_name_chinese[$i];
            array_push($slottypes, $slottype);
        }
        Storage::disk('local')->put('slotitem/type/all.json', json_encode($slottypes));
        // extract slotitems
        if (!Storage::disk('local')->has('slotitem/chinese_name/all.json')) Artisan::call('parse:lua slotitem');
        $slotitem_chinese_name = json_decode(Storage::disk('local')->get('slotitem/chinese_name/all.json'), true);
        $slotitems = [];
        $slotitems_common = [];
        foreach ($start2slotitem as $i => $item) {
            $slotitem = [];
            $slotitem['stats'] = [];
            $slotitem_common = [];
            foreach($item as $key => $value)
                if (array_key_exists($key, $this->slotitem_common_map)) {
                    $slotitem[$this->slotitem_common_map[$key]] = $value;
                    $slotitem_common[$this->slotitem_common_map[$key]] = $value;
                }
                else if ($key == 'api_broken' || $key == 'api_type') {
                    $slotitem['type'] = $item['api_type'];
                    $slotitem['broken'] = $item['api_broken'];
                } else
                    $slotitem['stats'][substr($key,4)] = $value;
            $id = $slotitem['id'];
            // Hot fix: 515 高速深海鱼雷 ==> 22inch魚雷後期型
            if ($id == 515) {
                $slotitem['name'] = '22inch魚雷後期型';
                $slotitem_common['name'] = '22inch魚雷後期型';
            }
            if (array_key_exists($id, $slotitem_chinese_name)) {
                $slotitem['chinese_name'] = $slotitem_chinese_name[$id];
                $slotitem_common['chinese_name'] = $slotitem_chinese_name[$id];
            }
            $slotitem['image'] = [
                'card' => "/kcs/resources/image/slotitem/card/{$id}.png",
                'up' => "/kcs/resources/image/slotitem/item_up/{$id}.png",
                'on' => "/kcs/resources/image/slotitem/item_on/{$id}.png",
                'character' => "/kcs/resources/image/slotitem/item_character/{$id}.png"
            ];
            $slotitem_common['type'] = $slotitem['type'][2];
            $slotitem_common['type_name'] = $slottypes[$slotitem_common['type'] - 1]['name'];
            Storage::disk('local')->put("slotitem/$id.json", json_encode($slotitem_common));
            Storage::disk('local')->put("slotitem/detail/$id.json", json_encode($slotitem));
            array_push($slotitems_common, $slotitem_common);
            array_push($slotitems, $slotitem);
        }
        Storage::disk('local')->put("slotitem/detail/all.json", json_encode($slotitems));
        Storage::disk('local')->put("slotitem/all.json", json_encode($slotitems_common));

        // extract furniture
        $this->info('Parsing furniture...');
        $furnitures = [];
        foreach ($start2furnitue as $item) {
            $furniture = [];
            foreach ($item as $key => $value) {
                $furniture[substr($key, 4)] = $value;
            }
            $furniture['type_name'] = $this->furniture_names[$furniture['type']];
            array_push($furnitures, $furniture);
        }
        Storage::disk('local')->put("furniture/all.json", json_encode($furnitures));
        $furnituregraphs = [];
        foreach ($start2furnituegraph as $item) {
            $furnituregraph = [];
            foreach ($item as $key => $value) {
                $furnituregraph[substr($key, 4)] = $value;
            }
            array_push($furnituregraphs, $furnituregraph);
        }
        Storage::disk('local')->put("furniture/graph/all.json", json_encode($furnituregraphs));

        // extract useitem
        $this->info('Parsing use item...');
        $useitems = [];
        foreach ($start2useitem as $item) {
            $useitem = [];
            foreach ($item as $key => $value) {
                $useitem[substr($key, 4)] = $value;
            }
            array_push($useitems, $useitem);
        }
        Storage::disk('local')->put("useitem/all.json", json_encode($useitems));

        // extract payitem
        $this->info('Parsing pay item...');
        $payitems = [];
        foreach ($start2payitem as $item) {
            $payitem = [];
            foreach ($item as $key => $value) {
                $payitem[substr($key, 4)] = $value;
            }
            array_push($payitems, $payitem);
        }
        Storage::disk('local')->put('payitem/all.json', json_encode($payitems));
        $this->info('Done.');
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