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
        preg_match_all('/(!=)|(<>)|(=)|(>=)|(<=)|(>)|(<)|(in)|(notin)|(between)|(notbetween)/', $where, $matches);
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

    public function valid_where_and_select($selects, $where, $header){
    	$GLOBALS['where_index'] = $this->map_arguments($where['identifier'], $header); 
	    if ($this->check_false_array($GLOBALS['where_index'])) return false;

	    if ($selects[0] != '*') {
	    	$GLOBALS['selects'] = $this->map_arguments($selects, $header);
	        if ($this->check_false_array($GLOBALS['selects'])) return false;
	    }

	    return true;
    }

    public function valid_select($selects, $header){
        if ($selects[0] != '*') {
            $GLOBALS['selects'] = $this->map_arguments($selects, $header);
            if ($this->check_false_array($GLOBALS['selects'])) return false;
        }

        return true;
    }

    public function valid_order($order, $header){
    	$order_index = $this->map_arguments($order, $header);
    	if ($this->check_false_array($order_index)) return false;
    	return true;
    }

    public function get_condition($delimiter, $args){
    	if ($delimiter == 'between' || $delimiter == 'notbetween') {
    		$temp = explode($delimiter, $args); 
    		$data = [$temp[0], explode('and', $temp[1])];
    	}
    	elseif($delimiter == 'in' || $delimiter == 'notin'){
    		$temp     = explode($delimiter, $args);
    		$temp[1]  = str_replace(['[', ']'], '', $temp[1]); 
    		$data     = [$temp[0], explode(',', $temp[1])];
    	}
    	else{
    		$data = explode($delimiter, $args);
    	}
    	return $data;
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

    public function query_and($query, $where_index, $where_condition, $i){
    	$query->where('body.'.$where_index[$i], $where_condition[$i][2], is_numeric($where_condition[$i][1]) ? intval($where_condition[$i][1]) : $where_condition[$i][1]);
    }

    public function query_or($query, $where_index, $where_condition, $i){
    	$query->orWhere('body.'.$where_index[$i], $where_condition[$i][2], is_numeric($where_condition[$i][1]) ? intval($where_condition[$i][1]) : $where_condition[$i][1]);
    }

    public function query_between($query, $where_index, $where_condition, $i){
        if ($where_condition[$i][2] == 'between') {
        	$query->whereBetween('body.'.$where_index[$i], $this->array_is_numeric($where_condition[$i][1]));
        }
        else{
            $data       = $this->array_is_numeric($where_condition[$i][1]);
            $data[0]    = $data[0] - 1;
            $data[1]    = $data[1] + 1;
            $query->whereNotBetween('body.'.$where_index[$i], $data);
        }
    }

    public function query_in($query, $where_index, $where_condition, $i){
        if ($where_condition[$i][2] == 'in') {
            $query->whereIn('body.'.$where_index[$i], $this->array_is_numeric($where_condition[$i][1]));
        }
        else{
            $query->whereNotIn('body.'.$where_index[$i], $this->array_is_numeric($where_condition[$i][1]));
        }
    }

    public function array_is_numeric($array){
        sort($array);
    	foreach ($array as $arr) {
    		$data[] = is_numeric($arr) ? intval($arr) : $arr;
    	}
    	return $data;
    }

    public function add_order($query, $order, $header){
    	$order['index'] = $this->map_arguments($order['arguments'], $header);
    	foreach ($order['index'] as $key => $order_index) {
    		$query->orderBy('body.'.$order_index, $order['type'][$key]);
    	}
    }
}
