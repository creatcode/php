<?php
class DB {
	private $adaptor;
	private $_condition = array();
    private $_query_sql = '';
	protected $comparison = array(
		'eq' => '=',
		'neq' =>'<>',
		'gt' => '>',
		'egt' => '>=',
		'lt' => '<',
		'elt' => '<=',
		'notlike' => 'NOT LIKE',
		'like' => 'LIKE',
		'in' => 'IN',
		'not in' => 'NOT IN'
	);
	
	protected $selectSql = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%INDEX%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%';
	
	public function __construct($adaptor, $hostname, $username, $password, $database, $port = null) {
		$class = 'DB\\' . $adaptor;
		
		if (class_exists($class)) {
			$this->adaptor = new $class($hostname, $username, $password, $database, $port);
		} else {
			throw new \Exception('Error: Could not load database adaptor ' . $adaptor . '!');
		}
	}

	public function __call($method, $args)
    {
        if (in_array(strtolower($method), array('on', 'lock', 'master', 'distinct', 'index', 'attr', 'key'), true)) {
            $this->_condition[strtolower($method)] = $args[0];
            return $this;
        } elseif (in_array(strtolower($method), array('min', 'max', 'count', 'sum', 'avg'), true)) {
            $field = isset($args[0]) ? $args[0] : '*';
            return $this->get_field(strtoupper($method) . '(' . $field . ') AS rc_' . $method);
        } else {
            $error = 'DB ERROR : Function ' . $method . ' is not exists!';
            throw new Exception($error);
        }
    }

	public function connection($db_name) {
        $this->adaptor->select_db($db_name);
        return $this;
    }

    public function query($sql, $params = array()) {
        $this->_query_sql = $sql;
		return $this->adaptor->query($sql, $params);
	}
	
	public function escape($value) {
		return $this->adaptor->escape($value);
	}
	
	public function countAffected() {
		return $this->adaptor->countAffected();
	}
	
	public function getLastId() {
		return $this->adaptor->getLastId();
	}
	
	public function connected() {
		return $this->adaptor->connected();
	}
	
	public function begin() {
		$this->adaptor->begin();
	}
	
	public function rollback() {
		$this->adaptor->rollback();
	}
	
	public function commit() {
		$this->adaptor->commit();
	}
	
	public function getTransactionNum() {
		$this->adaptor->getTransactionNum();
	}

	public function getLastSql() {
        return $this->_query_sql;
    }
	
	public function insert($data, $replace = false) {
		$values = $fields = array();
		foreach ($data as $key => $val) {
			$value = $this->_dealValue($val);
			if (is_scalar($value)) {
				$values[] = $value;
				$fields[] = $this->_dealKey($key);
			}
		}
		$this->_query_sql = $sql = ($replace ? 'REPLACE ' : 'INSERT ') . $this->_dealAttr() . ' INTO ' . $this->_dealTable() . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
		$this->query($sql);
		return $this->getLastId();
	}
	
	public function insertAll($data, $replace = false) {
		$first = reset($data);
		if (!is_array($first)) return false;
		$cols = array_keys($first);
		array_walk($field, array($this, '_dealKey'));
		$vals = array();
		foreach ($data as $value) {
			$val = array();
			foreach($value as $item) {
				$item = $this->_dealValue($item);
				if (is_scalar($item)) {
					$val[] = $item;
				}
			}
			$vals[] = '(' . implode(", ", $val) . ')';
		}
        $this->_query_sql = $sql = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->_dealTable() . ' (' . implode(',', $first) . ') VALUES ' . implode(',', $vals);
		$query = $this->query($sql);
		return $query->countAffected();
	}
	
	public function select() {
		return $this->getRows($this->_dealSelectSql());
	}
	
	public function find() {
		$rows = $this->select();
		return isset($rows[0]) ? $rows[0] : false;
	}
	
	public function update($data) {
        if (empty($data)) return false;
		$where = $this->_dealWhere();
        $sql = 'UPDATE '
			. $this->_dealAttr()
			. $this->_dealTable()
			. $this->_dealSet($data)
			. $where
			. $this->_dealOrder()
			. $this->_dealLimit();
		if ($where == '') {
			return false;
		}
        $this->_query_sql = $sql;
        $this->query($sql);
		return $this->countAffected();
	}
	
	public function getRows($sql) {
		$query = $this->query($sql);
        $this->_query_sql = $sql;
		return $query->rows;
	}

    public function getRow($sql) {
        $query = $this->query($sql);
        $this->_query_sql = $sql;
        return $query->row;
    }
	
	public function delete() {
		$where = $this->_dealWhere();
        if ($where == '') return false;
        $this->_query_sql = $sql = 'DELETE ' . $this->_dealAttr() . ' FROM '
            . $this->_dealTable()
            . $this->_dealWhere()
            . $this->_dealOrder()
            . $this->_dealLimit();
        $this->query($sql);
        return $this->countAffected();
	}
	
	public function where($where) {
		$this->_condition['where'] = $where;
		return $this;
	} 
	
	public function table($tables) {
		if (strpos($tables, ',') !== false) {
            $temp_table = array();
			foreach (explode(',', $tables) as $table) {
                array_push($temp_table, DB_PREFIX . $table);
			}
			$this->_condition['table'] = implode(',', $temp_table);
		} else {
			$this->_condition['table'] = DB_PREFIX . $tables;
		}
		return $this;
	}
	
	public function order($order) {
		$this->_condition['order'] = $order;
		return $this;
	}
	
	public function limit($limit) {
		$this->_condition['limit'] = $limit;
		return $this;
	}
	
	public function field($field) {
		$this->_condition['field'] = $field;
		return $this;
	}
	
	public function setInc($field, $step = 1) {
		return $this->set_field($field, array('exp', $field . '+' . $step));
	}
	
	public function setDec($field, $step = 1) {
		return $this->set_field($field, array('exp', $field . '-' . $step));
	}
	
	public function set_field($field, $value) {
		if (is_array($field)) {
			$data = $field;
		} else {
			$data[$field] = $value;
		}
		return $this->update($data);
	}

	public function get_field($field, $sep = null) {
        $this->_condition['field'] = $field;
        if (strpos($field, ',')) { //多字段
            $resultSet = $this->select();
            if (!empty($resultSet)) {
                $_field = explode(',', $field);
                $field = array_keys($resultSet[0]);
                $move = $_field[0] == $_field[1] ? false : true;
                $key = array_shift($field);
                $key2 = array_shift($field);
                $cols = array();
                $count = count($_field);
                foreach ($resultSet as $result) {
                    $name = $result[$key];
                    if ($move) { //删除键值记录
                        unset($result[$key]);
                    }
                    if (2 == $count) {
                        $cols[$name] = $result[$key2];
                    } else {
                        $cols[$name] = is_null($sep) ? $result : implode($sep, $result);
                    }
                }
                return $cols;
            }
        } else {
            $this->_condition['limit'] = 1;
            $result = $this->select();
            if (!empty($result)) {
                return reset($result[0]);
            }
        }
        return null;
    }
	
	public function group($group) {
		$this->_condition['group'] = $group;
		return $this;
	}

    public function having($having) {
        $this->_condition['having'] = $having;
        return $this;
    }
	
	public function join($join) {
		if (false !== strpos($join, ',')) {
			foreach (explode(',', $join) as $key => $val) {
				if (in_array(strtolower($val), array('left', 'inner', 'right'))) {
					$this->_condition['join'][] = strtoupper($val) . ' JOIN';
				} else {
					$this->_condition['join'][] = 'LEFT JOIN';
				}
			}
		} elseif (in_array(strtolower($join), array('left', 'inner', 'right'))) {
			$this->_condition['join'][] = strtoupper($join) . ' JOIN';
		}
		return $this;
	}
	
	protected function _dealSelectSql() {
		$sql = $this->selectSql;
		$sql = str_replace(
			array('%TABLE%', '%DISTINCT%', '%FIELD%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%INDEX%'),
			array(
				$this->_dealTable(),
				$this->_dealDistinct(),
				$this->_dealField(),
				$this->_dealJoin(),
				$this->_dealWhere(),
				$this->_dealGroup(),
				$this->_dealHaving(),
				$this->_dealOrder(),
				$this->_dealLimit(),
				$this->_dealUnion(),
				$this->_dealIndex()
			), $sql);
		$sql .= $this->_dealLock();
		// 清除所有条件
		$this->_condition = array();
		return $sql;
	}
	
	protected function _dealAttr() {
		if (isset($this->_condition['attr'])) {
			if (in_array($this->_condition['attr'], array('LOW_PRIORITY','QUICK','IGNORE','HIGH_PRIORITY','SQL_CACHE','SQL_NO_CACHE'))) {
				return $this->_condition['attr'] . ' ';
			}
		}
		return '';
	}
	
	protected function _dealIndex() {
		if (!isset($this->_condition['index'])) return '';
		return empty($this->_condition['index']) ? '' : ' USE INDEX (' . $this->_condition['index'] . ') ';
	}
	
	protected function _dealGroup() {
		$group = '';
		if (!isset($this->_condition['group'])) return $group;
		if (!empty($this->_condition['group'])) {
			$group = " GROUP BY {$this->_condition['group']}";
		}
		$this->_clearCondition('group');
		return $group;
	}
	
	protected function _dealOrder() {
		$order = '';
		if (!isset($this->_condition['order'])) return $order;
		if (!empty($this->_condition['order'])) {
			$order = " ORDER BY {$this->_condition['order']}";
		}
		$this->_clearCondition('order');
		return $order;
	}
	
	protected function _dealLimit() {
		if (!isset($this->_condition['limit'])) {
			return '';
		}
		return !empty($this->_condition['limit']) ? ' LIMIT ' . $this->_condition['limit'] . ' ' : '';
	}
	
	protected function _clearCondition($key) {
		$this->_condition[$key] = null;
	}
	
	protected function _dealJoin() {
		$joinStr = '';
		if (!isset($this->_condition['on'])) return $joinStr;
		if (false === strpos($this->_condition['table'], ',')) return '';
		$table = explode(',', $this->_condition['table']);
		$on = explode(',', $this->_condition['on']);
		$join = $this->_condition['join'];
		$joinStr .= $table[0];
		for ($i = 0; $i < (count($table) - 1); $i++) {
			$joinStr .= ' ' . ($join[$i] ? $join[$i] : 'LEFT JOIN') . ' ' . $table[$i + 1] . ' ON ' . ($on[$i] ? $on[$i] : '');
		}
		return $joinStr;
	}
	
	protected function _dealField() {
		if (!isset($this->_condition['field'])) return '*';
		if (is_string($this->_condition['field']) && strpos($this->_condition['field'], ',')) {
			$this->_condition['field'] = explode(',', $this->_condition['field']);
		}
		if (is_array($this->_condition['field'])) {
			$array = array();
			foreach ($this->_condition['field'] as $key => $field) {
				if (!is_numeric($key)) {
					$array[] = $this->_dealKey($key) . ' AS ' . $this->_dealKey($field);
				} else {
					$array[] = $this->_dealKey($field);
				}
			}
			$fieldStr = implode(',', $array);
		} elseif (is_string($this->_condition['field']) && !empty($this->_condition['field'])) {
			$fieldStr = $this->_dealKey($this->_condition['field']);
		} else {
			$fieldStr = '*';
		}
		return $fieldStr;
	}
	
	protected function _dealUnion() {
		return '';
	}
	
	protected function _dealLock() {
		if (!isset($this->_condition['lock'])) return '';
		if (!$this->_condition['lock']) return '';
		return ' FOR UPDATE ';
	}
	
	protected function _dealTable() {
		if (isset($this->_condition['on'])) return '';
		$tables = $this->_condition['table'];
		if (is_array($tables)) {
			$array = array();
			foreach ($tables as $table => $alias) {
				if (!is_numeric($table)) {
					$array[] = $this->_dealKey($table) . ' ' . $this->_dealKey($alias);
				} else {
					$array[] = $this->_dealKey($table);
				}
			}
			$tables = $array;
		} elseif (is_string($tables)) {
			$tables = explode(',', $tables);
			array_walk($tables, array(&$this, '_dealKey'));
		}
		return implode(',', $tables);
	}
	
	private function _dealKey(&$key) {
        if (trim($key) == '*') return $key;
//        if (strpos($key, 'COUNT') || strpos($key, 'count')) return $key;
//        if (strpos($key, 'SUM') || strpos($key, 'sum')) return $key;
//        if (strpos($key, 'MIN') || strpos($key, 'min')) return $key;
//        if (strpos($key, 'MAX') || strpos($key, 'max')) return $key;
//        if (strpos($key, 'AVG') || strpos($key, 'avg')) return $key;
        $filter = '/(count|sum|avg|min|max|distinct)/i';
        if (preg_match($filter, $key)) return $key;

        if (strstr($key, '`')) {
            return $key;
        }

        if (!strpos($key, '.')) {
            return "`" . trim($key) . "`";
        }
        return $key;
	}

	private function parseSpecialWhere($key, $val) {
        $where_str = '';
        switch ($key) {
            case '_string':
                //字符串查询模式
                $where_str = $val;
                break;
            case '_complex':
                //复合条件查询
                $where_str = substr($this->_parseWhere($val), 6);
                break;
            case '_query':
                parse_str($val, $where);
                if (isset($where['_logic'])) {
                    $op = ' ' . strtoupper($where['_logic']) . ' ';
                    unset($where['_logic']);
                } else {
                    $op = ' AND ';
                }
                $array = array();
                foreach ($where as $field => $data) {
                    $array[] = $this->_dealKey($field) . ' = ' . $this->_dealValue($data);
                }
                $where_str = implode($op, $array);
                break;
        }
        return '( ' . $where_str . ' )';
    }

    private function _parseWhere($where) {
	if (empty($where)) return '';
        $whereStr = '';
        if (is_string($where)) {
            $whereStr = $where;
        } else {
            $operate = isset($where['_logic']) ? strtoupper($where['_logic']) : '';
            if (in_array($operate, array('AND', 'OR', 'XOR'))) {
                $operate = ' ' . $operate . ' ';
                unset($where['_logic']);
            } else {
                $operate = ' AND ';
            }
            foreach ($where as $key => $val) {
                if (is_numeric($key)) {
                    $key = '_complex';
                }
                if (0 === strpos($key, '_')) {
                    //解析特殊条件表达式
                    $whereStr .= $this->parseSpecialWhere($key, $val);
                } else {
                    //查询字段的安全过滤
                    if (!preg_match('/^[A-Z_\|\&\-.a-z0-9]+$/', trim($key))) {
                        //throw new \Exception('存在不合法的操作');
                        die('EXPRESS_ERROR' . $key);
                    }
                    $multi = is_array($val) && isset($val['_multi']);
                    $key = trim($key);
                    if (strpos($key, '|')) {
                        $array = explode('|', $key);
                        $str = array();
                        foreach ($array as $m => $k) {
                            $v = $multi ? $val[$m] : $val;
                            $str[] = $this->_dealWhereItem($this->_dealKey($k), $v);
                        }
                        $whereStr .= '( ' . implode(' OR ', $str) . ' )';
                    } elseif (strpos($key, '&')) {
                        $array = explode('&', $key);
                        $str = array();
                        foreach ($array as $m => $k) {
                            $v = $multi ? $val[$m] : $val;
                            $str[] = '(' . $this->_dealWhereItem($this->_dealKey($k), $v) . ')';
                        }
                        $whereStr .= '( ' . implode(' AND ', $str) . ' )';
                    } else {
                        $whereStr .= $this->_dealWhereItem($this->_dealKey($key), $val);
                    }
                }
                $whereStr .= $operate;
            }
            $whereStr = substr($whereStr, 0, -strlen($operate));
        }
        return empty($whereStr) ? '' : ' WHERE ' . $whereStr;
    }

	private function _dealWhere() {
		if (!isset($this->_condition['where'])) return '';
		$whereStr = $this->_parseWhere($this->_condition['where']);
//		if (is_string($this->_condition['where'])) {
//			$whereStr = $this->_condition['where'];
//		} elseif (is_array($this->_condition['where'])) {
//			if (isset($this->_condition['_op'])) {
//				//操作符号
//				$operate = ' ' . strtoupper($this->_condition['_op']) . ' ';
//			} else {
//				$operate = ' AND ';
//			}
//			foreach ($this->_condition['where'] as $key => $value) {
//				if (is_numeric($key)) {
//                    $key = '_complex';
//                }
//                $whereStrTmp = '';
//				if (0 === strpos($key, '_')) {
//					$whereStr .= $this->parseSpecialWhere($key, $value);
//				} else {
//					//查询字段的安全过滤
//					if (!preg_match('/^[A-Z_\|\&\-.a-z0-9]+$/', trim($key))) {
//						throw new Exception('存在不合法的操作');
//					}
//					//多条件支持
//					$multi = is_array($value) && isset($value['_multi']);
//					$key = trim($key);
//					if (strpos($key, '|')) { //支持name|title|nickname方式定义查询字段
//						$array = explode('|', $key);
//						$str = array();
//						foreach ($array as $m => $k) {
//							$v = $multi ? $value[$m] : $value;
//							$str[] = '(' . $this->_dealWhereItem($k, $v) . ')';
//						}
//						$whereStrTmp .= implode(' OR ', $str);
//					} elseif (strpos($key, '&')) {
//						$array = explode('&', $key);
//						$str = array();
//						foreach ($array as $m => $k) {
//							$v = $multi ? $value[$m] : $value;
//							$str[] = '(' . $this->_dealWhereItem($k, $v) . ')';
//						}
//						$whereStrTmp .= implode(' AND ', $str);
//					} else {
//						$whereStrTmp .= $this->_dealWhereItem($key, $value);
//					}
//				}
//				if (!empty($whereStrTmp)) {
//					$whereStr .= '( ' . $whereStrTmp . ' )' . $operate;
//				}
//			}
//			$whereStr = substr($whereStr, 0, -strlen($operate));
//		}
		return $whereStr;
	}
	
	private function _dealWhereItem($key, $val) {
		$whereStr = '';
        if(is_array($val)) {
            if(is_string($val[0])) {
                if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i',$val[0])) { // 比较运算
                    $whereStr .= $this->_dealKey($key) .' '.$this->comparison[strtolower($val[0])].' '.$this->_dealValue($val[1]);
                }elseif('exp'==strtolower($val[0])){ // 使用表达式
//                    $whereStr .= ' ('.$this->_dealKey($key).' '.$val[1].') ';
                    $whereStr .= $val[1];
                }elseif(preg_match('/IN/i',$val[0])){ // IN 运算
                    if(isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $this->_dealKey($key) .' '.strtoupper($val[0]).' '.$val[1];
                    }else{
                    	if (empty($val[1])){
                    		$whereStr .= $this->_dealKey($key) . ' '.strtoupper($val[0]).'(\'\')';
                    	}elseif(is_string($val[1]) || is_numeric($val[1])) {
                             $val[1] =  explode(',',$val[1]);
                             $zone   =   implode(',',$this->_dealValue($val[1]));
                             $whereStr .= $this->_dealKey($key) . ' '.strtoupper($val[0]).' ('.$zone.')';
                        }elseif(is_array($val[1])){
 							$zone   =   implode(',',$this->_dealValue($val[1]));
                            $whereStr .= $this->_dealKey($key) . ' ' . strtoupper($val[0]).' ('.$zone.')';
                        }
                    }
                }elseif(preg_match('/BETWEEN/i',$val[0])){
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    if($data[0] && $data[1]) {
                        $whereStr .=  ' ('.$this->_dealKey($key).' '.strtoupper($val[0]).' '.$this->_dealValue($data[0]).' AND '.$this->_dealValue($data[1]).' )';
                    } elseif ($data[0]) {
                        $whereStr .= $this->_dealKey($key) . ' '.$this->comparison['gt'].' '.$this->_dealValue($data[0]);
                    } elseif ($data[1]) {
                        $whereStr .= $this->_dealKey($key) . ' '.$this->comparison['lt'].' '.$this->_dealValue($data[1]);
                    }
                }elseif(preg_match('/TIME/i',$val[0])){
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    if($data[0] && $data[1]) {
                        $whereStr .=  ' ('.$this->_dealKey($key).' BETWEEN '.$this->_dealValue($data[0]).' AND '.$this->_dealValue($data[1] + 86400 -1).' )';
                    } elseif ($data[0]) {
                        $whereStr .= $this->_dealKey($key).' '.$this->comparison['gt'].' '.$this->_dealValue($data[0]);
                    } elseif ($data[1]) {
                        $whereStr .= $this->_dealKey($key) . ' '.$this->comparison['lt'].' '.$this->_dealValue($data[1] + 86400);
                    }
                }else{
                    $error = 'Model Error: args '.$val[0].' is error!';
                    throw new Exception($error);
                }
            }else {
                $count = count($val);
                if(is_string($val[$count-1]) && in_array(strtoupper(trim($val[$count-1])),array('AND','OR','XOR'))) {
                    $rule = strtoupper(trim($val[$count-1]));
                    $count--;
                }else{
                    $rule = 'AND';
                }
                for($i=0;$i<$count;$i++) {
                	if (is_array($val[$i])) {
                		if (is_array($val[$i][1])) {
                			$data = implode(',',$val[$i][1]);
                		} else {
                			$data = $val[$i][1];
                		}
                	} else {
                		$data = $val[$i];
                	}
                    if('exp'==strtolower($val[$i][0])) {
                        $whereStr .= '('.$this->_dealKey($key).' '.$data.') '.$rule.' ';
                    } else 	{
                        $op = is_array($val[$i])?$this->comparison[strtolower($val[$i][0])]:'=';
						if(preg_match('/IN/i',$op)) {
							$whereStr .= '('.$this->_dealKey($key).' '.$op.' ('.$this->_dealValue($data).')) '.$rule.' ';
						}else{
							$whereStr .= '('.$this->_dealKey($key).' '.$op.' '.$this->_dealValue($data).') '.$rule.' ';
						}
                    }
                }
                $whereStr = substr($whereStr, 0, -4);
            }
        }else {
        	$whereStr .=  $this->_dealKey($key) .' = '.$this->_dealValue($val);
        }
        return $whereStr;
	}
	
	protected function _dealValue($value) {
        if(is_string($value) || is_numeric($value)) {
            $value = '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value   =  $value[1];
        }elseif(is_array($value)) {
            $value   =  array_map(array($this, '_dealValue'),$value);
        }elseif(is_null($value)){
            $value   =  'NULL';
        }
        return $value;
	}
	
	protected function _dealDistinct() {
		if (!isset($this->_condition['distinct'])) return '';
		return !empty($this->_condition['distinct']) ? ' DISTINCT ' . $this->_condition['distinct'] . ',' : '';
	}
	
	protected function _dealHaving() {
		if (!isset($this->_condition['having'])) {
			return '';
		}
		if (!$this->_condition['having']) return '';
		return ' HAVING ' . $this->_condition['having'];
	}
	
	protected function _dealSet($data) {
		$set = array();
		foreach($data as $key => $val) {
            $value = $this->_dealValue($val);
			if (is_scalar($value)) {
				$set[] = $this->_dealKey($key) . '=' . $value;
			} elseif (is_array($value)) {
                $k = reset($value);
                if ($k == 'exp') {
                    $set[] = $key . '=' . $value[1];
                } else {
                    $set[] = $this->_dealKey($key) . '=' . $value[0];
                }
            }
		}
		return ' SET ' . implode(',', $set);
	}
	
	public function escapeString($str) {
        $str = addslashes(stripslashes($str));//重新加斜线，防止从数据库直接读取出错
        return $str;
	}
}
