<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\UrlModel as Url;
use App\Models\TabelModel as Table;
use App\Models\Column;
use Illuminate\Support\Facades\DB;

class TableParserController extends Controller
{
    public function check_false_array($array){
    	foreach ($array as $arr) {
    		if ($arr === false) return true;
    	}
    	return false;
    }

    public function map_arguments($argument, $header){
        $func = function($value){
                    return strtolower(str_replace(' ', '', $value));
                };       
        foreach ($argument as $w) {
            $index_argument[] = array_search($func($w), array_map($func, $header));
        }
        return $index_argument;
    }

    public function explode_where($where){
        preg_match_all("/(!=)|(<>)|(=)|(>=)|(<=)|(>)|(<)/", $where, $matches);
        return $matches[0][0];
    }

    public function partially_header($header_partial, $all_header, $index_header = false){
    	$header_index = $this->map_arguments($header_partial, $all_header);
    	if ($index_header) return $header_index;
        foreach ($header_index as $key => $header) {
            $headers[$key] = $all_header[$header];
        }
        return $headers;
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

    public function get_column_with_where($selects, $id, $header, $where){
	    $GLOBALS['where_index'] = $this->map_arguments($where['identifier'], $header); 
	    if ($this->check_false_array($GLOBALS['where_index'])) return 'Error';

	    if ($selects[0] != '*') {
	    	$selects = $this->map_arguments($selects, $header);
	        if ($this->check_false_array($selects)) return 'Error';
	    }

	    foreach ($where['arguments'] as $key => $args) {
            $args 								= str_replace(' ', '', $args);
            $delimiter 							= $this->explode_where($args);
            $GLOBALS['where_condition'][] 		= explode($delimiter, $args);
            $GLOBALS['where_condition'][$key][] = $delimiter;
        }
        $GLOBALS['operators'] = isset($where['operators']) ? $where['operators'] : false;
        $results	= $this->get_column_collection($id);
        $result 	= $this->get_body_column($results);

        return $selects[0] == '*' || $result == '' ? $result : $this->dynamic_select($result, $selects);
    }

    public function get_column_collection($table_id, $order = false){
    	$result = Column::query()->where('tabel_id', $table_id)->where(function ($query){
			        	$this->dynamic_where($query, $GLOBALS['operators'], $GLOBALS['where_index'], $GLOBALS['where_condition'], true);
			        });
    	if ($order) $this->add_order($result);
    	return $result->get();
    }

    public function get_body_column($columns_table){
    	if (sizeof($columns_table) == 0) return '';
        foreach ($columns_table as $column) {
            $columns[] = $column['body'];
        }
        return $columns;
    }

    public function dynamic_select($columns_table, $column_index){
    	$i = 0;
    	foreach ($columns_table as $column) {
            foreach ($column_index as $col_index) {
                $columns[$i][] = $column[$col_index];
            }
            $i++;
        }
        return $columns;
    }

    public function dynamic_where($query, $operators, $where_index, $where_condition){
    	for ($i=0; $i < sizeof($where_index) ; $i++) {
            if ($i == 0) {
                $this->query_and($query, $where_index, $where_condition, $i);
            }
            $this->add_where($operators, $query, $where_index, $where_condition, $i);
        }
    }

    public function add_where($operators, $query, $where_index, $where_condition, $i){
    	if (isset($operators[$i-1])) {
            if ($operators[$i-1] == 'AND') {
            	$this->query_and($query, $where_index, $where_condition, $i);
            }
            elseif ($operators[$i-1] == 'OR') {
                $this->query_or($query, $where_index, $where_condition, $i);
            }
        }
    }

    public function query_and($query, $where_index, $where_condition, $i){
    	$query->where('body.'.$where_index[$i], $where_condition[$i][2], is_numeric($where_condition[$i][1]) ? intval($where_condition[$i][1]) : $where_condition[$i][1]);
    }

    public function query_or($query, $where_index, $where_condition, $i){
    	$query->orWhere('body.'.$where_index[$i], $where_condition[$i][2], is_numeric($where_condition[$i][1]) ? intval($where_condition[$i][1]) : $where_condition[$i][1]);
    }

}
