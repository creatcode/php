<?php
class ControllerLockIndex extends Controller {
    /**
     * 获取蓝牙初始化信息
     */
    public function getBLTLockInitMsg() {
        $input = $this->request->post(array('lock_sn', 'mac_address', 'encrypt_key', 'password', 'lock_factory', 'lock_type'));

        $mac = $encrypt_key = $password = '';
        // MAC不为空时
        if (!empty($input['mac_address'])) {
            $mac = strstr($input['mac_address'], ':') ? $input['mac_address'] : strtoupper(substr(chunk_split($input['mac_address'], 2, ':'), 0, 17));
        }
        // 蓝牙锁，机械锁，二合一锁都需要密钥
        if (in_array($input['lock_type'], array(2, 3, 4, 5))) {
            // 物理地址，ios传的不带冒号
            $encrypt_key = empty($input['encrypt_key']) ? substr(sha1(token(16)), 0, 16) : $input['encrypt_key'];
            $password = empty($input['password']) ? substr(sha1(token(6)), 0, 6) : $input['password'];
        }

        $temp_info = $this->db->table('mac_temp')->where(array('lock_sn' => $input['lock_sn']))->find();
        if (!empty($temp_info)) {
            if ($temp_info['state'] == 1) {
                $this->response->showErrorResult('此锁已入库');
            }
            $update = array(
                //'encrypt_key' => $encrypt_key,
                //'password' => $password,
                'lock_factory' => $input['lock_factory'],
                'lock_type' => $input['lock_type']
            );
	    if ($input['encrypt_key']) $update['encrypt_key'] = $encrypt_key;
	    if ($input['password']) $update['password'] = $password;
            $this->db->table('mac_temp')->where(array('lock_sn' => $input['lock_sn']))->update($update);
            $temp_info = array_merge($temp_info, $update);
            $this->response->showSuccessResult($temp_info);
        }

        $insert = array(
            'lock_sn' => $input['lock_sn'],
            'mac_address' => $mac,
            'encrypt_key' => $encrypt_key,
            'password' => $password,
            'lock_factory' => $input['lock_factory'],
            'lock_type' => $input['lock_type'],
			'add_time' => time()
        );

        $res = $this->db->table('mac_temp')->insert($insert);
        $res ? $this->response->showSuccessResult($insert) : $this->response->showErrorResult('锁数据后台保存失败，请重试');
    }

    /**
     * 打开GPRS锁
     */
    public function openGRPLock() {
        $post = $this->request->post(array('lock_sn'));
        if (!$post['lock_sn']) {
            $this->response->showErrorResult('锁编号不能为空');
        }

        $this->instructions_instructions = new Instructions\Instructions($this->registry);
        $this->instructions_instructions->openLock($post['lock_sn'], time());

        $this->response->showSuccessResult($post['lock_sn'], '开锁指令已发');
    }

    /**
     * 绑定锁单车
     */
    public function putInStorage() {
        $post = $this->request->post(array('bicycle_sn', 'lock_sn'));

        if ($post['lock_sn']) {
            $mac_info = $this->db->table('mac_temp')->where(array('lock_sn' => $post['lock_sn']))->find();
            if (empty($mac_info)) {
                $this->response->showErrorResult('锁数据未保存，请重新操作');
            }

            $lock_info = $this->db->table('lock')->where(array('lock_sn' => $post['lock_sn']))->find();
			$cooperator_id = $this->request->post('cooperator_id');
            if(empty($cooperator_id)){
				$cooperator_id = 0;
			}
            if (!empty($lock_info)) {
                $update = array(
                    'encrypt_key' => $mac_info['encrypt_key'],
                    'password' => $mac_info['password'],
                    'lock_type' => $mac_info['lock_type'],
                    'lock_factory' => $mac_info['lock_factory']
                );
                $this->db->table('lock')->where(array('lock_id' => $lock_info['lock_id']))->update($update);
                $lock_id = $lock_info['lock_id'];
            } else {
                $data = array(
                    'lock_sn' => $mac_info['lock_sn'],
                    'lock_name' => $mac_info['lock_sn'],
                    'mac_address' => $mac_info['mac_address'],
                    'encrypt_key' => $mac_info['encrypt_key'],
                    'password' => $mac_info['password'],
                    'lock_factory' => $mac_info['lock_factory'],
                    'lock_type' => $mac_info['lock_type'],
                    'cooperator_id' => $cooperator_id
                );
                $lock_id = $this->db->table('lock')->insert($data);
            }

            $bicycle_info = $this->db->table('bicycle')->where(array('full_bicycle_sn' => $post['bicycle_sn']))->find();
            if (empty($bicycle_info)) {
                $this->response->showErrorResult('系统不存在此编号的单车');
            }

            // 单车里锁编号为空是才绑定
            if (empty($bicycle_info['lock_sn'])) {
                $bicycle_lock = $this->db->table('bicycle')->field('full_bicycle_sn')->where(array('lock_sn' => $post['lock_sn']))->find();
                if (!empty($bicycle_lock) && $bicycle_lock['full_bicycle_sn'] != $bicycle_info['full_bicycle_sn']) {
                    $this->response->showErrorResult('此锁已入库' . '绑定的编号为' . $bicycle_lock['full_bicycle_sn']);
                }
                $condition = array(
                    'bicycle_id' => $bicycle_info['bicycle_id']
                );
                $data = array(
                    'lock_id' => $lock_id,
                    'lock_sn' => $post['lock_sn'],
                    'cooperator_id' => $cooperator_id
                );
                $update = $this->db->table('bicycle')->where($condition)->update($data);
                if ($update) {
                    $this->db->table('mac_temp')->where(array('lock_sn' => $post['lock_sn']))->update(array('state' => 1));
                    $this->response->showSuccessResult('', '入库成功');
                }
                else {
                    $this->response->showErrorResult('入库失败');
                }
            } else {
                if ($bicycle_info['lock_sn'] != $lock_info['lock_sn']) $this->response->showErrorResult('此单车二维码已入库');
            }
            $this->db->table('mac_temp')->where(array('lock_sn' => $post['lock_sn']))->update(array('state' => 1));
            $this->response->showSuccessResult('', '入库成功');
        } else {
            $this->response->showErrorResult('锁编号不能为空');
        }
    }

    public function getCooperator()
    {
        $this->load->library('sys_model/cooperator', true);
        $cooperator_list = $this->sys_model_cooperator->getCooperatorList(array('state'=>1),'','','cooperator_id,cooperator_name');
//        $cooperator_list[] = ['cooperator_id'=>0,'cooperator_name'=>'平台'];
        array_unshift($cooperator_list,['cooperator_id'=>0,'cooperator_name'=>'平台']);
        $this->response->showSuccessResult($cooperator_list, '成功获取合伙人');
    }
}
