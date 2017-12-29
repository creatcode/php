<?php

namespace Sys_Model;

class Fault
{
    private $memcache;

    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ****************************************** 设备故障表 ******************************************
    // ------------------------------------------ 写 ------------------------------------------
    /**
     * 添加故障记录
     * @param $data
     * @return mixed
     */
    public function addFault($data)
    {
        return $this->db->table('fault')->insert($data);
    }

    /**
     * 更新故障记录
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateFault($where, $data)
    {
        return $this->db->table('fault')->where($where)->update($data);
    }

    /**
     * 删除故障记录
     * @param $where
     * @return mixed
     */
    public function deleteFault($where)
    {
        return $this->db->table('fault')->where($where)->delete();
    }

    // ------------------------------------------ 读 ------------------------------------------
    // 故障表
    /**
     * 获取故障记录列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @param string $field
     * @param array $join
     * @param string $group
     * @return mixed
     * @internal param string $field
     */
    public function getFaultList($where = array(), $order = '', $limit = '', $field = '*', $join = array(), $group = '')
    {
        $table = 'fault as fault';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $filter = '/(lock)/i';
                    preg_match($filter, $v) ? $table .= sprintf(',%s as %s', $v, '`' . $v . '`') :
                        $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
        return $this->db->table('fault')->field($field)->where($where)->order($order)->limit($limit)->group($group)->select();
    }

    public function getFaultWithRepairList($where = array(), $order = '', $limit = '', $field = 'fault.*, repair.repair_id, admin.admin_id')
    {
        $this->db->join('left,left,left')->on('repair.fault_id = fault.fault_id,repair.admin_id = admin.admin_id,cooperator.cooperator_id = fault.cooperator_id');
        return $this->db->field($field)->table('fault AS `fault`,repair AS `repair`,admin AS `admin`,cooperator AS `cooperator`')->where($where)->order($order)->limit($limit)->select();
    }

    public function getFaultHistoryList($where = array(), $order = '', $limit = '', $field = 'fault.*, repair.repair_id')
    {
        $this->db->join('left')->on('repair.fault_id = fault.fault_id');
        return $this->db->field($field)->table('fault AS `fault`,repair AS `repair`')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取故障记录信息
     * @param $where
     * @return mixed
     */
    public function getFaultInfo($where)
    {
        return $this->db->table('fault')->where($where)->limit(1)->find();
    }

    /**
     * 统计故障记录信息
     * @param $where
     * @return mixed
     */
    public function getTotalFaults($where)
    {
        return $this->db->table('fault')->where($where)->limit(1)->count(1);
    }

    public function getTotalFaultsWithRepair($where)
    {
        $this->db->join('left,left,left')->on('repair.fault_id = fault.fault_id,repair.admin_id = admin.admin_id,cooperator.cooperator_id = fault.cooperator_id');
        return $this->db->table('fault AS `fault`,repair AS `repair`,admin AS `admin`,cooperator AS `cooperator`')->where($where)->limit(1)->count(1);
    }

    // 故障类型表

    /**
     * 获取故障类型列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @param string $field
     * @return mixed
     */
    public function getFaultTypeList($where = array(), $order = '', $limit = '', $field = '*')
    {
        $rec = $this->db->table('fault_type')->field($field)->where($where)->order($order)->limit($limit)->select();
        return $rec;
    }

    /**
     * 获取故障类型信息
     * @param $where
     * @return mixed
     */
    public function getFaultTypeInfo($where)
    {
        return $this->db->table('fault_type')->where($where)->limit(1)->find();
    }

    /**
     * 统计故障类型信息
     * @param $where
     * @return mixed
     */
    public function getTotalFaultTypes($where)
    {
        return $this->db->table('fault_type')->where($where)->limit(1)->count();
    }

    /**
     * 获取故障类型列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return array|string
     */
    public function getAllFaultType($where = array(), $order = 'display_order desc, fault_type_id asc', $limit = '')
    {
        $result = $this->memcache->get('ebike_fault_type');
        if (!$result) {
            $result = $this->db->table('fault_type')->where($where)->order($order)->limit($limit)->select();
            $this->memcache->set('ebike_fault_type', $result);
        }
        return $result;
    }

    // ****************************************** 违规停放表 ******************************************
    // ------------------------------------------ 写 ------------------------------------------
    /**
     * 添加故障记录
     * @param $data
     * @return mixed
     */
    public function addIllegalParking($data)
    {
        return $this->db->table('illegal_parking')->insert($data);
    }

    /**
     * 更新故障记录
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateIllegalParking($where, $data)
    {
        return $this->db->table('illegal_parking')->where($where)->update($data);
    }

    /**
     * 删除故障记录
     * @param $where
     * @return mixed
     */
    public function deleteIllegalParking($where)
    {
        return $this->db->table('illegal_parking')->where($where)->delete();
    }

    // ------------------------------------------ 读 ------------------------------------------
    // 故障表
    /**
     * 获取故障记录列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getIllegalParkingList($where = array(), $order = '', $limit = '', $field = 'illegal_parking.*', $join = array(), $group = '')
    {
        $table = 'illegal_parking as illegal_parking';
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

        return $this->db->table($table)->field($field)->where($where)->group($group)->order($order)->limit($limit)->select();
    }

    /**
     * 获取故障记录信息
     * @param $where
     * @return mixed
     */
    public function getIllegalParkingInfo($where)
    {
        return $this->db->table('illegal_parking')->where($where)->limit(1)->find();
    }

    /**
     * 统计故障记录信息
     * @param $where
     * @return mixed
     */
    public function getTotalIllegalParking($where, $join = array())
    {
        $table = 'illegal_parking as illegal_parking';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as `%s`', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }

        return $this->db->table($table)->where($where)->limit(1)->count(1);
    }

    // ****************************************** 正常停放表 ******************************************

    /**
     * 获取正常停放列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getNormalParkingList($where = array(), $order = '', $limit = '')
    {
        return $this->db->table('normal_parking')->where($where)->order($order)->limit($limit)->select();
    }


    /*
     *
     *未处理的违停举报详情；
     *
     */
    public function getParkingInfo($w){
        $sql = "SELECT illegal_parking.*, l.lock_sn, l.lat, l.lng FROM rich_illegal_parking AS illegal_parking LEFT JOIN rich_bicycle AS bicycle ON bicycle.bicycle_id = illegal_parking.bicycle_id LEFT JOIN rich_lock AS l ON bicycle.lock_sn = l.lock_sn WHERE ".$w." ORDER BY illegal_parking.parking_id DESC";
        return $this->db->getRows($sql);
    }

    /*
     *已经处理的违停举报详情；
     *
     */
    public function getParkingInfoed($w,$bicycle_id){
        $sql = "SELECT illegal_parking.*, l.lock_sn, l.lat, l.lng, r.*, r.add_time as repair_time FROM rich_illegal_parking AS illegal_parking LEFT JOIN rich_bicycle AS bicycle ON bicycle.bicycle_id = illegal_parking.bicycle_id LEFT JOIN rich_lock AS l ON bicycle.lock_sn = l.lock_sn LEFT JOIN rich_repair AS r ON r.bicycle_id = ".$bicycle_id." WHERE ".$w." ORDER BY illegal_parking.parking_id DESC";
        return $this->db->getRows($sql);
    }

}