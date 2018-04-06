<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPHtmlParser\Dom;
use App\Models\UrlModel as Url;
use App\Models\TabelModel as Tabel;
use App\Models\Column;
use Illuminate\Support\Facades\Redirect;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Query;
use PhpMyAdmin\SqlParser\Utils\Formatter;
use PhpMyAdmin\SqlParser\Component;
use PhpMyAdmin\SqlParser\Context;
use PhpMyAdmin\SqlParser\Token;
use PhpMyAdmin\SqlParser\TokensList;
use Illuminate\Support\Facades\URL as GetURL;

class ParserController extends Controller
{
    public function index(){
    	return view('pages.index');
    }

    public function createDom(Request $req){
    	$dom = new Dom();
    	$url = Url::firstOrNew(['url' => $req->url]);
      $string_page = $dom->loadFromUrl($req->url)->outerHTML;
      $md5 = md5($string_page);
      if ($md5 === $url->md5) {
        return Redirect::to('/url/'.$url->id);
      }
      else{
        $url->string_page = $string_page;
        $url->md5 = $md5;
        $url->save(); 
      }
      $this->schema_definition($string_page, $url);
    }

    public function schema_definition($string, $url){
      $dom = new Dom();
      $dom->load($string);
      $tables = $dom->find('table');
    	$j = 1;
        foreach ($tables as $table) {
          $data['url_id'] = $url->id;
          $data['name'] = 'table_'.$j;
    	    $number_column = 0;
          //get header table
          $headers = $table->find('th');
          $data['header'] = Array();
	        foreach ($headers as $header) {
	        	$colspan = $header->getAttribute('colspan');
	        	if ($colspan != null && $colspan > 1) {
	        		$temp = $header->text;
	        		for ($i=0; $i < $colspan ; $i++) { 
	        			$data['header'][] = $temp;
	        			$number_column++;
	        		}
	        	}
	        	else{
	        		$data['header'][] = $header->text;
	        		$number_column++;
	        	}
	        }

          $data['number_column'] = $number_column;
          $tabel = Tabel::firstOrNew($data);
          $tabel->save();
	      //get body table
          $i=0;
          $tr = $table->find('tr');
          foreach ($tr as $row) {
          	$kolom = Array();
            $tds = $row->find('td');
            foreach ($tds as $td) {
            	$kolom[] = 	is_numeric($td->text) ? intval($td->text) : $td->text;    
            }
            Column::create([
              'tabel_id' => $tabel->id,
              'body' => $kolom
            ]);
            $i++;
          }
          $tabel->number_row = $i;
          $tabel->save();
          $j++;
        }
        return Redirect::to('/url/'.$url->id);
    }

    public function sql_parser(Request $req){
      $query = $req->sql;
      $parser = new Parser($query);
      $flags = Query::getFlags($parser->statements[0]);
      if ($flags['querytype'] == 'SELECT') {
        $from = strtolower($parser->statements[0]->from[0]->table);
        $expression = $parser->statements[0]->expr;
        $table = Url::find($req->id)->tables->where('name',$from)->first();
        foreach ($expression as $column) {
          $select[] = $column->expr;
        }
        $data['select'] = $select;
        $data['from'] = $from;
        $where = $parser->statements[0]->where;
        if ($where != null) {
          $data['where'] = $this->get_where($where);
        }
        if ($parser->statements[0]->order != null) {
          $data['order'] = $parser->statements[0]->order[0]->expr->expr;
          $data['order_type'] = $parser->statements[0]->order[0]->type;
        }
        return Redirect::to('/table/'.$table->id."?".http_build_query($data));
      }
      else{
        echo "access denied";
      }
    }

    public function get_where($where){
      foreach ($where as $w) {
        if ($w->isOperator) {
          $data['operators'][] = $w->expr;
        }
        else{
          $data['arguments'][] = $w->expr;
          $data['identifier'][] = $w->identifiers[0];
        }
      }
      return $data;
    }
}
