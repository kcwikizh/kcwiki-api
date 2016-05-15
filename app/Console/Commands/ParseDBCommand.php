<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use DB;

class ParseDB extends Command
{

    protected $signature = 'parse:db {option}';

    protected $description = 'Generate enemy data from kandb database';

    private $slotitems = [];

    public function handle()
    {
        switch ($this->argument('option')) {
            case 'enemy':
                $ships = json_decode(Storage::disk('local')->get('ship/all.json'), true);
                $this->slotitems = json_decode(Storage::disk('local')->get('slotitem/all.json'), true);
                $enemy_equips = [];
                foreach ($ships as $ship) {
                    if ($ship['id'] < 500 || count($ship['name']) <= 0) continue;
                    echo "【{$ship['name']}】\n";
                    $row = DB::select('select count(*) as counts,slot1,slot2,slot3,slot4 from enemies where enemyId=:id group by enemyId,slot1,slot2,slot3,slot4 order by counts desc limit 1',
                        ['id' => $ship['id']]);
                    if (count($row) > 0) {
                        $enemy_equip = [];
                        $enemy_equip['id'] = $ship['id'];
                        $enemy_equip['name'] = $ship['name'];
                        $enemy_equip['slots'] = [
                            $row[0]->slot1,
                            $row[0]->slot2,
                            $row[0]->slot3,
                            $row[0]->slot4
                        ];
                        $enemy_equip['slot_names'] = [
                            $this->getSlotItemNameById($row[0]->slot1),
                            $this->getSlotItemNameById($row[0]->slot2),
                            $this->getSlotItemNameById($row[0]->slot3),
                            $this->getSlotItemNameById($row[0]->slot4)
                        ];
                        array_push($enemy_equips, $enemy_equip);
                        $this->info("Hit");
                    } else {
                        $this->error("Missing.");
                    }
                }
                Storage::disk('local')->put('initequip/enemy.json', json_encode($enemy_equips));
                $this->info('Done.');
                break;
        }
    }

    private function getSlotItemNameById($id) {
        if ($id == -1 || $id == '-1') return;
        foreach ($this->slotitems as $item) {
            if ($item['id'] == $id) return $item['name'];
        }
    }
}