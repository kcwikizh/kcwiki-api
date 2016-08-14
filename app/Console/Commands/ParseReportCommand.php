<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Util;
use DB;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ParseReport extends Command
{
    protected $signature = 'parse:report {option}';

    protected $description = 'Generate data from kcwiki-report';

    // Target enemy ships
    private $enemies = ["重巡夏姫", "港湾夏姫", "港湾夏姫-壊", "潜水夏姫", "戦艦夏姫"];

    // Target new ships (Ship ID)
    private $new = [439, 364, 444, 365, 481, 366, 483, 367];

    public function handle()
    {
        $this->slotitems = Util::load('slotitem/all.json');
        switch ($this->argument('option')) {
            case 'enemy':
                $this->handleEnemies();
                break;
            case 'new':
                $this->handleNewShip();
        }
    }

    private function handleEnemies() {
        $results = [];
        $missing = [];
        $ships = Util::load('ship/all.json');
        foreach ($this->enemies as $enemy) {
            $found = false;
            foreach ($ships as $ship) {
                if ($ship['name'] == $enemy) {
                    array_push($results, $ship);
                    $found = true;
                }
            }
            if (!$found) array_push($missing, $enemy);
        }
        foreach ($results as &$result) {
            $id = $result['id'];
            $this->info("【{$result['name']}】");
            $row = DB::select('select count(*) as counts,id,enemyId,maxHP,slot1,slot2,slot3,slot4,slot5,houg,raig,tyku,souk from enemies where enemyId=:enemyId group by enemyId,slot1,slot2,slot3,slot4,slot5 order by counts desc limit 1',
                ['enemyId' => $id]);
            if (count($row) > 0) {
                $result['slots'] = [
                    $this->getSlotItemNameById($row[0]->slot1),
                    $this->getSlotItemNameById($row[0]->slot2),
                    $this->getSlotItemNameById($row[0]->slot3),
                    $this->getSlotItemNameById($row[0]->slot4),
                    $this->getSlotItemNameById($row[0]->slot5)
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
}