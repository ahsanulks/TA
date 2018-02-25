<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Models\TabelModel;

class UrlModel extends Eloquent
{
    //
    protected $table = 'url';
    public $timestamps = true;

    protected $fillable = [
        'url',
        'time_to_live',
        'string_page'
    ];

    public function tables(){
    	return $this->hasMany('App\Models\TabelModel', 'url_id');
    }
}
