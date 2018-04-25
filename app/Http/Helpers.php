<?php

if (! function_exists('flatten')) {

    function flatten($array){
	    $return = array();
	    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
	    return $return;
	}
}

if (! function_exists('fix_numeric_data')) {

   function fix_numeric_data($number){
    	$temp = str_replace('.', ',', $number);
        if (is_numeric($number) && strpos($temp, ',')) {
            $data = (float) $number;
        }
        elseif (is_numeric($number)) {
            $data = intval($number);
        }
        else{
            $data = $number;
        }
        return $data;
    }
}