<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Expedition extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'expedition';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
