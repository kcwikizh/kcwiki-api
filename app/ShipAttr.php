<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ShipAttr extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ship_attrs';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
