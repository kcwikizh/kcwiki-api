<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'news';
    protected $hidden = ['updated_at', 'created_at'];
    protected $guarded = [];
}
