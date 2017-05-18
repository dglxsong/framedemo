<?php

class BraveModel extends BraveDB {
    
    function BraveModel($dsnid=1){
        $this->BraveDB($dsnid);
    }

    var $search = null;
    var $paging = array(
        'count' => 7,
        'pages' => 0,
        'page' => 1,
        'one' => 10,
        'order' => '',
        'sort' => 'desc',
        'code' => null,
    );
    var $transField = array();
    var $selectField = array();
    
    /**
    * @desc 
    */
    function transaction($command = 'begin') {
        switch ($command) {
            case 'begin':
                $this->Execute('set autocommit = 0');
                $this->Execute('begin');
                break;
                
            case 'rollback':
                $this->Execute('rollback');
                $this->Execute('set autocommit = 1');
                break;
                
            case 'commit':
                $this->Execute('commit');
                $this->Execute('set autocommit = 1');
                break;
                
            default:
                exit('unknow transaction');
        }
    }
    
    function setSearch($sh) {
        $this->search = $sh;
    }
    
    function paging($key = null) {
        // sh
        $sh = $this->search;
        // code
        $code = $this->code($key);
        $this->paging['code'] = $code;

        // page
        if (isset($sh['page'])) {
            $page = $sh['page'];
            
            if (preg_match('/^[0-9]+$/', $page) && $page) {
                $this->paging['page'] = $page;
            }
        }

        // one
        if (isset($code['one'])) {
            $config = $code['one'];
            $this->setValue($this->paging, 'code.one', $config);

            foreach ($config as $k => $v) {
                if (isset($v['default']) && $v['default']) {
                    $one = $v['value'];
                }
                
                if (isset($sh['one']) && $sh['one'] == $v['value']) {
                    $one = $v['value'];
                    break;
                }
            }
            
            if (isset($one)) {
                $this->paging['one'] = $one;
            }
        }

        // return
        return true;
    }
    
    function paginate($sql, $countSql = null) {
        if (!strlen($sql)) {
            return null;    
        }

        if (!strlen($countSql)) {
            $regex = '/^(.*?)SELECT(.*)FROM(.*?)$/is';

            if (preg_match($regex, $sql)) {
                $countSql = preg_replace($regex, 'SELECT COUNT(*) FROM$3', $sql);
            }
        }
        
        if (!strlen($countSql)) {
            return null;
        }

        $data = array_values($this->getOne($countSql));
        $this->paging['count'] = $data[0];

        if ($this->paging['one'] != 0) {
            if ($this->paging['count'] && $this->paging['one']) {
                $this->paging['pages'] = ceil($this->paging['count'] / $this->paging['one']);
            }

            if ($this->paging['pages'] < $this->paging['page']) {
                $this->paging['page'] = 1;
            }

            $paginate = $this->load(CORE, 'BravePaginate');
            $result = $paginate->parse($this->paging);
            $this->paging = array_merge($this->paging, $result);
            
            $row = ($this->paging['page'] - 1) * $this->paging['one'];
            $offset = $this->paging['one'];
            $sql.= "LIMIT {$row},{$offset}";
        }

        return $this->getAll($sql);
    }
    
    function order(&$sql, $key = null) {
        // sh
        $sh = $this->search;

        // code
        $code = $this->code($key);
        $order = '';
        $sort = 'desc';
        
        // sh
        if (isset($sh['order']) && preg_match('/^([0-9a-z\_\-]+)\.(asc|desc)$/is', $sh['order'], $match)) {
            $sort = $match[2];
        }

        foreach ($code as $k => $v) {
            if (isset($v['default']) && $v['default']) {
                $order = $v['field'];
                $v['sort'] ? $sort = $v['sort'] : null;
            }
            else if ($match && $match[1] == $k) {
                $order = $v['field'];
                $sort = $match[2];
                break;
            }
        }
        
        if (strlen($order)) {
            $sql.= " ORDER BY {$order} {$sort} ";
        }
    }
    
    function where(&$sql, $field, $key, $type, $logic = 'AND') {
        $data = $this->search;
        $where = null;
        
        if (!isset($data[$key]) && !strpos($key, ',')) {
            return $sql;
        }

        if (!is_array($data[$key]) && !strlen($data[$key]) && !strpos($key, ',')) {
            return $sql;
        }

        $value = $data[$key];
        $this->escape($value);

        switch ($type) {
            case 'lk':
                if (is_array($value)) {
                    $where = '(';
                    foreach ($value as $v) {
                        $where .= "{$field} LIKE '%{$v}%' AND ";
                    }
                    $where = rtrim($where,' AND ') .')';
                } else {
                    $where = "{$field} LIKE '%{$value}%'";
                }
                break;
            case 'llk':
                $where = "{$field} LIKE '{$value}%'";
                break;
            case 'rlk':
                $where = "{$field} LIKE '%{$value}'";
                break;
            case 'ibt':
                list($s, $e) = explode(',', $field);
                $where = "('{$value}' BETWEEN {$s} AND {$e})";
                break;
            case 'obt':
                list($s, $e) = explode(',', $key);
                
                if (!$this->isEmpty($data[$s]) && !$this->isEmpty($data[$e])) {
                    $where = "({$field} BETWEEN '{$data[$s]}' AND '{$data[$e]}')";
                }
                break;
            case 'tbt':
                list($s, $e) = explode(',', $key);
                
                if (!$this->isEmpty($data[$s])) {
                    $where[] = "{$field} >= '{$data[$s]} 00:00:00'";
                }
                if (!$this->isEmpty($data[$e])) {
                    $where[] = "{$field} <= '{$data[$e]} 23:59:59'";
                }
                break;
            case 'in':
                if (is_array($value)) {
                    $where = "{$field} IN ('" . implode("','", $value) . "')";
                } 
                else {
                    $where = "{$field} = '{$value}'";
                }
                break;
            case 'inset':
                $value = is_array($value)? $value: explode(',', $value);
                $where = '(';
                foreach((array)$value as $val) {
                    $where .= "FIND_IN_SET('{$val}',{$field}) OR ";
                }
                $where = substr($where,0,-3);
                $where .= ')';
                break;
            case '=':
            case '>':
            case '>=':
            case '<':
            case '<=':
            case '<>':
                $where = "{$field} {$type} '{$value}'";
                break;
            case '&':
                $value = is_array($value)? (int)array_sum($value): (int)$value;
                $where = "{$field} & {$value}";
                break;
            default:
                $this->debug('unknown where type.', E_USER_ERROR);
                break;
        };
        
        if (is_array($where)) {
            $sql .= " {$logic} " . implode(' AND ',$where);
        } elseif (strlen($where)) {
            $sql.= " {$logic} {$where}";
        }
    }
    
    function getSelectFields($unset = true) {
        $selectSql = '';
        $allSelectFields = $this->selectField;
        
        $filter = $allSelectFields['filter'];
        if ($filter && is_array($filter)) {
            unset($allSelectFields['filter']);
            foreach ($filter as $filterInfo) {
                $table = $filterInfo['table'];
                $alias = $filterInfo['alias'];
                $filterFields = preg_split("/,/",$filterInfo['fields'],-1,PREG_SPLIT_NO_EMPTY);
                if ($allSelectFields && $filterFields) {
                    foreach ($filterFields as $field) {
                        if (($index = array_search($alias . '.' . $field,$allSelectFields)) !== false) {
                            unset($allSelectFields[$index]);
                        }
                    }
                    
                    if (array_search($alias . '.*',$allSelectFields) !== false) {
                        $tableFields = $this->getTableFields($table);
                        foreach ($tableFields as $field) {
                            if (in_array($field,$filterFields)) {
                                unset($allSelectFields[$alias . '.' . $field]);
                            } 
                            else {
                                $allSelectFields[] = $alias . '.' . $field;
                            }
                        }
                        
                        if (($index = array_search($alias . '.*',$allSelectFields)) !== false) {
                            unset($allSelectFields[$index]);
                        }
                    }
                }
            }
        }
        
        if ($unset) {
            $this->selectField = null;
        }
        
        return $allSelectFields ? implode(',',$allSelectFields) : null;
    }
}

?>
