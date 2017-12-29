<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/8
 * Time: 15:55
 */
class Validate {
    private $_validator = array(
        'email' => '/^([.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\\.[a-zA-Z0-9_-])+/',
        'mobile' => '/^1[0-9]{10}$/',
        'currency' => '/^[0-9]+(\\.[0-9]+)?$/'
    );

    private $_param = array();
    private $_rule = array();
    private $_errorMsg = array();

    public function setConfig($param) {
        if (isset($param['param'])) {
            $this->setParam($param['param']);
        }
        if (isset($param['rule'])) {
            $this->setRule($param['rule']);
        }
        if (isset($param['msg'])) {
            $this->setErrorMsg($param['msg']);
        }
    }

    public function setRule($rule) {
        $this->_rule = $rule;
    }

    public function setParam($param) {
        $this->_param = $param;
    }

    public function setErrorMsg($error) {
        $this->_errorMsg = $error;
    }

    public function getParam() {
        return $this->_param;
    }

    private function requireOp($val) {
        if (empty($param)) {
            return callback(false, '此字段必填');
        } else {
            return callback(true);
        }
    }

    public function doubleOp($param) {
        if (!is_double($param)) {
            return callback(false, '不是double型');
        } else {
            return callback(true);
        }
    }

    public function currencyOp($param) {
        if (!preg_match($this->_validator['currency'], $param)) {
            return callback(false, '金额格式有误');
        } else {
            return callback(true);
        }
    }

    public function __call($method, $args)
    {
        if (in_array($method, array('matchOp'))) {
//            if (!preg_match($args[]))
        }
    }

    public function intOp($param) {
        if (!is_int($param)) {
            return callback(false, '不是整数');
        } else {
            return callback(true);
        }
    }

    public function floatOp($param) {
        if (!is_float($param)) {
            return callback(false, '不是浮点数');
        } else {
            return callback(true);
        }
    }

    public function mobileOp($param) {
        if (!preg_match($this->_validator['mobile'], $param)) {
            return callback(false, '手机号码格式有误');
        } else {
            return callback(true);
        }
    }

    public function emailOp($param) {
        if (!preg_match($this->_validator['email'], $param)) {
            return callback(false, '邮件格式错误');
        } else {
            return callback(true);
        }
    }

    public function lengthOp($param, $length) {
        if (utf8_strlen($param) != $length) {
            return callback(false, '长度不等于' . $length);
        } else {
            return callback(true);
        }
    }

    public function minLengthOp($param, $length) {
        if (utf8_strlen($param) < $length) {
            return callback(false, '长度不能小于' . $length);
        } else {
            return callback(true);
        }
    }

    public function maxLengthOp($param, $length) {
        if (utf8_strlen($param) > $length) {
            return callback(false, '长度不能大于' . $length);
        } else {
            return callback(true);
        }
    }

    public function run() {
        if (!$this->_param) {
            return callback(false, '参数不能为空');
        }
        if (!is_array($this->_param)) {
            return callback(false, '参数必须为数组');
        }
        if (!is_array($this->_rule) || empty($this->_rule)) {
            return callback(false, '验证规格没有设置');
        }

        foreach ($this->_rule as $field => $preg) {
            if (isset($this->_param[$field])) {
                $filters = explode('|', $preg);
                foreach ($filters as $call_function) {
                    if (strpos($call_function, ':')) {
                        $fun_arr = explode(':', $call_function);
                        $fun_name = $fun_arr[0] . 'Op';
                        $result = $this->$fun_name($this->_param[$field]);
                        if (!$result['state']) {
                            $msg = (isset($this->_errorMsg[$field]) ? $this->_errorMsg[$field] : $field) . $result['msg'];
                            $result['msg'] = $msg;
                            return $result;
                        }
                    } else {
                        $fun_name = $call_function . 'Op';
                        $result = $this->$fun_name($this->_param[$field]);
                        if (!$result['state']) {
                            $msg = (isset($this->_errorMsg[$field]) ? $this->_errorMsg[$field] : $field) . $result['msg'];
                            $result['msg'] = $msg;
                            return $result;
                        }
                    }
                }
            }
        }
        return callback(true, '验证成功');
    }
}