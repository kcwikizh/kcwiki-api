<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Util;
use DB;

class ParseReport extends Command
{
    protected $signature = 'parse:report {option}';

    protected $description = 'Generate data from kcwiki-report';

    // Target enemy ships
    private $enemies = ["重巡夏姫", "港湾夏姫", "港湾夏姫-壊", "潜水夏姫", "戦艦夏姫"];

    public function handle()
    {
        $ships = Util::load('ship/all.json');
        $this->slotitems = Util::load('slotitem/all.json');
        $results = [];
        $missing = [];
        switch ($this->argument('option')) {
            case 'enemy':
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
                break;
        }
    }

    private function getSlotItemNameById($id) {
        if ($id == -1 || $id == '-1') return '';
        foreach ($this->slotitems as $item) {
            if ($item['id'] == $id) return $item['name'];
        }
        return '';
    }

}