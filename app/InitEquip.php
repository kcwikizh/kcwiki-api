<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class InitEquip extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'init_equips';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
