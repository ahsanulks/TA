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
    	// Column::where('tabel_id', $table->id)->where('body', 'all', ["2"])->get()
    	// dd($data['5a83699020289d115c003a75'][1]['body'][1]);
    	$data['id'] = $id;
    	
    	return view('pages.tables', $data);
    }

    public function getTable($id, Request $req){
    	$table = Table::select('name', 'header')->where('_id', $id)->first();
    	unset($table->_id);
    	$data['table'] = $table;
        if (isset($req->where)) {
            $where = explode(" ", $req->where);
            if (sizeof($where) == 3) {
                if (isset($req->order)) {
                    $data['columns'] = $this->get_column($req->select, $id, $table->header, $where, $req->order, $req->order_type);
                }
                else{
                    $data['columns'] = $this->get_column($req->select, $id, $table->header, $where);
                }
            }
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

    public function get_column($selects, $id, $header, $where = null, $order = null, $order_type = null){
        $func = function($value){
                    return strtolower(str_replace(" ", "", $value));
                };
        if ($selects[0] == "*") {
            if ($where != null) {
                $index_where = array_search($func($where[0]), array_map($func,$header));
                if ($index_where === FALSE) {
                    return "Error";
                }
                else{
                    if ($order != null) {
                        $index_order = array_search($func($order), array_map($func,$header));
                        if ($index_order === FALSE) {
                            return "Error";
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
                        return "Error";
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
            return isset($columns) ? $columns : "Table not found";
        }
        else{
            foreach ($selects as $select) {
                $column_index[] = array_search(strtolower($select), array_map($func, $header));
            }
            if ($this->check_false_array($column_index) === FALSE) {
                $i = 0;
                $columns_table = Column::where('tabel_id', $id)->get();
                foreach ($columns_table as $column) {
                    foreach ($column_index as $col_index) {
                        $columns[$i][] = $column['body'][$col_index];
                    }
                    $i++;
                }
                return $columns;
            }
            else{
                return "Error";
            }
        }
    }
}
