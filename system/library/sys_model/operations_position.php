<?php

namespace Sys_Model;

class Operations_Position
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * 添加运维人员定位
     * @param $data
     * @return mixed
     */
    public function addOperationsPosition($data)
    {
        return $this->db->table('operations_location_record')->insert($data);
    }

    /**
     * 更新运维人员定位
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateOperationsPosition($where, $data)
    {
        return $this->db->table('operations_location_record')->where($where)->update($data);
    }

    /**
     * 获取定位
     * @param array $where
     * @param string $field
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getOperationsPositionList($where = array(), $field = '*', $order = '', $limit = '')
    {
        return $this->db->table('operations_location_record')->where($where)->field($field)->order($order)->limit($limit)->select();
    }

    /**
     * 查找定位
     * @param $where
     * @return mixed
     */
    public function getOperationsPosition($where, $field = '', $order = 'record_id DESC')
    {
        return $this->db->table('operations_location_record')->where($where)->field($field)->order($order)->find();
    }

    /**
     * 已经废弃
     * @param $min_lat
     * @param $min_lng
     * @param $max_lat
     * @param $max_lng
     * @param int $cooperator_id
     * @return mixed
     */
    public function getOperatorByBounds($min_lat, $min_lng, $max_lat, $max_lng, $cooperator_id = 0)
    {
        $min_lat += 0;
        $min_lng += 0;
        $max_lat += 0;
        $max_lng += 0;

        $lng_bound = ($min_lng <= $max_lng) ?
            "AND record.lng>=$min_lng AND record.lng<=$max_lng "
            :  // 注意地图在经度方向是可以拼接的（-180°跟+180°拼接在一起），所以出现左边的经度大于右边的经度是很正常的
            "AND ((record.lng>=$min_lng AND record.lng<=180) OR (record.lng>=-180 AND record.lng<=$max_lng))";
        $sql = 'select admin.admin_id,admin.admin_name,admin.nickname,admin.mobile,record.lng,record.lat,record.add_time,region.region_name from rich_admin as admin '
            . ' LEFT join rich_operations_location_record as record on admin.admin_id = record.operator_id'
            . ' LEFT join rich_operations_to_region as region on admin.admin_id = region.admin_id'
            . " WHERE record.lat<>'' AND record.lng<> '' "
            . (empty($cooperator_id) ? '' : ' AND b.cooperator_id=' . $cooperator_id)
            . " AND admin.type = 3 AND admin.state = 1 AND record.lat>=$min_lat AND record.lat<=$max_lat " . $lng_bound;
        return $this->db->getRows($sql);
    }

}
