<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Query;
// use PhpMyAdmin\SqlParser\Utils\Formatter;
// use PhpMyAdmin\SqlParser\Component;
// use PhpMyAdmin\SqlParser\Context;
// use PhpMyAdmin\SqlParser\Token;
// use PhpMyAdmin\SqlParser\TokensList;
use App\Models\UrlModel as Url;
use App\Http\Controllers\SchemaController as Schema;

class ParserController extends Controller
{
    public function index(){
    	return view('pages.index');
    }

    public function createDom(Request $req){
      $url    = str_replace('https', 'http', $req->url);
      $schema = new Schema($url);
      if ($schema->is_new()) $schema->create_dom();
      $url_id = $schema->get_url_id();
      return Redirect::to('/url/'.$url_id);
    }

    public function sql_parser(Request $req){
      $query = $req->sql;
      $parser = new Parser($query);
      $flags = Query::getFlags($parser->statements[0]);
      if ($flags['querytype'] == 'SELECT') {
        $from       = strtolower($parser->statements[0]->from[0]->table);
        $expression = $parser->statements[0]->expr;
        $table      = Url::find($req->id)->tables->where('name',$from)->first();
        foreach ($expression as $column) {
          $select[] = $column->expr;
        }
        $data['select'] = $select;
        $data['from']   = $from;
        if($parser->statements[0]->order) $data['order'] = $this->get_order($parser->statements[0]->order);
        $where = $parser->statements[0]->where;
        if ($where != null) {
          $data['where'] = $this->get_where($where);
        }
        $data['type'] = $req->type;
        
        return url('/table/'.$table->id."?".http_build_query($data));
      }
      else{
        echo "query denied";
      }
    }

    public function get_where($where){
      foreach ($where as $w) {
        if ($w->isOperator) {
          $data['operators'][]  = $w->expr;
        }
        else{
          $data['arguments'][]  = $w->expr;
          $data['identifier'][] = $w->identifiers[0];
        }
      }
      return $data;
    }

    public function get_order($order){
      foreach ($order as $key => $order) {
        $data['arguments'][]  = $order->expr->expr;
        $data['type'][]       = $order->type;
      }
      return $data;
    }
}
