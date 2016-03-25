<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Path extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'paths';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
