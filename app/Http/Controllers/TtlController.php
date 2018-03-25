<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL as GetURL;

class ParserController extends Controller
{
	public function increment(Request $req){
		$c = Config::get('contant.TTL.c');
		$increment = $current_ttl + (pow($c,$current_ttl));
		return $increment;
	}
}