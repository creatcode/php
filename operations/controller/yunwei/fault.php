<?php

class ControllerYunWeiFault extends Controller
{
    /**
     * 获取故障类型（弃用）
     */
    public function getFaultType()
    {
        $this->load->library('sys_model/fault');
        $result = $this->sys_model_fault->getAllFaultType();
        $this->response->showSuccessResult($result, '数据获取成功');
    }

    /**
     * 上报故障
     */
    public function addFault()
    {

        if (!isset($this->request->post['fault_type'])) {
            $this->response->showErrorResult('fault_type参数错误或缺失', 135);
        }
        if (empty($this->request->post['lat'])) {
            $this->response->showErrorResult('纬度不能为空', 136);
        }
        if (empty($this->request->post['lng'])) {
            $this->response->showErrorResult('经度不能为空', 137);
        }

        if (!(isset($this->request->post['full_bicycle_sn']) || isset($this->request->post['bicycle_sn']))) {
            $this->response->showErrorResult('参数错误或缺失', 1);
        }
        if (isset($this->request->post['full_bicycle_sn']) && $this->request->post['full_bicycle_sn']) {
            $full_bicycle_sn = trim($this->request->post['full_bicycle_sn']);
            if (!$full_bicycle_sn) {
                $this->response->showErrorResult('参数错误或缺失', 1);
            }
            $w = array('full_bicycle_sn' => $this->request->post['full_bicycle_sn']);
        } else {
            $bicycle_sn = trim($this->request->post['bicycle_sn']);
            if (!$bicycle_sn) {
                $this->response->showErrorResult('参数错误或缺失', 1);
            }
            $w = array('bicycle_sn' => $this->request->post['bicycle_sn']);
        }

        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/fault', true);
        $user_info = $this->startup_user->getUserInfo();
        $data['user_id'] = $user_info['admin_id'];
        $data['user_name'] = $user_info['admin_name'];
        $data['cooperator_id'] = $user_info['cooperator_id'];
        $data['reporter_type'] = 2;
        $data['fault_type'] = (is_array($this->request->post['fault_type']) && !empty($this->request->post['fault_type'])) ? implode(',', $this->request->post['fault_type']) : $this->request->post['fault_type'];
        $data['add_time'] = time();
        $data['lat'] = $this->request->post['lat'];
        $data['lng'] = $this->request->post['lng'];

        $data['fault_content'] = isset($this->request->post['fault_content']) ? $this->request->post['fault_content'] : '';

        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo($w);
        if (empty($bicycle_info)) {
            $this->response->showErrorResult('系统不存在此编号的单车', 140);
        }

        $data['bicycle_id'] = $bicycle_info['bicycle_id'];
        $data['bicycle_sn'] = $bicycle_info['bicycle_sn'];
        # 添加单车编号
        $data['lock_sn'] = $bicycle_info['lock_sn'];
        # h5可能会用到base64记得转码的问题，获取到的数据需要base64_decode
        $file_info['state'] = 'FAILURE';
        if (isset($this->request->files['fault_image']) || isset($this->request->post['fault_image'])) {
            $uploader = new Uploader(
                'fault_image',
                array(
                    'allowFiles' => array('.jpeg', '.jpg', '.png'),
                    'maxSize' => 10 * 1024 * 1024,
                    'pathFormat' => 'fault/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:4}'
                ),
                empty($this->request->files['fault_image']) ? 'base64' : 'upload', // upload, base64 or remote
                $this->request->files //文件上传变量数组，base64的不用提供，内部直接用$_POST[字段名]作为数据
            );
            $file_info = $uploader->getFileInfo();
        }
        if ($file_info['state'] == 'SUCCESS') {
            $data['fault_image'] = $file_info['url'];
        }
        # 故障描述
        $data['fault_content'] = isset($this->request->post['fault_content']) ? $this->request->post['fault_content'] : "";
        $insert_id = $this->sys_model_fault->addFault($data);
        # 举报成功；修改rich_bicycle的 fault；
        $w = array(
            'bicycle_id' => $bicycle_info['bicycle_id']
        );
        $this->sys_model_bicycle->updateBicycle($w, array("fault" => 1));

        $insert_id ? $this->response->showSuccessResult(array('fault_id' => $insert_id), '上报成功') : $this->response->showErrorResult('数据库操作失败', 4);
    }

    /**
     * 违规停车
     */
    public function addIllegalParking()
    {
        if (!isset($this->request->post['lat']) || empty($this->request->post['lat'])) {
            $this->response->showErrorResult('纬度不能为空', 136);
        }
        if (!isset($this->request->post['lng']) || empty($this->request->post['lng'])) {
            $this->response->showErrorResult('经度不能为空', 137);
        }
        if (!isset($this->request->post['type']) || empty($this->request->post['type'])) {
            $this->request->post['type'] = 1;
        }
        if (isset($this->request->post['content']) && mb_strlen($this->request->post['content']) > 100) {
            $this->response->showErrorResult('备注不能大于100个字', 138);
        }

        if (!(isset($this->request->post['full_bicycle_sn']) || isset($this->request->post['bicycle_sn']))) {
            $this->response->showErrorResult('参数错误或缺失', 1);
        }
        if (isset($this->request->post['full_bicycle_sn']) && $this->request->post['full_bicycle_sn']) {
            $full_bicycle_sn = trim($this->request->post['full_bicycle_sn']);
            if (!$full_bicycle_sn) {
                $this->response->showErrorResult('参数错误或缺失', 1);
            }
            $w = array('full_bicycle_sn' => $this->request->post['full_bicycle_sn']);
        } else {
            $bicycle_sn = trim($this->request->post['bicycle_sn']);
            if (!$bicycle_sn) {
                $this->response->showErrorResult('参数错误或缺失', 1);
            }
            $w = array('bicycle_sn' => $this->request->post['bicycle_sn']);
        }

        $user_info = $this->startup_user->getUserInfo();
        $data['lat'] = $this->request->post['lat'];
        $data['lng'] = $this->request->post['lng'];
        $data['content'] = isset($this->request->post['content']) ? $this->request->post['content'] : '';
        $data['user_id'] = $user_info['admin_id'];
        $data['user_name'] = $user_info['nickname'];
        $data['type'] = $this->request->post['type'];
        $data['add_time'] = time();
        $data['reporter_type'] = 2;
        $data['cooperator_id'] = $user_info['cooperator_id'];
        $this->load->library('sys_model/bicycle', true);
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo($w);
        if (empty($bicycle_info)) {
            $this->response->showErrorResult('系统不存在此编号的单车', 140);
        }
        $data['bicycle_id'] = $bicycle_info['bicycle_id'];
        $data['bicycle_sn'] = $bicycle_info['bicycle_sn'];

        $file_info['state'] = 'FAILURE';
        if (isset($this->request->files['file_image']) || isset($this->request->post['file_image'])) {
            $uploader = new Uploader(
                'file_image',
                array(
                    'allowFiles' => array('.jpeg', '.jpg', '.png'),
                    'maxSize' => 10 * 1024 * 1024,
                    'pathFormat' => 'illegal_parking/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:4}'
                ),
                empty($this->request->files['file_image']) ? 'base64' : 'upload', // upload, base64 or remote
                $this->request->files //文件上传变量数组，base64的不用提供，内部直接用$_POST[字段名]作为数据
            );
            $file_info = $uploader->getFileInfo();
        }

        if ($file_info['state'] == 'SUCCESS') {
            $data['file_image'] = $file_info['url'];
        }
        $this->load->library('sys_model/fault', true);
        $insert_id = $this->sys_model_fault->addIllegalParking($data);
        # 举报成功；修改rich_bicycle 中的illegal_parking；

        $w = array(
            'bicycle_id' => $bicycle_info['bicycle_id']
        );
        $this->sys_model_bicycle->updateBicycle($w, array("illegal_parking" => 1));

        $insert_id ? $this->response->showSuccessResult(array('parking_id' => $insert_id), '上报成功') : $this->response->showErrorResult('数据库操作失败', 4);
    }

    # 获取我的举报
    public function getMyReport()
    {

        $user_info = $this->startup_user->getUserInfo();
        if (empty($user_info)) {
            $this->response->showErrorResult('请先登录', 119);
        }
        if (isset($this->request->post['page']) && $this->request->post['page'] > 0) {
            $page = $this->request->post['page'];
        } else {
            $page = 1;
        }
        $this->load->library('sys_model/fault', true);
        $data['user_id'] = $user_info['admin_id'];
        $data['user_name'] = $user_info['admin_name'];
        $w = array();
        $order = "add_time DESC";
        $limit_code = $this->config->get('config_limit_admin');
        $start = ($page - 1) * $limit_code;
        $limit = "$start,$limit_code";
        $result = $this->sys_model_fault->getFaultList($w, $order, $limit);

        $this->response->showSuccessResult($result, '数据获取成功');
    }

    public function getOperatorsInfo()
    {
        date_default_timezone_set('PRC');
        $this->load->library('sys_model/admin');
        $this->load->library('sys_model/operations_position');
        $this->load->library('sys_model/repair');

        $admin_info = $this->startup_user->getUserInfo();

        if (!$admin_info['operation_ruler']) {
            $this->response->showErrorResult('不是运维管理者');
        }

        //根据管理人员所属合伙人查看运维人员数据
        $admin_list = $this->sys_model_admin->getAdminList(['cooperator_id' => $admin_info['cooperator_id'], 'type' => 3, 'state' => 1], '', '', 'admin_id,admin_name,nickname');

        $data = [];

        foreach ($admin_list as &$admin) {
            //获取运维人员的最新定位
            $condition = [
                'operator_id' => $admin['admin_id']
            ];
            $admin['last_position'] = $this->sys_model_operations_position->getOperationsPosition($condition, 'lng,lat');
            //如果没有数据整个放弃，到下一个循环
            if(!$admin['last_position']){
                continue;
            }
            //获取运维人员维修故障数
            $condition = [
                'admin_id' => $admin['admin_id'],
                'add_time' => [
                    ['gt', strtotime(date('Y-m-d'))],
                    ['elt', time()]
                ]
            ];
            $admin['repair_num'] = $this->sys_model_repair->getTotalRepairs($condition);
            $data[] = $admin;
        }

        $this->response->showSuccessResult($data, '添加成功');
    }


}