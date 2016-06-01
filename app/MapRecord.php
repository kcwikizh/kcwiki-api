<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class MapRecord extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'map_records';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
