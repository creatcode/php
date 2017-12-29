<?php
class ControllerSystemOperations extends Controller {
    private $cur_url = null;
    private $error = null;
    private $admin_name_prefix = 'yw_';
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载admin Model
        $this->load->library('sys_model/admin', true);
        $this->load->library('sys_model/rbac', true);
        $this->load->library('logic/admin', true);
    }

    /**
     * 管理员列表
     */
    public function index() {
        $filter = $this->request->get(array('admin_name', 'login_time', 'mobile', 'state'));

        $condition = array(
            'type' => '1'
        );
        if (!empty($filter['admin_name'])) {
            $condition['admin_name'] = array('like', "%{$filter['admin_name']}%");
        }
        if (!empty($filter['login_time'])) {
            $login_time = explode(' 至 ', $filter['login_time']);
            $condition['login_time'] = array(
                array('egt', strtotime($login_time[0])),
                array('elt', bcadd(86399, strtotime($login_time[1])))
            );
        }
        if (is_numeric($filter['mobile'])) {
            $condition['mobile'] = array("like","%".$filter['mobile']."%");
        }
        if (is_numeric($filter['state'])) {
            $condition['state'] = (int)$filter['state'];
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        // 所有角色
        $roles = array();
        $roleList = $this->sys_model_rbac->getRoleList();
        if (!empty($roleList)) {
            foreach ($roleList as $v) {
                $roles[$v['role_id']] = $v['role_name'];
            }
        }
        // 所有运维人员
        $order  = 'add_time DESC';
        $rows   = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit  = sprintf('%d, %d', $offset, $rows);
        $condition['type'] = 3;
        $result = $this->sys_model_admin->getAdminList($condition, $order, $limit);
        $total = $this->sys_model_admin->getTotalAdmins($condition);
        $state = get_setting_boolean();
        $this->load->library("sys_model/cooperator");
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $CooperatorInfo = $this->sys_model_cooperator->getCooperatorInfo(array("cooperator_id" => $item['cooperator_id']));
                $item['cooperator_name'] = $item['cooperator_id'] > 0 ? $CooperatorInfo['cooperator_name'] : '';
                $item['state']            = isset($state[$item['state']]) ? $state[$item['state']] : '';
                $item['login_time']      = !empty($item['login_time']) ? date('Y-m-d H:i:s', $item['login_time']) : '';
                $item['mobile']          = isset($item['mobile']) ? $item['mobile'] : '';
                $item['edit_action']    = $this->url->link('system/operations/edit', 'admin_id='.$item['admin_id']);
                $item['delete_action']  = $this->url->link('system/operations/delete', 'admin_id='.$item['admin_id']);
                $item['info_action']    = $this->url->link('system/operations/info', 'admin_id='.$item['admin_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('roles', $roles);
        $this->assign('state', $state);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('system/operations/add'));

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->response->setOutput($this->load->view('system/operations_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('用户名称');
        $this->setDataColumn('电话');
        $this->setDataColumn('所属公司');
        $this->setDataColumn('最后登录时间');
        $this->setDataColumn('状态');
        return $this->data_columns;
    }

    /**
     * 添加管理员
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('admin_name', 'nickname', 'password', 'state', 'role_id','mobile', 'cooperator_id'));
            $this->load->library('sys_model/rbac', true);
           
            $now = time();
            $data = array(
                'role_id'        => $input['role_id'],
                'mobile'         => $input['mobile'],
                'type'           => 3,
                'cooperator_id'  => $input['cooperator_id'] ? $input['cooperator_id'] : 0,
                'admin_name'     => $this->admin_name_prefix . $input['admin_name'],
                'nickname'       => $input['nickname'],
                'password'       => $input['password'],
                'state'          => $input['state'] ? 1 : 0,
                'add_time'       => $now
            );
            $admin_id = $this->logic_admin->add($data);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加运维人员：'.$admin_id,
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);


            $this->session->data['success'] = '添加运维人员成功！';

            $filter = $this->request->get(array('admin_name', 'login_time', 'mobile', 'state'));

            $this->load->controller('common/base/redirect', $this->url->link('system/operations', $filter, true));
        }

        $this->assign('title', '管理员添加');
        $this->getForm();
    }

    /**
     * 编辑管理员
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('nickname', 'password', 'state', 'role_id', 'mobile', 'cooperator_id'));
            $admin_id = $this->request->get['admin_id'];
            $data = array(
                'nickname'  => $input['nickname'],
                'state'     => $input['state'] ? 1 : 0,
                'role_id'   => $input['role_id'],
                'mobile'    => $input['mobile'],
                'cooperator_id' => $input['cooperator_id']
            );
            if (!empty($input['password'])) {
                $data['password'] = $input['password'];
            }
            $condition = array(
                'admin_id' => $admin_id
            );
            $this->logic_admin->update($condition, $data);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id'          => $this->logic_admin->getId(),
                'admin_name'        => $this->logic_admin->getadmin_name(),
                'log_description'  => '编辑维护人员信息：'.$admin_id,
                'log_ip'            => $this->request->ip_address(),
                'log_time'          => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->session->data['success'] = '编辑管理员成功！';

            $filter = $this->request->get(array('admin_name', 'login_time', 'role_id', 'state', 'mobile'));

            $this->load->controller('common/base/redirect', $this->url->link('system/operations', $filter, true));
        }

        $this->assign('title', '编辑管理员');
        $this->getForm();
    }

    /**
     * 删除管理员
     */
    public function delete() {
        if (isset($this->request->get['admin_id']) && $this->validateDelete()) {
            $condition = array(
                'admin_id' => $this->request->get['admin_id']
            );
            $this->sys_model_admin->deleteAdmin($condition);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '删除管理员：'.$this->request->get['admin_id'],
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->session->data['success'] = '删除管理员成功！';
        }
        $filter = $this->request->get(array('admin_name', 'login_time', 'role_id', 'state'));
        $this->load->controller('common/base/redirect', $this->url->link('system/admin', $filter, true));
    }

    /**
     * 管理员详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $admin_id = $this->request->get('admin_id');
        $condition = array(
            'admin_id' => $admin_id
        );
        $info = $this->sys_model_admin->getAdminInfo($condition);
        if (!empty($info)) {
            $state = get_setting_boolean();
            $info['state'] = isset($state[$info['state']]) ? $state[$info['state']] : '';
            $info['login_time'] = !empty($info['login_time']) ? date('Y-m-d H:i:s', $info['login_time']) : '';
            $info['add_time'] = !empty($info['add_time']) ? date('Y-m-d H:i:s', $info['add_time']) : '';
        }

        $this->assign('data', $info);

        $this->response->setOutput($this->load->view('system/admin_info', $this->output));
    }

    private function getForm() {
        #编辑时获取已有的数据
        $info = $this->request->post(array('admin_name', 'nickname', 'password', 'confirm', 'role_id', 'state', 'mobile', 'cooperator_id'));
        $admin_id = $this->request->get('admin_id');
        $this->load->library("sys_model/cooperator");
        #所有角色
        $roles = array();
        # 要判断登录的账号是否是管理员； 不是管理员的话就要做 是那个合伙人添加运维人员 做cooperator_id 的区分判断  #$w     = array("cooperator_id" => 1);
        $admin_data = $this->logic_admin->getData();
        if($admin_data['type'] && $admin_data['type'] > 1){
            $w["cooperator_id"] = $admin_data['cooperator_id'];
            $cooperator_arr[] = $this->sys_model_cooperator->getCooperatorInfo(array("state" => 1, 'cooperator_id' => $admin_data['cooperator_id']));
        }else{
            $cooperator_arr = $this->sys_model_cooperator->getCooperatorList(array("state" => 1));
        }
        $w["type"] = 3;
        $roleList = $this->sys_model_rbac->getRoleList($w);
        if (!empty($roleList)) {
            foreach ($roleList as $v) {
                $roles[$v['role_id']] = $v['role_name'];
            }
        }
        # 默认账号启用
        $info['state'] = $info['state'] != '' ? $info['state'] : 1;
        if (isset($this->request->get['admin_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'admin_id' => $this->request->get['admin_id']
            );
            $info = $this->sys_model_admin->getAdminInfo($condition);
        }
        $this->assign('admin_id', $admin_id);
        $this->assign('roles', $roles);
        $this->assign('cooperator_arr',$cooperator_arr);
        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&admin_id=' . $admin_id);
        $this->assign('return_action', $this->url->link('system/operations'));
        $this->assign('error', $this->error);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('system/operations_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {

        $route          = $this->request->get('route');
        $mobile         = $this->request->post('mobile');
        $password       = $this->request->post('password');
        $confirm        = $this->request->post('confirm');
        $cooperator_id  = $this->request->post('cooperator_id');
        $admin_name     = $this->request->post('admin_name');
        if ($route == 'system/operations/add') {
            if (empty($password)) {
                $this->error['password'] = '请输入密码！';
            }
            if(empty($admin_name)){
                $this->error['admin_name'] = '用户名不能为空！';
            }
            if (empty($this->request->post['nickname'])) {
                $this->error['nickname'] = '姓名不能为空';
            }
            if (!preg_match('/^[0-9a-z]*$/i', $admin_name)) {
                $this->error['admin_name'] = '用户名必须由字母或者数字组成';
            }
        }
        if (!empty($password)) {
            if ($password !== $confirm) {
                $this->error['confirm'] = '两次输入密码不正确！';
            }
        }
        if(empty($mobile)){
            $this->error['mobile'] = '请输入手机号码！';
        }      
        if(!is_numeric($cooperator_id)){
            $this->error['cooperator_id'] = '账号归属参数不正确！';
        }
        # 判断该账号是否已经添加过；
        $result = $this->sys_model_admin->getAdminInfo(array("admin_name" => $this->admin_name_prefix . $admin_name));
        if(!empty($result)){
            $this->error['admin_name'] = '用户名已经存在，不能添加！';
        }
        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }

        return !$this->error;
    }

    /**
     * 验证删除条件
     */
    private function validateDelete() {
        return !$this->error;
    }
}
