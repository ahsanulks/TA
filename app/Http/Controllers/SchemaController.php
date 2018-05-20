<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPHtmlParser\Dom;
use App\Models\UrlModel as Url;
use App\Models\TabelModel as Tabel;
use App\Models\Column;
use App\Http\Controllers\TtlController as Ttl;

class SchemaController extends Controller
{
	private $url;
	private $dom;
	private $temp_md5;
	private $not_have_thead;

	public function __construct($url) {
		$this->url = Url::firstOrNew(['url' => $url]);
		$this->dom = new Dom();
		$this->url->string_page = $this->dom->loadFromUrl($this->url->url)->outerHTML;
		$this->temp_md5 = md5($this->url->string_page);
	}

	public function create_dom(){
		$this->url->md5 = $this->temp_md5;
		$this->url->ttl = Url::TTL_MIN;
		$this->url->save();
		$this->schema_definition();
	}

	public function update_dom(){
		$ttl = new Ttl($this->url->url);
		if ($this->is_new()) {
			$this->url->md5 = $this->temp_md5;
			$ttl->update_ttl('lowest');
			$this->url->save();
			$this->schema_definition();
		}
		else{
			$ttl->update_ttl('increment');
			$this->url->save();
		}
	}

	public function is_new(){
		return $this->url->md5 === $this->temp_md5 ? false : true;
	}

	public function get_url_id(){
		return $this->url->id;
	}

	private function to_lower_and_underscore($array){
		$func = function($value){
                    return strtolower(str_replace(' ', '_', $value));
                };
		return array_map($func, $array);
	}

	private function schema_definition(){
		$tables = $this->dom->find('table');
		foreach ($tables as $key => $table) {
			$url_id 	= $this->url->id;
			$name 		= 'table_'.$key;
			$header		= $this->to_lower_and_underscore($this->get_headers_data($table));
			$table_id	= $this->update_table($url_id, $name, $header, $key);
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
			$data['name']		= $name;
			$data['header']	= $header;
			$table 					= Tabel::create($data);
			$id = $table->id;
		}
		return $id;
	}

	private function update_column($table, $table_id){
		$tbody = $table->find('tbody', 0);
		if (is_null($tbody)) {
			$rows = $table->find('tr');
		}
		else{
			$rows = $tbody->find('tr');
		}
		$data['tabel_id'] = $table_id;

		$i = 0;
		if ($this->not_have_thead) $i = 1;

		for ($i; $i < sizeof($rows); $i++) { 
			$body[] = $this->get_body_data($rows[$i]);
		}
		
		$this->delete_column($table_id);
		$chunk_body = array_chunk($body, 1);
		foreach ($chunk_body as $body) {
			$data['body'] = $body[0];
			Column::create($data);
		}
	}

	private function get_body_data($dom){
		$row_th = $dom->find('th', 0);
		if (is_null($row_th)) {
			$data[] = $this->get_data_table($dom, 'td');
		}
		else{
			$data[] = strip_tags($row_th->innerHtml);
			$data[] = $this->get_data_table($dom, 'td');
		}
		return flatten($data);
	}

	private function get_headers_data($dom){
		$thead = $dom->find('thead', 0);
		if (is_null($thead)){
			$this->not_have_thead 	= true;
			$row 					= $dom->find('tr', 0);
			$th 					= $this->get_data_table($row, 'th');
		}
		else {
			$row 	= $thead->find('tr');
			$th 	= sizeof($row) == 0 ? $this->get_data_table($thead, 'th') : $this->get_data_table($row, 'th');
		}
		return $th;
	}

	private function get_data_table($dom, $search, $i = 0){
		$datas = (sizeof((array) $dom) != 10) ? $dom[$i]->find($search) : $dom->find($search);
		$array = array();
		$rowspan = array();
		foreach ($datas as $data) {
			$colspantemp[] 	= $data->getAttribute('colspan'); 
			$colspan 				= $data->getAttribute('colspan');
			$rowspan[]			= $data->getAttribute('rowspan');
			$array[]				= $this->get_colspan_data($colspan, $data);
		}
		$uniq_rowspan = array_unique($rowspan);
		if (sizeof($uniq_rowspan) > 1 && in_array(null, $uniq_rowspan, TRUE)) {
			$index_rowspan = array_keys($rowspan, null, true);
			$temp = $this->get_data_table($dom, $search, $i+1);
			$array[$index_rowspan[0]] = $this->set_colspan($array[$index_rowspan[0]], $temp, max($colspantemp));
		}
		return flatten($array);
	}

	public function set_colspan($repeated, $aim, $repeat=1){
		$repeated	= is_string($repeated) ? $repeated : reset($repeated);
		$temp 		= array();
		for($i = 0; $i < $repeat; $i++){
			$temp[] = $repeated . ' ' . $aim[$i];
		}
		return $temp;
	}

	private function get_colspan_data($colspan, $column){
		if ($colspan != null && $colspan > 1) {
    		$temp 	= fix_numeric_data(strip_tags($column->innerHtml));
    		$data = $this->copy_data($temp, $colspan);
    	}
    	else{
    		$data = fix_numeric_data(strip_tags($column->innerHtml));
    	}
    	return $data;
	}

	private function copy_data($data, $colspan){
		for ($i = 0; $i < $colspan ; $i++) { 
			$copy_data[] = $data;
		}
		return $copy_data;
	}

    private function delete_column($table_id){
        Column::where('tabel_id', $table_id)->delete();
    }
}
