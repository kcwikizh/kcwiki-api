<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use DB;

class ParseDB extends Command
{

    protected $signature = 'parse:db {option}';

    protected $description = 'Generate game data from kandb database';

    private $slotitems = [];

    public function handle()
    {
        $ships = json_decode(Storage::disk('local')->get('ship/all.json'), true);
        $this->slotitems = json_decode(Storage::disk('local')->get('slotitem/all.json'), true);
        switch ($this->argument('option')) {
            case 'initequip':
                $equips = [];
                $missing = [];
                $upgraded_ships = [];
                foreach ($ships as $ship) {
                    if (array_key_exists('after_ship_id', $ship))
                        $upgraded_ships[$ship['after_ship_id']] = true;
                }
                foreach ($ships as $ship) {
                    if ($ship['id'] >= 500 || count($ship['name']) <= 0 || array_key_exists($ship['id'], $upgraded_ships)) continue;
                    echo "【{$ship['name']}】\n";
                    $row = DB::select('select count(*) as counts,slot1,slot2,slot3,slot4 from init_equips where sortno=:sortno group by sortno,slot1,slot2,slot3,slot4 order by counts desc limit 1',
                        ['sortno' => $ship['sort_no']]);
                    if (count($row) > 0) {
                        $equip = [];
                        $equip['id'] = $ship['id'];
                        $equip['name'] = $ship['name'];
                        $equip['slots'] = [
                            $row[0]->slot1,
                            $row[0]->slot2,
                            $row[0]->slot3,
                            $row[0]->slot4
                        ];
                        $equip['slot_names'] = [
                            $this->getSlotItemNameById($row[0]->slot1),
                            $this->getSlotItemNameById($row[0]->slot2),
                            $this->getSlotItemNameById($row[0]->slot3),
                            $this->getSlotItemNameById($row[0]->slot4)
                        ];
                        array_push($equips, $equip);
                        Storage::disk('local')->put("initequip/{$ship['id']}.json", json_encode($equip));
                        $this->info('Hit');
                    } else {
                        $this->error("Missing.");
                        array_push($missing, ['name' => $ship['name'], 'id' => $ship['id']]);
                    }
                }
                Storage::disk('local')->put('initequip/all.json', json_encode($equips));
                Storage::disk('local')->put('initequip/missing.json', json_encode($missing));
                $this->info('Done.');
                break;
            case 'enemy':
                $enemy_equips = [];
                $missing = [];
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
                        array_push($missing, ['name' => $ship['name'], 'id' => $ship['id']]);
                    }
                }
                Storage::disk('local')->put('initequip/enemy.json', json_encode($enemy_equips));
                Storage::disk('local')->put('initequip/enemy_missing.json', json_encode($missing));
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