<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPHtmlParser\Dom;
use App\Models\UrlModel as Url;
use Illuminate\Support\Facades\Config;
use App\Models\TabelModel as Tabel;
use App\Models\Column;

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
		$this->url->md5 = $this->temp_md5;
		$this->url->ttl = Config::get('constants.TTL.t_min');
		$this->url->save();
		$this->schema_definition();
	}

	public function update_dom(){
		$this->url->md5 = $this->temp_md5;
		$this->url->save();
		$this->schema_definition();
	}

	public function is_new(){
		return $this->url->md5 === $this->temp_md5 ? false : true;
	}

	public function get_url_id(){
		return $this->url->id;
	}

	private function schema_definition(){
		$tables = $this->dom->find('table');
		foreach ($tables as $key => $table) {
			$url_id 		= $this->url->id;
			$name 			= 'table_'.$key;
			$header			= $this->get_data_table($table, 'th');
			$table_id		= $this->update_table($url_id, $name, $header, $key);
			$this->update_column($table, $table_id);
		}
	}

	private function update_table($url_id, $name, $header, $i){
		if (sizeof($this->url->tables) > 0) {
			$this->url->tables[$i]->name	= $name;
			$this->url->tables[$i]->header	= $header;
			$this->url->tables[$i]->save();
			$id = $this->url->tables[$i]->id;
		}
		else{
			$data['url_id']	= $url_id;
			$data['name']	= $name;
			$data['header']	= $header;
			$table 			= Tabel::create($data);
			$id = $table->id;
		}
		return $id;
	}

	private function update_column($table, $table_id){
		$rows = $table->find('tr');
		$data['tabel_id'] = $table_id;
		foreach ($rows as $key => $row) {
			$body[] = $this->get_data_table($row, 'td');
		}
		$this->delete_column($table_id);
		$chunk_body = array_chunk($body, 1);
		foreach ($chunk_body as $body) {
			$data['body'] = $body[0];
			Column::create($data);
		}
	}

	private function get_data_table($dom, $search){
		$datas = $dom->find($search);
		foreach ($datas as $data) {
			$colspan 	= $data->getAttribute('colspan');
			$array[]	= $this->get_colspan_data($colspan, $data);
		}
		return $this->flatten($array);
	}

	private function get_colspan_data($colspan, $column){
		if ($colspan != null && $colspan > 1) {
    		$temp = $column->text;
    		for ($i = 0; $i < $colspan ; $i++) { 
    			$data[] = $temp . "_$i";
    		}
    	}
    	else{
    		$data = $column->text;
    	}
    	return $data;
	}

	private function flatten($array){
	    $return = array();
	    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
	    return $return;
	}

    private function delete_column($table_id){
        Column::where('tabel_id', $table_id)->delete();
    }
}
