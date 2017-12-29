<?php
namespace Sys_Model;

class Intelligent {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
		$this->registry=$registry;
		
    }
	/**
     * 获取长期定位单车数
     */
    public function getTotalLbsBicycles($where) {
		$join = 'intelligent.device_id=bicycle.lock_sn';
		$field='count(1) as total';
        $total = $this->db->table('intelligent_lbs intelligent,bicycle bicycle')->where($where)->field($field)->join('left')->on($join)->select();
		//var_dump($this->db->getLastSql());
		if(empty($total[0]['total'])){
			return 0;
		}else{
			return $total[0]['total'];
		}
    }
	 /**
     * 获取长期定位单车列表
     * @param array $where
     * @param string $limit
	 * @param string $field
     * @return mixed
     */
    public function getBicycleList($where = array(),  $limit = '', $field = 'intelligent.*') {
		$join = 'intelligent.device_id=bicycle.lock_sn,bicycle.cooperator_id=cooperator.cooperator_id';
        $result = $this->db->table('intelligent_lbs intelligent,bicycle bicycle,cooperator cooperator')->where($where)->field($field)->join('left,left')->on($join)->limit($limit)->select();
        return $result;
		
    }
	/**
     * 获取重复单车/锁的总数
     * @return int
     */
	public function getTotalRepeatdata($table='bicycle as bicycle',$group,$count) {
        $sql='select count(1) as total from '.$table.' group by '.$group.' having count('.$count.')>1';
        $total=$this->db->getRows($sql);
		if(empty($total[0]['total'])){
			return 0;
		}else{
			return $total[0]['total'];
		}
    }
	/**
     * 获取重复单车/锁的数据
     * @return int
     */
	public function getRepeatdataList($type,$limit) {
		
        $return=array();
		if($type=='bicycle'){
			$field = 'bicycle.*,region.region_name,cooperator.cooperator_name,lock.lock_type';
			$join = array(
				'region' => 'region.region_id=bicycle.region_id',
				'cooperator' => 'cooperator.cooperator_id=bicycle.cooperator_id',
				'lock' => 'lock.lock_sn=bicycle.lock_sn'
			);
			$sql='select bicycle_sn from rich_bicycle group by bicycle_sn having count(1)>1';
			$result=$this->db->getRows($sql);
			$sys_model_bicycle = new \Sys_Model\Bicycle($this->registry);
			foreach($result as $key=>$val){
				$condition=array(
					'bicycle_sn'=>$val['bicycle_sn']
				);
				$result2 = $sys_model_bicycle->getBicycleList($condition, '', $limit, $field, $join);
				array_push($return,$result2);	
			}
			return $return;
		}else if($type=='lock'){
			$field = 'l.*,cooperator.cooperator_name';
			$join = array(
				'cooperator' => 'cooperator.cooperator_id=l.cooperator_id'
			);
			$sql='select lock_sn from rich_lock group by lock_sn having count(1)>1';
			$result=$this->db->getRows($sql);
			$sys_model_lock = new \Sys_Model\Lock($this->registry);
			foreach($result as $key=>$val){
				$condition=array(
					'lock_sn'=>$val['lock_sn']
				);
				$result2 = $sys_model_lock->getLockList($condition, '', $limit, $field, $join);
				array_push($return,$result2);	
			}
			return $return;
		}
		
    }

    public function getAbnormalOrdersList($where, $order = '', $limit = '', $field = '*', $join = array(), $group = '', $having = '')
    {
        $table = 'orders as orders';
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

        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->group($group)->having($having)->select();
    }

}