<?php
namespace Sys_Model;

class Cooperator {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ******************************************** 合伙人表 ********************************************
    // -------------------------------------------- 写 --------------------------------------------
    /**
     * 添加单车
     * @param $data
     * @return mixed
     */
    public function addCooperator($data) {
        if (isset($data['password'])) {
            $data['salt'] = token(10);
            $data['password'] = sha1($data['salt'] . sha1($data['salt'] . sha1($data['password'])));
        }
        return $this->db->table('cooperator')->insert($data);
    }

    /**
     * 更新单车
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateCooperator($where, $data) {
        if (isset($data['password'])) {
            $data['salt'] = token(10);
            $data['password'] = sha1($data['salt'] . sha1($data['salt'] . sha1($data['password'])));
        }
        return $this->db->table('cooperator')->where($where)->update($data);
    }

    /**
     * 删除单车
     * @param $where
     * @return mixed
     */
    public function deleteCooperator($where) {
        return $this->db->table('cooperator')->where($where)->delete();
    }

    // -------------------------------------------- 读 --------------------------------------------
    /**
     * 获取单车列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @param string $field
     * @param array $join
     * @return mixed
     */
    public function getCooperatorList($where = array(), $order = '', $limit = '', $field = 'cooperator.*', $join = array()) {
        $table = 'cooperator as cooperator';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }

        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取单车信息
     * @param $where
     * @return mixed
     */
    public function getCooperatorInfo($where) {
        return $this->db->table('cooperator')->where($where)->limit(1)->find();
    }

    /**
     * 统计单车信息
     * @param $where
     * @param array $join
     * @return mixed
     */
    public function getTotalCooperators($where, $join = array()) {
        $table = 'cooperator as cooperator';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->where($where)->limit(1)->count(1);
    }

    // --------------------------------------------------- 其他 ---------------------------------------------------
    /**
     * 检验 用户名称格式
     * @param $username
     * @return bool
     */
    public function checkAdminNameFormat($username) {
        return preg_match("/^[0-9a-zA-Z]{6,14}$/", $username) ? true : false;
    }

    /**
     * 检验 会员密码长度
     * @param $password
     * @return bool
     */
    public function checkPasswordFormat($password) {
        return preg_match("/^[0-9a-zA-Z]{6,20}$/", $password) ? true : false;
    }

    /**
     * 检验 会员密码
     * @param $password
     * @param $data
     * @return bool
     */
    public function checkPassword($password, $data) {
        return sha1($data['salt'] . sha1($data['salt'] . sha1($password))) == $data['password'] ? true : false;
    }
}
