<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class MapMaxHp extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'map_max_hp';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
