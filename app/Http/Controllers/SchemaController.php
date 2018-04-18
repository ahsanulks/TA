<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPHtmlParser\Dom;
use App\Models\UrlModel as Url;
use Illuminate\Support\Facades\Config;

class SchemaController extends Controller
{
	private $url;
	private $dom;
	private $temp_md5;

	public function __construct($url) {
		$this->url = Url::firstOrNew(['url' => $url]);
		$this->dom = new Dom();
		$this->url->string_page = $this->dom->loadFromUrl($this->url->url)->outerHTML;
		$this->temp_md5 = md5($this->url->string_page);
	}

	public function create_dom(){
		if ($this->is_new()) {
			$this->schema_definition();
			$url->md5 = $this->temp_md5;
			$url->ttl = Config::get('constants.TTL.t_min');
			$url->save();
		}
		else{
			$this->schema_definition(); //dipindahkan ke dalam if atas.
		}
	}

	public function is_new(){
		return $this->url->md5 === $this->temp_md5 ? false : true;
	}

	private function schema_definition(){
		//masih error
		// dd($this->url->tables[0]['name']);
		$tables = $this->dom->find('table');
		foreach ($tables as $key => $table) {
			$url_id 		= $this->url->id;
			$name 			= 'table_'.$key;
			$header_data	= $this->get_headers($tables);
			$number_column	= $header_data['number_column'];
			$header 		= $header_data['header'];
			// $number_row = $row;
			$this->url->tables[$key]['name'] == $name ? $this->update_table($name, $header, $number_column, $number_row, $key) : 'null';
		}
	}

	private function update_table($name, $header, $number_column, $number_row, $i){
		$this->url->tables[$i]['name']			= $name;
		$this->url->tables[$i]['header']		= $header;
		$this->url->tables[$i]['number_column']	= $number_column;
		$this->url->tables[$i]['number_row']	= $number_row;
		$this->url->tables[$i]->save();
	}

	private function get_headers($tables){
		$headers = $tables->find('th');
		$number_column = 0;
        $data['header'] = Array();
        foreach ($headers as $header) {
        	$colspan = $header->getAttribute('colspan');
        	if ($colspan != null && $colspan > 1) {
        		$temp = $header->text;
        		for ($i=0; $i < $colspan ; $i++) { 
        			$data['header'][] = $temp . "_$i";
        			$number_column++;
        		}
        	}
        	else{
        		$data['header'][] = $header->text;
        		$number_column++;
        	}
        }
        $data['number_column'] = $number_column;
        return $data;
	}
}
