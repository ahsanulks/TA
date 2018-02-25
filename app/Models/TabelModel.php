<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Models\UrlModel;

class TabelModel extends Eloquent
{
    //
    protected $table = 'tabel';
    public $timestamps = true;

    protected $fillable = [
    	'url_id',
        'name',
        'number_column',
        'number_row',
        'header',
        'body'
    ];

    public function url(){
        return $this->belongsTo('App\Models\UrlModel');
    }

    public function columns(){
        return $this->hasMany('App\Models\Column', 'tabel_id');
    }
}
