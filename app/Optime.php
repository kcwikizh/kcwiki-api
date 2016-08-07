<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Optime extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'optime';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
