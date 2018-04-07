<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\UrlModel as Url;
use App\Models\TabelModel as Table;
use App\Models\Column;
use Illuminate\Support\Facades\DB;

class TableController extends TableParserController
{
    //
    public function getAllUrlTable($id){
    	if (empty($id)) {
    		return Redirect::to('/');
    	}
    	$tables = Url::find($id)->tables;
        if (empty($tables)) {
            return Redirect::to('/');
        }
    	foreach ($tables as $table) {
    		$data['tables'][] = $table;
    		$data['column'][$table->id] = $table->columns;
    	}
    	// Column::where('tabel_id', $table->id)->where('body', 'all', ['2'])->get()
    	// dd($data['5a83699020289d115c003a75'][1]['body'][1]);
    	$data['id'] = $id;
    	
    	return view('pages.tables', $data);
    }

    public function getTable($id, Request $req){
        $table = Table::select('name', 'header')->where('_id', $id)->first();
        $headers = $table->header;
        $table = $this->get_table_and_header($table, $req->select);
        $data['table'] = $table;
        if (isset($req->where)){
            $data['columns'] = $this->get_column_with_where($req->select, $id, $headers, $req->where, $req->order);
        }
        else{
            $data['columns'] = $this->get_column($req->select, $id, $headers, $req->order);
        }
    	return response()->json($data);
    }

    public function get_column($select, $table_id, $header, $order = false){
        if ($order) { if (!$this->valid_order($order['arguments'], $header)) return 'Error'; }
        if (!$this->valid_select($select, $header)) return 'Error';

        $query = Column::query()->where('tabel_id', $table_id);

        if ($order) $this->add_order($query, $order, $header);
        $result     = $this->get_body_column($query->get());

        return $select[0] == '*' || $result == '' ? $result : $this->dynamic_select($result, $GLOBALS['selects']);;
    }

    public function valid_select($selects, $header){
        if ($selects[0] != '*') {
            $GLOBALS['selects'] = $this->map_arguments($selects, $header);
            if ($this->check_false_array($GLOBALS['selects'])) return false;
        }

        return true;
    }

    public function delete_table($url_id){
        Table::where('url_id', $url_id)->delete();
    }

    public function delete_column($table_id){
        Column::where('tabel_id', $table_id)->delete();
    }
}
