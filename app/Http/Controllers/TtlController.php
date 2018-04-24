<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL as GetURL;
use App\Models\UrlModel as Url;
use Carbon\Carbon;

class TtlController extends Controller
{

	private $url;
	
	public function __construct($url) {
		$this->url 	= Url::where('url', $url)->first() ?? false;
    }

	public function is_expired(){
		date_default_timezone_set('Asia/Jakarta');
		$date 		= date('Y-m-d H:i:s', time());
		$expired 	= $this->convert_ttl($this->url->ttl, $this->url->updated_at);
		return $date > $expired ? true : false;
	}

	public function update_ttl($params){
		$this->url->ttl = $this->ttl_table($params);
		$this->url->save();
		return true;
	}

	private function ttl_table($params){
		return ($params == 'lowest') ? $this->set_lowest() : $this->increment($this->url->ttl);
	}

	private function increment($ttl){
		$c 			= Config::get('constants.TTL.c');
		$increment 	= $ttl + (pow($c, $ttl));
		return $increment < 7 ? $increment : 7;
	}

	private function set_lowest(){
		return Config::get('constants.TTL.t_min');
	}

	private function convert_ttl($ttl, $updated_at){
		$temp 		= explode('.', $ttl);
		$hour 		= isset($temp[1]) ? ('0.'.$temp[1]) * 60 : 0;
		$temp2 		= explode('.', $hour);
		$minutes 	= isset($temp2[1]) ? ('0.'.$temp2[1]) * 60 : 0;
		
		$expired 	= Carbon::parse($updated_at)->addDay($temp[0]);
		$expired->addHours($hour);
		$expired->addMinutes($minutes);
		return $expired;
	}
}