<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Models\TabelModel;

class UrlModel extends Eloquent
{
    //
    const TTL_TYPE          = ['linear', 'polynomial', 'exponential'];
    const TTL_MIN           = 1;
    const TTL_MAX           = 8;
    const TTL_C_LINEAR      = 1/5;
    const TTL_C_POLYNOMIAL  = -1;
    const TTL_C_EXPONENTIAL = 1;

    protected $table = 'url';
    public $timestamps = true;

    protected $fillable = [
        'url',
        'md5',
        'ttl',
        'string_table'
    ];

    public function tables(){
    	return $this->hasMany('App\Models\TabelModel', 'url_id');
    }
}
