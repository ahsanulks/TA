<?php

namespace App\Models\Tracker;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Compare extends Eloquent
{
  protected $table = 'compare';
  public $timestamps = true;

  protected $fillable = [
      'url_id',
      'ttl_type',
      'different_md5', //array(), first index is old, second index is the new one
      'is_same'
  ];
}