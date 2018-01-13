<?php
namespace Sys_Model;

class Bicycle {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加单车
     * @param $data
     * @return mixed
     */
    public function addBicycle($data) {
        return $this->db->table('bicycle')->insert($data);
    }

    /**
     * 更新单车
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateBicycle($where, $data) {
        return $this->db->table('bicycle')->where($where)->update($data);
    }

    /**
     * 删除单车
     * @param $where
     * @return mixed
     */
    public function deleteBicycle($where) {
        return $this->db->table('bicycle')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取单车列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getBicycleList($where = array(), $order = '', $limit = '', $field = 'bicycle.*', $join = array()) {
        $table = 'bicycle as bicycle';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $filter = '/(lock)/i';
                    preg_match($filter, $v) ? $table .= sprintf(',%s as %s', $v, '`' .$v . '`') :
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
     * @param string $field
     * @return mixed
     */
    public function getBicycleInfo($where, $field = '*', $order = '') {
        return $this->db->table('bicycle')->where($where)->field($field)->order($order)->limit(1)->find();
    }

    /**
     * 统计单车信息
     * @param $where
     * @return mixed
     */
    public function getTotalBicycles($where, $join = array()) {
        $table = 'bicycle as bicycle';
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

    /**
     * 获取单车位置信息
     */
    public function getBicycleLockMarker($where = array(), $field = '', $limit = '') {
        $field .= 'b.bicycle_id,b.bicycle_sn,b.type,b.fee,b.last_used_time,r.region_id,r.region_name,r.region_city_code,r.region_city_ranking,';
        $field .= 'l.lock_sn,l.lat,l.lng,l.battery';
        $on = 'b.lock_sn=l.lock_sn,b.region_id=r.region_id';
        $result = $this->db->table('bicycle as b,lock as l,region as r')->where($where)->field($field)->join('left,left')->on($on)->limit($limit)->select();
        //var_dump( $this->db->getLastSql());
        return $result;
    }

    public function getBicycleAndLockInfo($where = array(), $field = '', $limit = '') {
        $field .= 'b.bicycle_id,b.bicycle_sn,b.type,b.fee,b.region_id,b.region_name,b.cooperator_id,b.is_using,b.add_time,b.keep_time,b.fault,b.illegal_parking,b.low_battery,b.full_bicycle_sn,b.last_used_time,';
        $field .= 'l.lock_status,l.lock_type,l.system_time,l.device_time,l.open_nums,l.battery,l.use_status,l.serialnum,l.transfer_url,l.last_close_time,l.lock_sn,l.lock_id,l.lat,l.lng,l.gx';
        $field .= ',l.lock_type,l.encrypt_key,l.password,l.mac_address';
        $on = 'b.lock_sn=l.lock_sn';
        $result = $this->db->table('bicycle as b,lock as l')->where($where)->field($field)->join('left')->on($on)->limit($limit)->select();
        #echo $this->db->getLastSql();
        #file_put_contents('/dev/shm/logs/lock.log', date('Y-m-d H:i:s ') . print_r($result,true)."\n", FILE_APPEND);


        return $result;
    }

    public function getBlueBikeInfo($where = array(), $field = '', $limit = '') {
        $field .= 'b.bicycle_id,b.bicycle_sn,b.type,b.fee,b.region_id,b.region_name,b.cooperator_id,b.is_using,b.add_time,b.keep_time,b.fault,b.illegal_parking,b.low_battery,b.full_bicycle_sn,';
        $field .= 'l.lock_status,l.lock_type,l.system_time,l.device_time,l.open_nums,l.battery,l.use_status,l.serialnum,l.transfer_url,l.last_close_time,l.lock_sn,l.lock_id,l.lat,l.lng,l.gx,l.lock_factory,l.lock_name';
        $on = 'b.lock_sn=l.lock_sn';
        $result = $this->db->table('bicycle as b,lock as l')->where($where)->field($field)->join('left')->on($on)->limit($limit)->select();
        #echo $this->db->getLastSql();
        return $result;
    }

    public function getBlueBikeInfoNew($where = array(), $field = '', $limit = '') {
        $field .= 'b.bicycle_id,b.bicycle_sn,b.type,b.fee,b.region_id,b.region_name,b.cooperator_id,b.is_using,b.add_time,b.keep_time,b.fault,b.illegal_parking,b.low_battery,b.full_bicycle_sn,';
        $field .= 'l.lock_status,l.lock_type,l.system_time,l.device_time,l.open_nums,l.battery,l.use_status,l.serialnum,l.transfer_url,l.last_close_time,l.lock_sn,l.lock_id,l.lat,l.lng,l.gx,l.lock_factory,l.lock_name,(select count(fault_id) from rich_fault as r where r.bicycle_id = b.bicycle_id and r.processed = 0 and r.fault_type = 12) as fault_num,(select count(fault_id) from rich_fault as r where r.bicycle_id = b.bicycle_id and r.fault_type = 12) as faultd_num';
        $on = 'b.lock_sn=l.lock_sn';
        $result = $this->db->table('bicycle as b,lock as l')->where($where)->field($field)->join('left')->on($on)->limit($limit)->select();
        #echo $this->db->getLastSql();
        return $result;
    }


    public function getBlueBikeInfototal($where = array(), $field = '') {
        $field .= 'b.bicycle_id,b.bicycle_sn,b.type,b.fee,b.region_id,b.region_name,b.cooperator_id,b.is_using,b.add_time,b.keep_time,b.fault,b.illegal_parking,b.low_battery,b.full_bicycle_sn,';
        $field .= 'l.lock_status,l.lock_type,l.system_time,l.device_time,l.open_nums,l.battery,l.use_status,l.serialnum,l.transfer_url,l.last_close_time,l.lock_sn,l.lock_id,l.lat,l.lng,l.gx';
        $on = 'b.lock_sn=l.lock_sn';
        $result = $this->db->table('bicycle as b,lock as l')->where($where)->field($field)->join('left')->on($on)->limit(1)->count(1);
        #echo $this->db->getLastSql();
        return $result;
    }

    /**
     * 获取指定边界内的单车列表
     * @param $min_lat
     * @param $min_lng
     * @param $max_lat
     * @param $max_lng
     * @param $status 状态选择，可以是low_battery,illegal_parking,fault,offline等字符串中的一个或者多个所组成的数组
     * @param int $cooperator_id
     * @return mixed
     */
    public function getBicyclesByBounds($min_lat, $min_lng, $max_lat, $max_lng, $status, $cooperator_id = 0) {
        $min_lat += 0;
        $min_lng += 0;
        $max_lat += 0;
        $max_lng += 0;

        $lng_bound = ($min_lng <= $max_lng) ?
            "AND l.lng>=$min_lng AND l.lng<=$max_lng "
            :  // 注意地图在经度方向是可以拼接的（-180°跟+180°拼接在一起），所以出现左边的经度大于右边的经度是很正常的
            "AND ((l.lng>=$min_lng AND l.lng<=180) OR (l.lng>=-180 AND l.lng<=$max_lng))";
        $sql = "SELECT distinct(b.bicycle_id), b.bicycle_sn, b.type, b.fee, b.cooperator_id, b.is_hide, r.region_id, r.region_name,co.cooperator_name,"
            ." r.region_city_code, r.region_city_ranking, l.open_nums, l.lock_sn, l.lock_type, l.lat, l.lng, l.lock_status, l.battery,l.system_time,"
            ." from_unixtime(l.system_time,'%Y-%m-%d %H:%i:%s') as last_update, b.fault, b.illegal_parking, b.low_battery, b.is_activated, "
            ."(l.lock_type=2 OR l.lock_type=5 OR l.system_time>=unix_timestamp()-" . OFFLINE_THRESHOLD . ") as online, "
            ."(l.lock_type<>2 AND l.lock_type<>5 AND l.system_time<unix_timestamp()-" . OFFLINE_THRESHOLD . ") as offline, "
            ."(l.lock_type<>2 AND l.lock_type<>5 AND l.system_time<unix_timestamp()-" . LONG_TIME_OFFLINE_THRESHOLD . ") as offline24, "
            ."round(l.gz/100) as gprs, l.gz%100 as gps, FORMAT(l.gx/100,2) as battery_voltage, FORMAT(l.gy/100,2) as charging_voltage, "
            ."l.serialnum, (l.serialnum < 64 AND (l.serialnum & 32)=32) as charging,  (l.serialnum < 64 AND (l.serialnum & 16)=16) as moving, "
            ."(l.serialnum < 64 AND (l.serialnum & 8)=8) as closed, (l.serialnum < 64 AND (l.serialnum & 4)=4) as low_battary_alarm, "
            ."(l.serialnum < 64 AND (l.serialnum & 2)=2) as illegal_moving_alarm, (l.serialnum < 64 AND (l.serialnum & 1)=1) as gps_positioning, "
            ."(f.bicycle_id IS NOT NULL) AS hasFault, f.add_time AS faultTime, "
            ."(il.bicycle_id IS NOT NULL) AS hasIllegalParking, il.add_time AS illegalParkingTime, "
            ."(CASE WHEN b.last_used_time<>0 THEN FLOOR((unix_timestamp()-b.last_used_time)/86400) ELSE 0 END) AS noUsedDays, "
            ."b.is_using, (od3.order_id IS NOT NULL) AS cant_finish "
            ."FROM ".DB_PREFIX."bicycle as b "
            ."LEFT JOIN ".DB_PREFIX."lock as l ON b.lock_sn=l.lock_sn "
            ."LEFT JOIN ".DB_PREFIX."region as r ON b.region_id=r.region_id "
            ."LEFT JOIN ".DB_PREFIX."cooperator as co ON b.cooperator_id=co.cooperator_id "
            ."LEFT JOIN (SELECT bicycle_id, add_time FROM ".DB_PREFIX."fault WHERE processed=0 ORDER BY add_time ASC ) AS f on b.bicycle_id = f.bicycle_id "
            ."LEFT JOIN (SELECT bicycle_id, add_time FROM ".DB_PREFIX."illegal_parking WHERE processed=0 ORDER BY add_time ASC ) AS il on b.bicycle_id = il.bicycle_id "
            ."LEFT JOIN (SELECT bicycle_id, order_id FROM ".DB_PREFIX."orders WHERE order_state=-3) AS od3 ON b.bicycle_id = od3.bicycle_id "
            ."WHERE l.lat<>'' AND l.lng<> '' "
            .(empty($cooperator_id) ? '': ' AND b.cooperator_id=' . ($cooperator_id + 0) . ' ')
            ."AND l.lat>=$min_lat AND l.lat<=$max_lat " . $lng_bound
            ." GROUP BY b.bicycle_id";
        return $this->db->getRows($sql);
    }

    public function getCurrentLocationBicy($min_lat, $min_lng, $max_lat, $max_lng, $status = 1, $cooperator_id = '') {

        $time_code = time() - 2*60*60;
        if(!$cooperator_id){
            $where = " l.lat >= $min_lat AND l.lat <= $max_lat AND l.lng >= $min_lng AND l.lng <= $max_lng "; //
        }else{
            $where = " l.lat >= $min_lat AND l.lat <= $max_lat AND l.lng >= $min_lng AND l.lng <= $max_lng  AND b.cooperator_id = $cooperator_id "; //
        }


        $field = 'b.bicycle_id,b.bicycle_sn,b.type,b.fee,b.region_id,b.region_name,b.cooperator_id,b.is_using,b.add_time,b.keep_time,b.fault,b.illegal_parking,b.low_battery,b.full_bicycle_sn,';
        $field .= 'l.lock_status,l.lock_type,l.system_time,l.device_time,l.open_nums,l.battery,l.use_status,l.serialnum,l.transfer_url,l.last_close_time,l.lock_sn,l.lock_id,l.lat,l.lng,l.gx,l.lock_type,l.encrypt_key,l.password,l.mac_address,';
        $field .= '(select user_id from '.DB_PREFIX.'orders where bicycle_id = b.bicycle_id and order_state = 1 limit 1) as user_id, (select end_time from '.DB_PREFIX.'orders where bicycle_id = b.bicycle_id and order_state > 0 order by end_time DESC limit 1) as use_end_time,';
        $field .= ' (select repair_id from '.DB_PREFIX.'repair where add_time > '.$time_code.' AND find_in_set(5,repair_type) AND b.bicycle_id = bicycle_id) as checkLowBattery,';
        $field .= ' (select add_time from '.DB_PREFIX.'illegal_parking where bicycle_id = b.bicycle_id  AND processed < 1 order by add_time ASC limit 1) as illegal_parking_add_time ';

        $sql = 'select '.$field.' from '.DB_PREFIX.'bicycle as b ';
        $sql .= ' LEFT JOIN '.DB_PREFIX.'lock as l ON b.lock_sn = l.lock_sn ';
        $sql .= ' where '.$where;
        //echo $sql;
        $res = $this->db->getRows($sql);
        return $res;

    }


}
