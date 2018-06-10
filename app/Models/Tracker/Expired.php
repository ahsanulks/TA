<?php

namespace App\Models\Tracker;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Expired extends Eloquent
{
    protected $table = 'expired';
    public $timestamps = true;

    protected $fillable = [
        'url_id',
        'count',
        'ttl_type'
    ];
}