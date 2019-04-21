<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Util;
use App\InitEquip;
use DB;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ParseReport extends Command
{
    protected $signature = 'parse:report {option}';

    protected $description = 'Generate data from kcwiki-report';

    // Target enemy ships
    private $enemies = ["防空埋護姫","海峡夜棲姫-壊","海峡夜棲姫"];

    // Target new ships (Ship ID)
    private $new = [433, 438, 457, 369, 472, 370, 491, 372];

    public function handle()
    {
        $this->slotitems = Util::load('slotitem/all.json');
        switch ($this->argument('option')) {
            case 'enemy':
                $this->handleEnemies();
                break;
            case 'new':
                $this->handleNewShip();
                break;
            case 'tyku':
                $this->handleTyku();
                break;
            case 'newslot':
                $this->handleNewShipSlotitem();
            case 'battle':
                $this->handleBattle();
            case 'paths':
                $this->handlePaths();
        }
    }

    private function handleEnemies() {
        $results = [];
        $missing = [];
        $ships = Util::load('ship/all.json');
        foreach ($ships as $ship) {
            if ($ship['id'] > 1000) {
                array_push($results, $ship);
            }
        }
        foreach ($results as &$result) {
            $id = $result['id'];
            $this->info("【{$result['name']}】");
            $row = DB::select('select count(*) as counts,id,enemyId,maxHP,slot1,slot2,slot3,slot4,slot5,houg,raig,tyku,souk from enemies where enemyId=:enemyId group by enemyId,slot1,slot2,slot3,slot4,slot5 order by counts desc limit 1',
                ['enemyId' => $id]);
            if (count($row) > 0) {
                $result['slots'] = [
                    $row[0]->slot1,
                    $row[0]->slot2,
                    $row[0]->slot3,
                    $row[0]->slot4,
                    $row[0]->slot5
                ];
                $result['stats'] = [
                    'maxHP' => $row[0]->maxHP,
                    'houg' => $row[0]->houg,
                    'raig' => $row[0]->raig,
                    'tyku' => $row[0]->tyku,
                    'souk' => $row[0]->souk
                ];
            } else {
                array_push($missing, $result['name']);
            }
        }
        foreach ($missing as $name) {
            $this->error("$name is missing");
        }
        Util::dump('report/enemy.json', $results);
        $this->info('Done.');
    }

    private function handleNewShip() {
        $missing = ['min' => [], 'max' => []];
        $results = [];
//        $this->new = [];
//        $ships = Util::load("ship/all.json");
//        foreach ($ships as $ship) {
//            if (array_key_exists('id', $ship)) {
//                array_push($this->new, $ship['id']);
//            }
//        }
        foreach ($this->new as $new) {
            try {
                $ship = Util::load("ship/$new.json");
            } catch (FileNotFoundException $e) {
                $this->error("$new not found..");
            }
            $sortno = $ship['sort_no'];
            $this->info("【{$ship['name']}】");
            // Min attributes
            $row = DB::select('select min(`level`) as level from ship_attrs where sortno=:sortno', ['sortno' => $sortno]);
            if (count($row) > 0) {
                $level = $row[0]->level;
                $row = $this->getShipAttrByLevel($sortno, $level);
                if (count($row) > 0) {
                    array_push($results, [
                        'id' => $ship['id'],
                        'name' => $ship['name'],
                        'level' => $level,
                        'taisen' => $row[0]->taisen,
                        'kaihi' => $row[0]->kaihi,
                        'sakuteki' => $row[0]->sakuteki
                    ]);
                } else {
                    array_push($missing['min'], $ship['name']);
                }
            } else {
                array_push($missing['min'], $ship['name']);
            }
            // Max attributes
            $row = DB::select('select max(level) as level from ship_attrs where sortno=:sortno', ['sortno' => $sortno]);
            if (count($row) > 0) {
                $level = $row[0]->level;
                $row = $this->getShipAttrByLevel($sortno, $level);
                if (count($row) > 0) {
                    array_push($results, [
                        'id' => $ship['id'],
                        'name' => $ship['name'],
                        'level' => $level,
                        'taisen' => $row[0]->taisen,
                        'kaihi' => $row[0]->kaihi,
                        'sakuteki' => $row[0]->sakuteki
                    ]);
                } else {
                    array_push($missing['max'], $ship['name']);
                }
            } else {
                array_push($missing['max'], $ship['name']);
            }
        }
        Util::dump('report/new.json', $results);
        foreach ($missing['max'] as $name) {
            $this->error("$name max attributes is missing");
        }
        foreach ($missing['min'] as $name) {
            $this->error("$name min attributes is missing");
        }
        $this->info('Done.');
    }

    private function handleNewShipSlotitem() {
        foreach ($this->new as $new) {
            try {
                $ship = Util::load("ship/$new.json");
            } catch (FileNotFoundException $e) {
                $this->error("$new not found..");
            }
            $sortno = $ship['sort_no'];
            $this->info("【{$ship['name']}】");
            $record = InitEquip::where('sortno', $sortno)->first();
            if ($record) {
                $this->info($this->getSlotItemNameById($record->slot1));
                $this->info($this->getSlotItemNameById($record->slot2));
                $this->info($this->getSlotItemNameById($record->slot3));
                $this->info($this->getSlotItemNameById($record->slot4));
                $this->info($this->getSlotItemNameById($record->slot5));
            } else {
                $this->error("database did not has {$ship['name']} data");
            }
        }
        $this->info('Done.');
    }

    private function handleTyku() {
        $results = [];
        $rows = DB::select('select mapId,mapAreaId,cellId from tyku group by mapId,mapAreaId,cellId order by mapId,mapAreaId,cellId');
        foreach ($rows as $r) {
            $maxTyku = DB::select("select max(maxTyku) as max,count(*) as count from tyku where (seiku=3 or seiku=4) and mapId=:mapId and mapAreaId=:mapAreaId and cellId=:cellId", ['mapId' => $r->mapId, 'mapAreaId' => $r->mapAreaId, 'cellId' => $r->cellId]); 
            if($maxTyku[0]->count == 0) {
                $maxTyku = DB::select("select min(maxTyku) as max,count(*) as count from tyku where (seiku=1 or seiku=2 or seiku=0) and mapId=:mapId and mapAreaId=:mapAreaId and cellId=:cellId", ['mapId' => $r->mapId, 'mapAreaId' => $r->mapAreaId, 'cellId' => $r->cellId]);
                if($maxTyku[0]->count == 0) {
                    $maxTyku = -1;
                } else { 
                    $maxTyku = $maxTyku[0]->max;
                }
            } else {
                $maxTyku = $maxTyku[0]->max +1;
            }
            array_push($results, [
                'mapId' => $r->mapId,
                'mapAreaId' => $r->mapAreaId,
                'cellId' => $r->cellId,
                'tyku' => $maxTyku
            ]);
        }
        Util::dump('report/tyku.json', $results);
    }

    private function handleTykuByFleets() {
        $results = [];
        $rows = DB::select('select tyku.mapId,tyku.mapAreaId,tyku.cellId,enemy_fleets.fleets as fleets,count(*) as count from tyku inner join enemy_fleets on tyku.mapId=enemy_fleets.mapId and tyku.mapAreaId=enemy_fleets.mapAreaId and tyku.cellId=enemy_fleets.cellId and tyku.created_at=enemy_fleets.created_at group by mapId,mapAreaId,cellId order by mapId,mapAreaId,cellId');
        foreach ($rows as $r) {
            $maxTyku = DB::select("select max(maxTyku) as max,count(*) as count from tyku inner join enemy_fleets on tyku.mapId=enemy_fleets.mapId and tyku.mapAreaId=enemy_fleets.mapAreaId and tyku.cellId=enemy_fleets.cellId and tyku.created_at=enemy_fleets.created_at where (seiku=0 or seiku=3 or seiku=4) and tyku.mapId=:mapId and tyku.mapAreaId=:mapAreaId and tyku.cellId=:cellId and fleets=:fleets",                ['mapId' => $r->mapId, 'mapAreaId' => $r->mapAreaId, 'cellId' => $r->cellId, 'fleets' => $r->fleets]);
            $minTyku = DB::select("select min(maxTyku) as min,count(*) as count from tyku inner join enemy_fleets on tyku.mapId=enemy_fleets.mapId and tyku.mapAreaId=enemy_fleets.mapAreaId and tyku.cellId=enemy_fleets.cellId and tyku.created_at=enemy_fleets.created_at where (seiku=1 or seiku=2) and tyku.mapId=:mapId and tyku.mapAreaId=:mapAreaId and tyku.cellId=:cellId and fleets=:fleets",
                ['mapId' => $r->mapId, 'mapAreaId' => $r->mapAreaId, 'cellId' => $r->cellId, 'fleets' => $r->fleets]);
            if($maxTyku[0]->count == 0 && $minTyku[0]->count ==0) {
                $tyku = -1;
            } else if($maxTyku[0]->count == 0) {
                $tyku = $minTyku[0]->min;
            } else if($minTyku[0]->count == 0) {
                $tyku = $maxTyku[0]->max+1;
            } else if($maxTyku[0]->max+1>$minTyku[0]->min) {
                $tyku = $maxTyku[0]->max+1;
            } else {
                $tyku = $minTyku[0]->min;
            }
            array_push($results, [
                'mapId' => $r->mapId,
                'mapAreaId' => $r->mapAreaId,
                'cellId' => $r->cellId,
                'fleets' => $r->fleets,
                'tyku' => $tyku,
                'count' => $r->count
            ]);
        }
        Util::dump('report/tyku_by_fleets.json', $results);
    }

    private function handleBattle() {
        $results = DB::select('select mapId, mapAreaId, cellId as cellIds, ships from expedition;');
        Util::dump('report/battle.json', $results);
    }

    private function getSlotItemNameById($id) {
        if ($id == -1 || $id == '-1') return '';
        foreach ($this->slotitems as $item) {
            if ($item['id'] == $id) return $item['name'];
        }
        return '';
    }

    private function getShipAttrByLevel($sortno, $level) {
        return DB::select('select count(*) as counts, sortno, taisen, kaihi, sakuteki, luck, level from ship_attrs where level=:level and sortno=:sortno group by taisen,kaihi,sakuteki,luck order by counts desc limit 1',
            ['sortno' => $sortno, 'level' => $level]);
    }

    private function handlePaths() {
        $results = DB::select('select mapId, mapAreaId, decks, path, count(*) as counts from paths group by mapId, mapAreaId, decks, path');
        Util::dump('report/paths.json', $results);
    }
}
