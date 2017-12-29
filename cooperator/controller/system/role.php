<?php
class ControllerSystemRole extends Controller {
    private $cooperator_id = null;
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');

        // 加载role Model
        $this->load->library('sys_model/rbac', true);
        $this->load->library('sys_model/cooperator', true);
    }

    /**
     * 角色列表
     */
    public function index() {
        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'role_id ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_rbac->getRoleList($condition, $order, $limit);
        $total = $this->sys_model_rbac->getTotalRoles($condition);

        $state = get_setting_boolean();
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['state'] = isset($state[$item['state']]) ? $state[$item['state']] : '';

                $item['edit_action'] = $this->url->link('system/role/edit', 'role_id='.$item['role_id']);
                $item['delete_action'] = $this->url->link('system/role/delete', 'role_id='.$item['role_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('state', $state);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('system/role/add'));
        $this->assign('update_admin_region_action', $this->url->link('system/role/update_admin_region'));

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}';
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->response->setOutput($this->load->view('system/role_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('角色');
        $this->setDataColumn('状态');
        return $this->data_columns;
    }

    /**
     * 添加角色
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('role_name', 'role_permission', 'state'));
            $data = array(
                'cooperator_id' => $this->cooperator_id,
                'type' => 2,
                'role_name' => $input['role_name'],
                'state' => $input['state'] ? 1 : 0
            );
            $role_id = $this->sys_model_rbac->addRole($data);
            if ($role_id) {
                // 添加选中的权限
                $rolePermission = json_decode($input['role_permission'], true);
                if (!empty($rolePermission) && is_array($rolePermission)) {
                    foreach ($rolePermission as $v) {
                        $param = array(
                            'role_id' => $role_id,
                            'permission_id' => $v
                        );
                        $this->sys_model_rbac->addRolePermission($param);
                    }
                }
            }

            $this->session->data['success'] = '添加角色成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：添加角色：' . $data['role_name'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 5,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $filter = $this->request->get(array('role_name', 'login_time', 'role_id', 'state'));

            $this->load->controller('common/base/redirect', $this->url->link('system/role') . '&' . http_build_query($filter));
        }

        $this->assign('title', '角色添加');
        $this->getForm();
    }

    /**
     * 编辑角色
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('role_name', 'role_permission', 'state'));
            $role_id = $this->request->get['role_id'];
            $data = array(
                'role_name' => $input['role_name'],
                'state' => $input['state'] ? 1 : 0
            );
            $condition = array(
                'role_id' => $role_id
            );
            $this->sys_model_rbac->updateRole($condition, $data);

            // 修改角色权限
            // 先删除角色所有权限
            $condition = array(
                'role_id' => $role_id
            );
            $this->sys_model_rbac->deleteRolePermission($condition);
            // 添加选中的权限
            $rolePermission = json_decode($input['role_permission'], true);
            if (!empty($rolePermission) && is_array($rolePermission)) {
                foreach ($rolePermission as $v) {
                    $param = array(
                        'role_id' => $role_id,
                        'permission_id' => $v
                    );
                    $this->sys_model_rbac->addRolePermission($param);
                }
            }

            $this->session->data['success'] = '编辑角色成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：编辑角色：' . $data['role_name'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 5,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $filter = $this->request->get(array('role_name', 'login_time', 'role_id', 'state'));

            $this->load->controller('common/base/redirect', $this->url->link('system/role') . '&' . http_build_query($filter));
        }

        $this->assign('title', '编辑角色');
        $this->getForm();
    }

    /**
     * 删除角色
     */
    public function delete() {
        if (isset($this->request->get['role_id']) && $this->validateDelete()) {
            $role_id = $this->request->get['role_id'];
            $condition = array(
                'role_id' => $role_id
            );
            $this->sys_model_rbac->deleteRole($condition);
            // 先删除角色所有权限
            $condition = array(
                'role_id' => $role_id
            );
            // 删除角色相关权限
            $this->sys_model_rbac->deleteRolePermission($condition);

            $this->session->data['success'] = '删除角色成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：删除角色：' . $role_id,
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 5,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);
        }
        $filter = $this->request->get(array('role_name', 'login_time', 'role_id', 'state'));
        $this->load->controller('common/base/redirect', $this->url->link('system/role') . '&' . http_build_query($filter));
    }

    /**
     * 角色详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $role_id = $this->request->get('role_id');
        $condition = array(
            'role_id' => $role_id
        );
        $info = $this->sys_model_rbac->getroleInfo($condition);
        if (!empty($info)) {
            $state = get_setting_boolean();
            $info['state'] = isset($state[$info['state']]) ? $state[$info['state']] : '';
            $info['login_time'] = !empty($info['login_time']) ? date('Y-m-d H:i:s', $info['login_time']) : '';
            $info['add_time'] = !empty($info['add_time']) ? date('Y-m-d H:i:s', $info['add_time']) : '';
        }

        $this->assign('data', $info);

        $this->response->setOutput($this->load->view('system/role_info', $this->output));
    }

    private function getForm() {
        $this->load->library('sys_model/admin', true);
        // 编辑时获取已有的数据
        $info = $this->request->post(array('role_name', 'role_permission', 'state'));
        $role_id = $this->request->get('role_id');
        // 转换role_permission数据类型
        $permissions = empty($info['role_permission']) ? array() : json_decode($info['role_permission'], true);

        // 编辑时没post数据读取数据库原有的数据
        if (isset($this->request->get['role_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'role_id' => $role_id
            );
            // 角色信息
            $info = $this->sys_model_rbac->getRoleInfo($condition);
            // 已选中的权限
            $rolePermissionList = $this->sys_model_rbac->getRolePermissionList($condition);
            if (!empty($rolePermissionList)) {
                $permissions = array();
                foreach ($rolePermissionList as $v) {
                    $permissions[] = (int)$v['permission_id'];
                }
            }
        }

        // 合伙人信息
        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        $cooperator = $this->sys_model_cooperator->getCooperatorInfo($condition);
        // 合伙人拥有的权限
        $condition = array(
            'admin_id' => $cooperator['admin_id']
        );
        $adminInfo = $this->sys_model_admin->getAdminInfo($condition);

        $condition = array(
            'role_id' => $adminInfo['role_id']
        );
        $rolePermissionList = $this->sys_model_rbac->getRolePermissionList($condition);
        $rootPermissions = array();
        if (!empty($rolePermissionList)) {
            foreach ($rolePermissionList as $v) {
                $rootPermissions[] = (int)$v['permission_id'];
            }
        }
        // 所有权限
        $condition = array(
            'permission_id' => array('in', $rootPermissions)
        );
        $permissionList = $this->sys_model_rbac->getPermissionList($condition);
        // 组成zTree数组
        $selectPermission = json_encode($this->_getPermissionTreeData($permissionList, $permissions));

        $this->assign('role_id', $role_id);
        $this->assign('select_permission', $selectPermission);
        $this->assign('role_permission', json_encode($permissions));
        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&role_id=' . $role_id);
        $this->assign('return_action', $this->url->link('system/role'));
        $this->assign('error', $this->error);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('system/role_form', $this->output));
    }

    // ---------------------------------------- 其他 ----------------------------------------
    /**
     * 把指定角色的权限数据添加到权限树数据中
     * @param $role_id
     * @param $all_permissions
     * @param $permissions
     */
    private function _getPermissionTreeData($all_permissions, $permissions) {
        $role_permission = array();
        foreach ($all_permissions as $permission) {
            $role_permission[] = array(
                'id' => $permission['permission_id'] + 0,
                'pId' => $permission['permission_parent_id'] + 0,
                'name' => $permission['permission_name'],
                'open' => false,
                'checked' => in_array($permission['permission_id'], $permissions)
            );
        }
        return $role_permission;
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('role_name'));

        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
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
}