<?php
class ControllerCooperatorAccount extends Controller {
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载admin Model
        $this->load->library('sys_model/admin', true);
        $this->load->library('sys_model/region', true);
    }

    /**
     * 操作员列表
     */
    public function index() {
        $cooperator_id = $this->request->get('cooperator_id');
        $filter = $this->request->get(array('admin_name'));

        // 合伙人所有区域
        $regions = $allRegions = array();
        $condition = array(
            'cooperator_id' => $cooperator_id
        );
        $limit = $order = '';
        $field = 'region.*';
        $join = array(
            'region' => 'region.region_id=cooperator_to_region.region_id'
        );
        $regionList = $this->sys_model_region->getCooperatorToRegionList($condition, $order, $limit, $field, $join);
        if (is_array($regionList) && !empty($regionList)) {
            foreach ($regionList as $region) {
                $allRegions[] = array(
                    'id' => $region['region_id'],
                    'name' => $region['region_name'],
                    'parent_id' => 0,
                );
            }
        }
        unset($regionList);

        // 操作员列表
        $condition = array(
            'admin.cooperator_id' => $cooperator_id
        );
        if (!empty($filter['admin_name'])) {
            $condition['admin_name'] = array('like', "%{$filter['admin_name']}%");
        }
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $field = 'admin.*,rbac_role.role_name';

        $join = array(
            'rbac_role' => 'rbac_role.role_id=admin.role_id'
        );

        $result = $this->sys_model_admin->getAdminList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_admin->getTotalAdmins($condition);

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $selectedRegions = array();
                $condition = array(
                    'admin_id' => $item['admin_id']
                );
                $adminRegions = $this->sys_model_region->getAdminToRegionList($condition);
                foreach ($adminRegions as $val) {
                    $selectedRegions[] = $val['region_id'];
                }
                $regions[$item['admin_id']] = $this->_getTreeData($allRegions, $selectedRegions);
                $item['regions_num'] = count($selectedRegions);

                $item['edit_action'] = $this->url->link('cooperator/account/edit', 'admin_id='.$item['admin_id'] . '&cooperator_id='.$cooperator_id);
                $item['delete_action'] = $this->url->link('cooperator/account/delete', 'admin_id='.$item['admin_id'] . '&cooperator_id='.$cooperator_id);
                $item['info_action'] = $this->url->link('cooperator/account/info', 'admin_id='.$item['admin_id'] . '&cooperator_id='.$cooperator_id);
            }
        }

        $data_columns = $this->getDataColumns();
        $regions = json_encode($regions);
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('static', HTTP_CATALOG);
        $this->assign('filter', $filter);
        $this->assign('regions', $regions);
        $this->assign('action', $this->cur_url . '&cooperator_id='.$cooperator_id);
        $this->assign('add_action', $this->url->link('cooperator/account/add', 'cooperator_id='.$cooperator_id));
        $this->assign('cooperator_action', $this->url->link('cooperator/cooperator/info', 'cooperator_id='.$cooperator_id));
        $this->assign('region_action', $this->url->link('cooperator/region', 'cooperator_id='.$cooperator_id));
        $this->assign('bicycle_action', $this->url->link('cooperator/bicycle', 'cooperator_id='.$cooperator_id));
        $this->assign('role_action', $this->url->link('cooperator/role', 'cooperator_id='.$cooperator_id));
        $this->assign('update_admin_region_action', $this->url->link('cooperator/account/update_admin_region'));

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

        $this->response->setOutput($this->load->view('cooperator/account_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('登录名');
        $this->setDataColumn('角色');
        $this->setDataColumn('区域管辖');
        return $this->data_columns;
    }

    /**
     * 添加管理员
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $cooperator_id = $this->request->get('cooperator_id');
            $input = $this->request->post(array('admin_name', 'password', 'state', 'role_id'));
            $now = time();
            $data = array(
                'role_id' => $input['role_id'],
                'type' => 2,
                'cooperator_id' => $cooperator_id,
                'admin_name' => $input['admin_name'],
                'password' => $input['password'],
                'state' => $input['state'] ? 1 : 0,
                'add_time' => $now
            );
            $admin_id = $this->logic_admin->add($data);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：添加合伙人管理员：'.$admin_id,
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 6,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);


            $this->session->data['success'] = '添加管理员成功！';

            $filter = $this->request->get(array('admin_name', 'login_time', 'role_id', 'state'));

            $this->load->controller('common/base/redirect', $this->url->link('cooperator/account') . '&' . http_build_query($filter) . '&cooperator_id=' . $cooperator_id);
        }

        $this->assign('title', '管理员添加');
        $this->getForm();
    }

    /**
     * 编辑管理员
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $cooperator_id = $this->request->get('cooperator_id');
            $input = $this->request->post(array('password', 'state', 'role_id'));
            $admin_id = $this->request->get['admin_id'];
            $data = array(
                'state' => $input['state'] ? 1 : 0,
                'role_id' => $input['role_id']
            );
            if (!empty($input['password'])) {
                $data['password'] = $input['password'];
            }
            $condition = array(
                'admin_id' => $admin_id,
                'cooperator_id' => $cooperator_id
            );
            $this->logic_admin->update($condition, $data);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：编辑合伙人管理员：'.$admin_id,
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 6,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->session->data['success'] = '编辑管理员成功！';

            $filter = $this->request->get(array('admin_name', 'login_time', 'role_id', 'state'));

            $this->load->controller('common/base/redirect', $this->url->link('cooperator/account') . '&' . http_build_query($filter) . '&cooperator_id=' . $cooperator_id);
        }

        $this->assign('title', '编辑管理员');
        $this->getForm();
    }

    /**
     * 删除管理员
     */
    public function delete() {
        //加载管理员 model
        $this->load->library('sys_model/admin', true);

        $cooperator_id = $this->request->get('cooperator_id');
        if (isset($this->request->get['admin_id']) && $this->validateDelete()) {
            $condition = array(
                'admin_id' => $this->request->get['admin_id'],
                'cooperator_id' => $cooperator_id
            );
            $this->sys_model_admin->deleteAdmin($condition);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：删除管理员：'.$this->request->get['admin_id'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 6,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->session->data['success'] = '删除合伙人管理员成功！';
        }
        $filter = $this->request->get(array('admin_name', 'login_time', 'role_id', 'state'));
        $this->load->controller('common/base/redirect', $this->url->link('cooperator/account') . '&' . http_build_query($filter) . '&cooperator_id=' . $cooperator_id);
    }

    private function getForm() {
        $this->load->library('sys_model/rbac', true);
        $cooperator_id = $this->request->get('cooperator_id');
        // 编辑时获取已有的数据
        $info = $this->request->post(array('admin_name', 'password', 'confirm', 'role_id', 'state'));
        $admin_id = $this->request->get('admin_id');

        // 所有角色
        $roles = array();
        $condition = array(
            'cooperator_id' => $cooperator_id
        );
        $roleList = $this->sys_model_rbac->getRoleList($condition);
        if (!empty($roleList)) {
            foreach ($roleList as $v) {
                $roles[$v['role_id']] = $v['role_name'];
            }
        }

        if (isset($this->request->get['admin_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'admin_id' => $this->request->get['admin_id']
            );
            $info = $this->sys_model_admin->getAdminInfo($condition);
        }

        $this->assign('admin_id', $admin_id);
        $this->assign('roles', $roles);
        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&admin_id=' . $admin_id . '&cooperator_id=' . $cooperator_id);
        $this->assign('return_action', $this->url->link('cooperator/account', 'cooperator_id=' . $cooperator_id));
        $this->assign('error', $this->error);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('cooperator/account_form', $this->output));
    }

    /**
     * 更改合伙人管辖的区域
     */
    public function update_admin_region() {
        $input = $this->request->post(array('admin_id', 'regions'));

        // 删除原有的区域
        $condition = array(
            'admin_id' => $input['admin_id']
        );
        $this->sys_model_region->deleteAdminToRegion($condition);

        // 重新绑定区域
        if (!empty($input['regions'])) {
            $regions = explode(',', $input['regions']);
            if (is_array($regions) && !empty($regions)) {
                foreach ($regions as $region_id) {
                    $data = array(
                        'admin_id' => $input['admin_id'],
                        'region_id' => $region_id
                    );
                    $this->sys_model_region->addAdminToRegion($data);
                }
            }
        }
        $this->response->showSuccessResult('', '修改成功');
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
//        $input = $this->request->post(array('state', 'type', 'lock_sn'));
//
//        foreach ($input as $k => $v) {
//            if (empty($v)) {
//                $this->error[$k] = '请输入完整！';
//            }
//        }
        $route = $this->request->get('route');
        $password = $this->request->post('password');
        $confirm = $this->request->post('confirm');
        if ($route == 'cooperator/cooperator/add') {
            if (empty($password)) {
                var_dump($this->request->post);
                $this->error['password'] = '请输入密码！';
            }
        }
        if (!empty($password)) {
            if ($password !== $confirm) {
                $this->error['confirm'] = '两次输入密码不正确！';
            }
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

    // ---------------------------------------- 其他 ----------------------------------------
    /**
     * 把指定角色的权限数据添加到权限树数据中
     * @param $all_permissions
     * @param $permissions
     * @return array
     */
    private function _getTreeData($all_permissions, $permissions) {
        $role_permission = array();
        foreach ($all_permissions as $permission) {
            $role_permission[] = array(
                'id' => $permission['id'] + 0,
                'pId' => $permission['parent_id'] + 0,
                'name' => $permission['name'],
                'open' => false,
                'checked' => in_array($permission['id'], $permissions)
            );
        }
        return $role_permission;
    }
}