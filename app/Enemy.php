<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Enemy extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'enemies';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
