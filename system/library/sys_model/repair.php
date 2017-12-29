<?php
namespace Sys_Model;

class Repair {
	public function __construct($registry) {
		$this->db = $registry->get('db');
	}

    // -------------------------------------------- 写 --------------------------------------------
    /**
     * 添加信息
     * @param $data
     * @return mixed
     */
	public function addRepair($data) {
        return $this->db->table('repair')->insert($data);
    }

    /**
     * 更新信息
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateRepair($where, $data) {
        return $this->db->table('repair')->where($where)->update($data);
    }

    // -------------------------------------------- 读 --------------------------------------------

    /**
     * 列表
     * @param array|string $where
     * @param string $fields
     * @param string $order
     * @param string $limit
     * @param string $field
     * @param array $join
     * @param string $joinTypeSelect
     * @param string $group
     * @return mixed
     * @internal param string $fields
     */
    public function getRepairList($where = array(), $order = '', $limit = '', $field = 'repairTable.*', $join = array() , $joinTypeSelect = ',left', $group = '') {
        $table = 'repair as repairTable';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= $joinTypeSelect;
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }

        return $this->db->table($table)->field($field)->where($where)->group($group)->order($order)->limit($limit)->select();
    }

    public function getTotalRepairList($where, $join = array() , $joinTypeSelect = ',left') {
        $table = 'repair as repairTable';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as `%s`', $v, $v);
                    $joinType .= $joinTypeSelect;
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }

        return $this->db->table($table)->where($where)->limit(1)->count(1);
    }

    /**
     * 读取信息
     * @param $where
     * @param string $field
     * @return mixed
     */
    public function getRepairInfo($where, $field = '*', $group = '') {
        if (!isset($where['repair_id'])) {
            $this->db->limit(1);
        }
        return $this->db->table('repair')->field($field)->where($where)->group($group)->find();
    }

    /**
     * 统计信息
     * @param $where
     * @return mixed
     */
    public function getTotalRepairs($where) {
        $result = $this->db->table('repair')->where($where)->limit(1)->count(1);
        #echo $this->db->getLastSql();
        return $result;
    }

    /*
     * 统计运维人员维修单车数量；
     *
     * */

    public function getSumRepairsByAdminId($where,$limit){
        $sql = "select count(r.repair_id) as total,a.nickname,a.admin_name,a.mobile,r.admin_id from `rich_repair` as r  LEFT JOIN rich_admin as a ON r.admin_id = a.admin_id where ".$where." AND r.admin_id !='' AND r.admin_id !='null'  group by r.admin_id limit ".$limit;
        $result = $this->db->getRows($sql);
        return $result;
    }

    public function getSumRepairsByAdminIdTotal($where){
        $sql = "select count(DISTINCT `admin_id`) as total  from `rich_repair` where $where limit 1";
        $result = $this->db->getRow($sql);
        return $result;
    }

}