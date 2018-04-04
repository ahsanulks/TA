<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\UrlModel as Url;
use App\Models\TabelModel as Table;
use App\Models\Column;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
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
    	unset($table->_id);
    	$data['table'] = $table;
        if (isset($req->where) && sizeof($req->where) == 1) {
            $where = explode(' ', $req->where);
            if (sizeof($where) == 3) {
                if (isset($req->order)) {
                    $data['columns'] = $this->get_column($req->select, $id, $table->header, $where, $req->order, $req->order_type);
                }
                else{
                    $data['columns'] = $this->get_column($req->select, $id, $table->header, $where);
                }
            }
        }
        else if (isset($req->where) && sizeof($req->where) > 1){
            $data['columns'] = $this->get_column_with_complex_where($req->select, $id, $table->header, $req->where);
        }
        else{
            if (isset($req->order)) {
                $data['columns'] = $this->get_column($req->select, $id, $table->header, null, $req->order, $req->order_type);
            }
            else{
                $data['columns'] = $this->get_column($req->select, $id, $table->header);   
            }
        }   	
    	return response()->json($data);
    } 

    public function check_false_array($array){
    	foreach ($array as $arr) {
    		if ($arr === false) {
    			return true;
    		}
    	}
    	return false;
    }

    public function explode_where($where){
        preg_match_all("/(!=)|(<>)|(=)|(>=)|(<=)|(>)|(<)/", $where, $matches);
        return $matches[0][0];
    }

    public function get_column_with_complex_where($selects, $id, $header, $where){
        foreach ($where['arguments'] as $key => $args) {
            $args = str_replace(' ', '', $args);
            $delimiter = $this->explode_where($args);
            $GLOBALS['where_condition'][] = explode($delimiter, $args);
            $GLOBALS['where_condition'][$key][] = $delimiter;
        }
        $GLOBALS['operators'] = $where['operators'];
        if ($selects[0] == '*') {
            $GLOBALS['where_index'] = $this->map_arguments($where['identifier'], $header); 
            if ($this->check_false_array($GLOBALS['where_index'])) return 'Error';
            $results = Column::query()->where('tabel_id', $id)->where(function ($query){
                $i = 0;
                for ($i=0; $i < sizeof($GLOBALS['where_index']) ; $i++) {
                    if ($i == 0) {
                        $query->where('body.'.$GLOBALS['where_index'][$i], $GLOBALS['where_condition'][$i][2], is_numeric($GLOBALS['where_condition'][$i][1]) ? intval($GLOBALS['where_condition'][$i][1]) : $GLOBALS['where_condition'][$i][1]);
                    } 
                    if (isset($GLOBALS['operators'][$i-1])) {
                        if ($GLOBALS['operators'][$i-1] == 'AND') {
                            $query->where('body.'.$GLOBALS['where_index'][$i], $GLOBALS['where_condition'][$i][2], is_numeric($GLOBALS['where_condition'][$i][1]) ? intval($GLOBALS['where_condition'][$i][1]) : $GLOBALS['where_condition'][$i][1]);
                        }
                        elseif ($GLOBALS['operators'][$i-1] == 'OR') {
                            $query->orWhere('body.'.$GLOBALS['where_index'][$i], $GLOBALS['where_condition'][$i][2], is_numeric($GLOBALS['where_condition'][$i][1]) ? intval($GLOBALS['where_condition'][$i][1]) : $GLOBALS['where_condition'][$i][1]);
                        }
                    }
                }
            });
            return $results->get();
        }
        return Column::where('tabel_id', $id)->get();
    }

    public function map_arguments($where, $header){
        $func = function($value){
                    return strtolower(str_replace(' ', '', $value));
                };       
        foreach ($where as $w) {
            $index_where[] = array_search($func($w), array_map($func, $header));
        }
        return $index_where;
    }

    public function get_column($selects, $id, $header, $where = null, $order = null, $order_type = null){
        $func = function($value){
                    return strtolower(str_replace(' ', '', $value));
                };
        if ($selects[0] == '*') {
            if ($where != null) {
                $index_where = array_search($func($where[0]), array_map($func,$header));
                if ($index_where === FALSE) {
                    return 'Error';
                }
                else{
                    if ($order != null) {
                        $index_order = array_search($func($order), array_map($func,$header));
                        if ($index_order === FALSE) {
                            return 'Error';
                        }
                        else{
                            $columns_table = Column::where('tabel_id', $id)->where('body.'.$index_where, $where[1], is_numeric($where[2]) ? intval($where[2]) : $where[2])->orderBy('body.'.$index_order, $order_type)->get();
                        }
                    }
                    else{
                        $columns_table = Column::where('tabel_id', $id)->where('body.'.$index_where, $where[1], is_numeric($where[2]) ? intval($where[2]) : $where[2])->get();
                    }
                }
            }
            else{
                if ($order != null) {
                    $index_order = array_search($func($order), array_map($func,$header));
                    if ($index_order === FALSE) {
                        return 'Error';
                    }
                    else{
                        $columns_table = Column::where('tabel_id', $id)->orderBy('body.'.$index_order, $order_type)->get();
                    }
                }
                else{
                    $columns_table = Column::where('tabel_id', $id)->get();
                }
            }
            foreach ($columns_table as $column) {
                $columns[] = $column['body'];
            }
            return isset($columns) ? $columns : 'Table not found';
        }
        else{
            foreach ($selects as $select) {
                $column_index[] = array_search(strtolower($select), array_map($func, $header));
            }
            if ($this->check_false_array($column_index) === FALSE) {
                $i = 0;
                if ($order != null) {
                    $index_order = array_search($func($order), array_map($func,$header));
                    if ($index_order === FALSE) {
                        return 'Error';
                    }
                    else{
                        $columns_table = Column::where('tabel_id', $id)->orderBy('body.'.$index_order, $order_type)->get();
                    }
                }
                else{
                    $columns_table = Column::where('tabel_id', $id)->get();
                }
                foreach ($columns_table as $column) {
                    foreach ($column_index as $col_index) {
                        $columns[$i][] = $column['body'][$col_index];
                    }
                    $i++;
                }
                return $columns;
            }
            else{
                return 'Error';
            }
        }
    }
}
