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