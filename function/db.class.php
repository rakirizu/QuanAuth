<?php

/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2017-10-15
 * Time: 12:07
 * 温泉专用数据库类库
 */


class db
{
    /*
     * 链接初始化
     */
    public $connection;

    function __construct($server, $username, $password, $dbname,$prot = 3306,$charset = 'utf8')
    {
        if (!$this->connection = new mysqli($server, $username, $password, $dbname, $prot)) {
            die('MySQL Connect Error (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);
        }
        if ($this->connection->connect_error) {
            die('MySQL Connect Error (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);
        }
        $this->connection->set_charset($charset);
        $this->connection->query('set names ' . $charset);
    }

    /*
     * select 返回第一条数据
     */
    public function select_first_row($table, $fields, $where, $wheretype)
    {
        $sql = 'SELECT ';
        $fidtmp = '';
        if (is_array($fields)) {
            foreach ($fields as $value) {
                if (empty($fidtmp)) {
                    $fidtmp = $value;
                } else {
                    $fidtmp .= ',' . $value;
                }
            }
        } else {
            $fidtmp = $fields;
        }

        $sql .= $fidtmp;
        unset($fidtmp);
        $sql .= ' FROM ' . $table;
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $condition = '';
            $condition = $this->wheretosql($where, $wheretype);
            $sql .= $condition . ' LIMIT 1';
            unset($condition);
        }

        //echo $sql;
        if (!$result = $this->connection->query($sql)) {
            $this->posterror($this->geterror(),'warning');
            //$this->insert_back_id('sq_log_system', array('time' => time(), 'ip' => get_real_ip(), 'msg' => '数据库错误：' . $this->geterror(), 'type' => 'danger'));
            return false;
        } else {
            if ($result->num_rows === 0) {
                return false;
            }
            return $result->fetch_array();
        }
    }

    /*
     * select 返回所有数据
     */

    public function wheretosql($where, $wheretype)
    {
        $condition = '';
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                if (empty($condition)) {
                    $condition = $key . '=\'' . addslashes($value) . '\'';
                } else {
                    $condition .= ' ' . $wheretype . ' ' . $key . '=\'' . addslashes($value) . '\'';
                }
            }
            return $condition;
        } else {
            return $where;
        }

    }


    /*
     * 获取某个select行数
     */

    public function insert_back_id($table, $data)
    {
    	
        $return = $this->query('SHOW FULL COLUMNS FROM ' . $table);
        while ($info = $return->fetch_assoc()) {
        	if(empty($data[$info['Field']]) ){
	            if ($info['Field'] == 'ID' or $info['Field'] == 'id' or $info['Field'] == 'Id' or $info['Field'] == 'iD') {
	                $data[$info['Field']] = '{nullvaluereplace}';
	            } else if (stristr($info['Type'], 'int') || stristr($info['Type'], 'bigint') || stristr($info['Type'], 'integer') || stristr($info['Type'], 'double')) {
	                $data[$info['Field']] = '{intvaluereplace}';
	            } else {
	                $data[$info['Field']] = '';
	            }
        	}
        	
        }
		$column = '';
		$value = '';
        foreach ($data as $k => $v) {
            
            if (empty($column)) {
                $column = $k;
                if ($v == '{nullvaluereplace}') {
                    $value = 'NULL';
                } else if ($v == '{intvaluereplace}') {
                    $value = '0';
                } else {
                	if(is_int($v) || is_float($v) || is_double($v)){
                		$value =  $v ;
                	}else{
                    	$value = '\'' . addslashes($v) . '\'';
                	}
                }
            } else {
                $column .= ',' . $k;
                if ($v == '{nullvaluereplace}') {
                    $value .= ',NULL';
                } else if ($v == '{intvaluereplace}') {
                    $value .= ',0';
                } else {
                	if(is_int($v) || is_float($v) || is_double($v)){
                		 $value .= ', ' . addslashes($v) ;
                	}else{
                    	 $value .= ', \'' . addslashes($v) . '\'';
                	}
                   
                }
			
            }
        }

        $sql = 'INSERT INTO ' . $table . ' (' . $column . ') VALUES (' . $value . ')';
        //echo $sql;
        if (!$this->connection->query($sql)) {
            //echo $this->geterror();
            return false;
        } else {
            return $this->connection->insert_id;
        }
    }

    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    /*
     * 执行查询语句(update)，成功返回受影响的行数
     */

    public function geterror()
    {
        return $this->connection->error;
    }


    /*
     * 插入数据到一个表，返回插入的ID
     */

    public function select_all_row($table, $fields = '*', $where = '', $wheretype = 'AND', $order = '')
    {
        $sql = 'SELECT ';
        $fidtmp = '';
        if (is_array($fields)) {
            foreach ($fields as $value) {
                if (empty($fidtmp)) {
                    $fidtmp = $value;
                } else {
                    $fidtmp .= ',' . $value;
                }
            }
        } else {
            $fidtmp = $fields;
        }
        $sql .= $fidtmp;
        unset($fidtmp);
        $sql .= ' FROM ' . $table;
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $condition = '';
            $condition = $this->wheretosql($where, $wheretype);
            $sql .= $condition;
            unset($condition);
        }

        $return = array();

        $sql .= $order;

        if (!$result = $this->connection->query($sql)) {
            //$this->insert_back_id('sq_log_system', array('time' => time(), 'ip' => get_real_ip(), 'msg' => '数据库错误：' . $this->geterror(), 'type' => 'danger'));
            $this->posterror($this->geterror(),'warning');
            return false;
        } else {
            while ($info = $result->fetch_array()) {
                $return[] = $info;
            }
            $result->free();
            return $return;
        }
    }


    /*
     * 插入多条数据到表，返回插入的行数
     */

    public function select_count_row($table, $where = '', $wheretype = '')
    {
        $sql = 'SELECT COUNT(ID) FROM ' . $table;
        if (!empty($where)) {
            if (is_array($where)) {
                $condition = ' WHERE ' . $this->wheretosql($where, $wheretype);
                $sql .= $condition;
            } else {
                $condition = ' WHERE ' . $where;
                $sql .= $condition;
            }

        }


        unset($condition);

        if (!$result = $this->connection->query($sql)) {
            //$this->insert_back_id('sq_log_system', array('time' => time(), 'ip' => get_real_ip(), 'msg' => '数据库错误：' . $this->geterror(), 'type' => 'danger'));
            $this->posterror($this->geterror(),'warning');
            return false;
        } else {

            $array = $result->fetch_array();

            return intval($array[0]);
        }

    }


    /*
     * 删除记录
     */

    public function select_limit_row($table, $fields, $limit = '', $num = 0, $where = '', $wheretype = 'AND', $order = '')
    {
        $sql = 'SELECT ';
        $fidtmp = '';
        if (is_array($fields)) {
            foreach ($fields as $value) {
                if (empty($fidtmp)) {
                    $fidtmp = $value;
                } else {
                    $fidtmp .= ',' . $value;
                }
            }
        } else {
            $fidtmp = $fields;
        }
        $sql .= $fidtmp;
        unset($fidtmp);
        $sql .= ' FROM ' . $table;
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $condition = '';
            $condition = $this->wheretosql($where, $wheretype);
            $sql .= $condition;
            unset($condition);
        }
        $sql .= ' ' . $order;
        $return = array();

        if (empty($limit)) {
            if (!empty($num)) {
                $sql .= ' LIMIT ' . $num;
            }
        } else {
            if (!empty($num)) {
                $sql .= ' LIMIT ' . $limit . ',' . $num;
            }
        }

        if (!$result = $this->connection->query($sql)) {
            //$this->insert_back_id('sq_log_system', array('time' => time(), 'ip' => get_real_ip(), 'msg' => '数据库错误：' . $this->geterror(), 'type' => 'danger'));
            $this->posterror($this->geterror(),'warning');
            return false;
        } else {
            while ($info = $result->fetch_array()) {
                $return[] = $info;
            }
            $result->free();
            return $return;
        }
    }


    /*
     * 获取最后一次出现的错误信息
     */

    public function update($table, $where, $wheretype, $newdata)
    {
        $newdatatmp = '';
        foreach ($newdata as $key => $value) {
            if (is_int($value) || is_double($value) || is_float($value)){
                $value = round($value,2);
            }
            if (empty($newdatatmp)) {
                $newdatatmp = $key . '=\'' . addslashes($value) . '\'';
            } else {
                $newdatatmp .= ', ' . $key . '=\'' . addslashes($value) . '\'';
            }
        }

        $condition = $this->wheretosql($where, $wheretype);

        $sql = 'UPDATE ' . $table . ' SET ' . $newdatatmp . ' WHERE ' . $condition;
        //echo $sql;
        if (!$result = $this->connection->query($sql)) {
            //$this->insert_back_id('sq_log_system', array('time' => time(), 'ip' => get_real_ip(), 'msg' => '数据库错误：' . $this->geterror(), 'type' => 'danger'));
            $this->posterror($this->geterror(),'warning');
            return false;
        } else {
            if ($this->connection->affected_rows === 0) {
                return false;
            }
            return $this->connection->affected_rows;
        }


    }

    public function insert_back_row($table, $k, $v)
    {
        $return = $this->query('SHOW FULL COLUMNS FROM ' . $table);
        while ($info = $return->fetch_assoc()) {
            $bool = false;
            foreach ($k as $value) {
                if ($value === $info['Field']) {
                    $bool = true;
                    break;
                }
            }
            if (!$bool) {
                $k[] = $info['Field'];
                foreach ($v as $key => $value) {
                    if ($info['Field'] == 'ID' or $info['Field'] == 'id' or $info['Field'] == 'Id' or $info['Field'] == 'iD') {
                        $v[$key] .= ',NULL';
                    } else if (stristr($info['Type'], 'int') || stristr($info['Type'], 'bigint') || stristr($info['Type'], 'integer') || stristr($info['Type'], 'double')) {
                        $v[$key] .= ',0';
                    } else {

                        $v[$key] .= ',\'\'';
                    }

                }
            }
        }

        $datainfo = '';
        foreach ($v as $value) {
            if ($datainfo === '') {
                $datainfo = '(' . $value . ')';
            } else {
                $datainfo .= ',(' . $value . ')';
            }

        }
        $sql = 'INSERT INTO ' . $table . ' (' . $this->arraytocomma($k) . ') VALUES ' . $datainfo;
        //echo $sql;
        if (!$this->connection->query($sql)) {
            //$this->insert_back_id('sq_log_system', array('time' => time(), 'ip' => get_real_ip(), 'msg' => '数据库错误：' . $this->geterror(), 'type' => 'danger'));
            $this->posterror($this->geterror(),'warning');
            return false;
        } else {
            return $this->connection->affected_rows;
        }
    }

    private function arraytocomma($array)
    {
        $back = '';
        foreach ($array as $value) {
            if ($back === '') {
                $back = $value;
            } else {
                $back .= ', ' . $value;
            }
        }
        return $back;
    }

    public function delete($table, $where, $wheretype)
    {
        $condition = $this->wheretosql($where, $wheretype);
        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $condition;
        if (!$this->connection->query($sql)) {
            return false;
        } else {
            return true;
        }
    }
    public  function  affected_num(){
        return $this->connection->affected_rows;
    }

    /**
     * @param $msg
     * @param string $type info | warning | success | danger
     */
    public function posterror($msg,$type = "info"){
        $this->insert_back_id('sq_log_system', array('time' => time(), 'ip' => get_real_ip(), 'msg' => $msg, 'type' => $type));
    }
}