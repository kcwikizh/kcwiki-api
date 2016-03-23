<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Tyku extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tyku';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
