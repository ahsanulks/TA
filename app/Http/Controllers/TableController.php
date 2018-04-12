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

    public function get_column_with_where($selects, $id, $header, $where, $order = false){
        if (!$this->valid_where_and_select($selects, $where, $header)) return 'Error';
        if ($order) { if (!$this->valid_order($order['arguments'], $header)) return 'Error'; }
        foreach ($where['arguments'] as $key => $args) {
            $args                               = str_replace(' ', '', $args);
            $delimiter                          = $this->explode_where($args);
            $GLOBALS['where_condition'][]       = $this->get_condition($delimiter, $args);
            $GLOBALS['where_condition'][$key][] = $delimiter;
        }
        $GLOBALS['operators'] = isset($where['operators']) ? $where['operators'] : false;
        $results    = $this->get_column_collection($id, $header, $order);
        $result     = $this->get_body_column($results);

        return $selects[0] == '*' || $result == '' ? $result : $this->dynamic_select($result, $GLOBALS['selects']);
    }

    public function get_table_and_header($table, $select){
        unset($table->_id);
        if ($select[0] != '*') {
            $headers = $this->partially_header($select, $table->header);
            unset($table->header);
            $table->header = $headers;
        }
        return $table;
    }

    public function get_column_collection($table_id, $header, $order = false){
        $result = Column::query()->where('tabel_id', $table_id)->where(function ($query){
                        $this->dynamic_where($query, $GLOBALS['operators'], $GLOBALS['where_index'], $GLOBALS['where_condition'], true);
                    });
        if ($order) $this->add_order($result, $order, $header);
        return $result->get();
    }

    public function delete_table($url_id){
        Table::where('url_id', $url_id)->delete();
    }

    public function delete_column($table_id){
        Column::where('tabel_id', $table_id)->delete();
    }

    public function dynamic_where($query, $operators, $where_index, $where_condition){
        for ($i=0; $i < sizeof($where_index) ; $i++) {
            if ($i == 0) {
                $this->first_where($operators, $query, $where_index, $where_condition, $i);
            }
            $this->add_where($operators, $query, $where_index, $where_condition, $i);
        }
    }

    public function first_where($operators, $query, $where_index, $where_condition, $i){
        if ($where_condition[$i][2] == 'between' || $where_condition[$i][2] == 'notbetween') {
            $this->query_between($query, $where_index, $where_condition, $i);
        }
        elseif ($where_condition[$i][2] == 'in' || $where_condition[$i][2] == 'notin'){
            $this->query_in($query, $where_index, $where_condition, $i);
        }
        else{
            $this->query_and($query, $where_index, $where_condition, $i);
        }
    }

    public function add_where($operators, $query, $where_index, $where_condition, $i){
        if (isset($operators[$i-1])) {
            $this->choose_where($operators, $query, $where_index, $where_condition, $i);
        }
    }

    public function where_between_condition($query, $operators, $i){
        if ($operators[$i-1] == 'AND') {
            $this->query_between($query, $GLOBALS['where_index'], $GLOBALS['where_condition'], $i);
        }
        else{
            $GLOBALS['i'] = $i;
            $query->orWhere(function ($query2) {
                $this->query_between($query2, $GLOBALS['where_index'], $GLOBALS['where_condition'], $GLOBALS['i']);
            });
        }
    }

    public function where_in_condition($query, $operators, $i){
        if ($operators[$i-1] == 'AND') {
            $this->query_in($query, $GLOBALS['where_index'], $GLOBALS['where_condition'], $i);
        }
        else{
            $GLOBALS['i'] = $i;
            $query->orWhere(function ($query2) {
                $this->query_in($query2, $GLOBALS['where_index'], $GLOBALS['where_condition'], $GLOBALS['i']);
            });
        }
    }

    public function choose_where($operators, $query, $where_index, $where_condition, $i){
        if ($where_condition[$i][2] == 'between' || $where_condition[$i][2] == 'notbetween') {
            $this->where_between_condition($query, $operators, $i);
        }
        elseif ($where_condition[$i][2] == 'in' || $where_condition[$i][2] == 'notin'){
            $this->where_in_condition($query, $operators, $i);
        }
        else{
            $this->where_condition($operators, $query, $where_index, $where_condition, $i);
        }
    }

    public function where_condition($operators, $query, $where_index, $where_condition, $i){
        if ($operators[$i-1] == 'AND') {
            $this->query_and($query, $where_index, $where_condition, $i);
        }
        elseif ($operators[$i-1] == 'OR') {
            $this->query_or($query, $where_index, $where_condition, $i);
        }
    }
}
