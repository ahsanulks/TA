<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Column extends Eloquent
{
    protected $table = 'column';
    public $timestamps = true;

    protected $fillable = [
        'tabel_id',
        'body'
    ];
}
