<?php

class BraveDB extends Brave {

    var $logType = 'Database';
    var $dbo = null;
    static $sqlArray = array();
    var $dsnid = null;
    
    function BraveDB($dsnid=1) {
        //$this->dbo = $this->getDBO($dsnid);
        $this->dsnid = $dsnid;
    }

    function formatData($data) {
        $ext = '';

        foreach ($data as $k => $v) {
            // null
            if ($v === null)
                //$ext .= "`{$k}` = null, ";
                continue;
            // now()
            elseif (preg_match('/^now\(\)$/i', $v))
                $ext .= "`{$k}` = {$v}, ";
            // operation
            elseif (preg_match('/^' . preg_quote($k) . '[\s]*[\+\-]{1}[\s]*/i', $v))
                $ext .= "`{$k}` = {$v}, ";
            // value
            else
                $ext .= "{$k} = '{$v}', ";
        }

        $ext = substr($ext, 0, -2);
        return $ext;
    }

    function buildSelect($table, $select, $where) {
        $sql = '';

        // empty?
        if (empty($select)) {
            return $sql;
        }

        $select = implode('`,`', $select);
        $sql = "SELECT `{$select}` FROM {$table} WHERE TRUE ";

        if (!empty($where)) {
            foreach ($where as $k => $v)
                $sql.= " AND {$k} = '{$v}' ";
        }

        return $sql;
    }

    function buildInsert($table, $data) {
        $sql = '';

        // empty?
        if (empty($data)) {
            return $sql;
        }

        $sql = "INSERT INTO {$table} SET ";
        $sql.= $this->formatData($data);
        return $sql;
    }

    function buildUpdate($table, $data, $where = '') {
        $sql = '';

        // empty?
        if (empty($data)) {
            return $sql;
        }

        if (strpos($table, '.'))
            $sql = "UPDATE {$table} SET ";
        else
            $sql = "UPDATE {$table} SET ";

        $sql.= $this->formatData($data);

        if (strlen($where)) {
            $sql.= ' WHERE ' . $where;
        }

        return $sql;
    }

    function buildReplace($table, $data) {
        $sql = '';

        // empty?
        if (empty($data)) {
            return $sql;
        }

        $sql = "REPLACE INTO `{$table}` SET ";
        $sql.= $this->formatData($data);
        return $sql;
    }

    function buildDelete($table, $where = null) {
        $sql = "DELETE FROM `{$table}`";

        if (strlen($where)) {
            $sql.= ' WHERE ' . $where;
        }

        return $sql;
    }

    function escape(&$var, $quote = false) {
        // escape
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $this->escape($v);
                $var[$k] = $v;
            }
        } else {
            if (is_null($var) || $quote)
                $var = $this->getDBO($this->dsnid)->qstr($var);
            else
                $var = substr($this->getDBO($this->dsnid)->qstr($var), 1, -1);
        }
    }

    function Insert($table, $data) {
        // empty?
        if (empty($data)) {
            return false;
        }

        // escape
        $this->escape($data);

        if ($rs = $this->Execute($this->buildInsert($table, $data)))
            return $this->getDBO($this->dsnid)->Insert_ID();
        else
            return false;
        
    }

    function Update($table, $data, $where = '') {
        // empty?
        if (empty($data) || !is_array($data))
            return false;

        // escape
        $this->escape($data);

        if ($rs = $this->Execute($this->buildUpdate($table, $data, $where)))
            return $rs;
        else
            return false;
    }

    function Replace($table, $data) {
        // empty?
        if (empty($data)) {
            return false;
        }

        // escape
        $this->escape($data);

        if ($rs = $this->Execute($this->buildReplace($table, $data)))
            return $rs;
        else
            return false;
    }

    function Delete($table, $where = '') {
        if ($rs = $this->Execute($this->buildDelete($table, $where)))
            return $rs;
        else
            return false;
    }

    function Execute($sql = "") {
        $this->log($sql);
        if (DEBUG_MODE) {
            self::$sqlArray[] = $sql;
        }
        
        if ($rs = $this->getDBO($this->dsnid)->Execute($sql)) {
            return $rs;
        } else {
            $msg = $this->getDBO($this->dsnid)->ErrorMsg();
            if($this->exception) {
                Throw new Exception("{$msg}<br>{$sql}");
            } else {
                $this->debug("{$msg}<br>{$sql}", E_USER_ERROR);
                return false;
            }
        }
    }

    function getAll($sql, $field = null) {
        if (!$rs = $this->Execute($sql)) {
            return null;
        }

        // result
        $data = array();

        while ($array = $rs->FetchRow()) {
            $data[] = $array;
        }

        if (is_null($field))
            return $data;
        else
            return $this->unique($data, $field);
    }

    function getOne($sql) {
        if (!$rs = $this->Execute($sql)) {
            return null;
        }

        // result
        $data = null;

        while ($array = $rs->FetchRow()) {
            $data = $array;
            break;
        }

        return $data;
    }

    function getTable($table, $select = null, $where = null) {
        // empty?
        if (empty($select)) {
            $select = array('*');
        }

        // escape
        $this->escape($where);

        // get
        $sql = $this->buildSelect($table, $select, $where);
        return $this->getAll($sql);
    }

    function getTableFields($table) {
        $fields = array();
        if (!$table) {
            return array();
        }

        $rs = $this->getAll("DESC {$table}");
        return $this->unique($rs, 'Field', 'Field');
    }

    function Procedure($call, $in, &$out, &$error = array()) {

        $this->log("BraveDB -> Procedure '$call' with in_param:\n" . print_r($in, true) . "and out_param:\n" . print_r($out, true));

        if (DEBUG_MODE) {
            self::$sqlArray[] = $in;
        }

        $inParam = array();
        foreach($in as $k => $v) {
            $inParam[] = "'{$v}'";
        }

        $outParam = array();
        $outSelect = array();
        foreach($out as $k => $v) {
            if (!$this->getDBO($this->dsnid)->Execute("SET @{$k} = '$v'")) {
                $errorNo = $this->getDBO($this->dsnid)->ErrorNo();
                $errorMessage = $this->getDBO($this->dsnid)->ErrorMsg();
                $error['errorNo'] = $errorNo;
                $error['errorMessage'] = $errorMessage;
                $this->log("Set out_param error: " . print_r($error, true));
                return false;
            }
            $outParam[] = "@{$k}";
            $outSelect[] = "@{$k} AS {$k}";
        }

        if ($inParam)
            $sql = "CALL $call(" . implode(', ', $inParam) . ", " . implode(', ', $outParam) . ")";
        else
            $sql = "CALL $call(" . implode(', ', $outParam) . ")";
        $this->log('Call procedure sql: ' . $sql);

        if (!$result = $this->getDBO($this->dsnid)->Execute($sql)) {
            $errorNo = $this->getDBO($this->dsnid)->ErrorNo();
            $errorMessage = $this->getDBO($this->dsnid)->ErrorMsg();
            $error['errorNo'] = $errorNo;
            $error['errorMessage'] = $errorMessage;
            $this->log('Call procedure error: ' . print_r($error, true));
            return false;
        }

        if (is_object($result) && !$result->EOF) {
            $return_data = array();
            while ($row_array = $result->FetchRow()) {
                $return_data[] = $row_array;
            }
            $this->log("Return data:" . print_r($return_data, true));
            $result->NextRecordSet();
        }

        if ($outSelect) {
            $sql = "SELECT " . implode(', ', $outSelect);
            $this->log('Select out_param value sql: ' . $sql);

            if (!$res_out = $this->getDBO($this->dsnid)->Execute($sql)) {
                $errorNo = $this->getDBO($this->dsnid)->ErrorNo();
                $errorMessage = $this->getDBO($this->dsnid)->ErrorMsg();
                $error['errorNo'] = $errorNo;
                $error['errorMessage'] = $errorMessage;
                $this->log('Select out_param value error: ' . print_r($error, true));
                return false;
            }

            while ($array = $res_out->FetchRow()) {
                $out = $array;
                break;
            }
        }

        $return_data = $return_data ? $return_data : true;
        $this->log('Return data: ' . print_r($return_data, true));
        return $return_data;
    }

}

?>
