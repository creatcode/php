<?php
namespace Sys_Model;

class Admin_Menu_Collect {
	public function __construct($registry) {
		$this->db = $registry->get('db');
	}

    // -------------------------------------------- 写 --------------------------------------------

	public function addCollect($data) {
        return $this->db->table('admin_collect')->insert($data);
    }

    public function updateCollect($where, $data) {
        return $this->db->table('admin_collect')->where($where)->update($data);
    }

    // -------------------------------------------- 读 --------------------------------------------

    public function getCollect($where = '', $fields = '*') {
        return $this->db->table('admin_collect')->where($where)->field($fields)->find();
    }

    public function getCollectList($fields = '*') {
        return $this->db->getRows('select '.$fields.' from rich_admin_collect c left join rich_rbac_menu m on c.menu_id = m.menu_id where c.status = 1 order by c.time desc');
    }

}