<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class EnemyFleet extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'enemy_fleets';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
