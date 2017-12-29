<?php
namespace Logic;
class cooperator {
    private $cooperator_id;
    private $cooperator_name;
    private $data = array();

    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->request = $registry->get('request');
        $this->session = $registry->get('session');
        $this->cooperator = new \Sys_Model\cooperator($registry);

        // 如果已经登录就获取用户信息
        if (isset($this->session->data['cooperator_id'])) {
            $condition = array(
                'cooperator_id' => $this->session->data['cooperator_id'],
                'state' => 1
            );
            $user = $this->cooperator->getcooperatorInfo($condition);
            if (!empty($user) && is_array($user)) {
                $this->cooperator_id = $user['cooperator_id'];
                $this->cooperator_name = $user['cooperator_name'];
                $this->data = $user;
            } else {
                $this->logout();
            }
        }
    }
}