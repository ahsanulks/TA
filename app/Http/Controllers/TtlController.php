<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL as GetURL;
use App\Models\UrlModel as Url;
use Carbon\Carbon;

class TtlController extends Controller
{

	private $url;
	private $type;
	
	public function __construct($url, $type) {
		$this->url 	= Url::where('url', $url)->first() ?? false;
		$this->type = $type;
    }

	public function is_expired(){
		date_default_timezone_set('Asia/Jakarta');
		$date 		= date('Y-m-d H:i:s', time());
		$expired 	= $this->convert_ttl($this->url->ttl - 1, $this->url->updated_at);
		return $date > $expired ? true : false;
	}

	public function update_ttl($params){
		$this->url->ttl = $this->ttl_table($params);
		$this->url->save();
		return true;
	}

	private function ttl_table($params){
		return ($params == 'lowest') ? $this->set_lowest() : $this->increment($this->url->ttl, $this->type);
	}

	private function increment($ttl, $type){
		$increment = $this->$type($ttl);
		return $increment < Url::TTL_MAX ? $increment : Url::TTL_MAX;
	}

	private function set_lowest(){
		return Url::TTL_MIN;
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

	private function linear($ttl){
		return $ttl + (Url::TTL_C_LINEAR * $ttl);
	}

	private function polynomial($ttl){
		return $ttl + (pow($ttl, Url::TTL_C_POLYNOMIAL));
	}

	private function exponential($ttl){
		return $ttl + (pow(Url::TTL_C_EXPONENTIAL, $ttl));
	}
}