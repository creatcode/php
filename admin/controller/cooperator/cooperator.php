<?php
class ControllerCooperatorCooperator extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载cooperator Model
        $this->load->library('sys_model/cooperator', true);
        $this->load->library('sys_model/region', true);
    }

    /**
     * 合伙人列表
     */
    public function index() {
        $filter = $this->request->get(array('cooperator_name', 'state'));

        $regions = $allRegions = array();
        $regionList = $this->sys_model_region->getRegionList();
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

        $condition = array();
        if (!empty($filter['cooperator_name'])) {
            $condition['cooperator_name'] = array('like', "%{$filter['cooperator_name']}%");
        }
        if (is_numeric($filter['state'])) {
            $condition['state'] = (int)$filter['state'];
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

        $result = $this->sys_model_cooperator->getCooperatorList($condition, $order, $limit);
        $total = $this->sys_model_cooperator->getTotalCooperators($condition);

        $state = get_cooperator_state();
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $selectedRegions = array();
                $condition = array(
                    'cooperator_id' => $item['cooperator_id']
                );
                $cooperatorRegions = $this->sys_model_region->getCooperatorToRegionList($condition);
                foreach ($cooperatorRegions as $val) {
                    $selectedRegions[] = $val['region_id'];
                }
                $regions[$item['cooperator_id']] = $this->_getTreeData($allRegions, $selectedRegions);
                $item['regions_num'] = count($selectedRegions);
                $item['state'] = isset($state[$item['state']]) ? $state[$item['state']] : '';

                $item['edit_action'] = $this->url->link('cooperator/cooperator/edit', 'cooperator_id='.$item['cooperator_id']);
                $item['delete_action'] = $this->url->link('cooperator/cooperator/delete', 'cooperator_id='.$item['cooperator_id']);
                $item['info_action'] = $this->url->link('cooperator/cooperator/info', 'cooperator_id='.$item['cooperator_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $regions = json_encode($regions);
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('state', $state);
        $this->assign('static', HTTP_CATALOG);
        $this->assign('filter', $filter);
        $this->assign('regions', $regions);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('cooperator/cooperator/add'));
        $this->assign('update_cooperator_region_action', $this->url->link('cooperator/cooperator/update_cooperator_region'));

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

        $this->assign('import_action', $this->url->link('cooperator/cooperator/import'));
        $this->assign('export_action', $this->url->link('cooperator/cooperator/export'));

        $this->response->setOutput($this->load->view('cooperator/cooperator_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('合伙人');
        $this->setDataColumn('区域管辖');
        $this->setDataColumn('状态');
        return $this->data_columns;
    }

    /**
     * 添加合伙人
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('cooperator_name', 'mobile', 'admin_name', 'password', 'role_id', 'region', 'state'));
            $now = time();
            $data = array(
                'cooperator_name' => $input['cooperator_name'],
                'mobile' => $input['mobile'],
                'state' => (int)$input['state'],
                'add_time' => $now
            );
            $cooperator_id = $this->sys_model_cooperator->addCooperator($data);
            if ($cooperator_id) {
                // 合伙人管辖区域
                if (!empty($input['region']) && is_array($input['region'])) {
                    foreach ($input['region'] as $region_id) {
                        $data = array(
                            'cooperator_id' => $cooperator_id,
                            'region_id' => $region_id
                        );
                        $this->sys_model_region->addCooperatorToRegion($data);
                    }
                }

                // 操作员
                $data = array(
                    'role_id' => $input['role_id'],
                    'type' => 2,
                    'cooperator_id' => $cooperator_id,
                    'admin_name' => $input['admin_name'],
                    'password' => $input['password'],
                    'state' => 1,
                    'add_time' => $now
                );
                $admin_id = $this->logic_admin->add($data);

                // 更新合伙人管理
                $condition = array(
                    'cooperator_id' => $cooperator_id
                );
                $data = array(
                    'admin_id' => $admin_id,
                    'admin_name' => $input['admin_name']
                );
                $this->sys_model_cooperator->updateCooperator($condition, $data);
            }

            $this->session->data['success'] = '添加合伙人成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加合伙人：' . $input['cooperator_name'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 6,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $filter = $this->request->get(array('cooperator_name', 'login_time', 'login_ip', 'state'));

            $this->load->controller('common/base/redirect', $this->url->link('cooperator/cooperator', $filter, true));
        }

        $this->assign('title', '合伙人添加');
        $this->getForm();
    }

    /**
     * 编辑合伙人
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('password', 'region', 'state'));
            $cooperator_id = $this->request->get['cooperator_id'];
            $data = array(
                'state' => $input['state']
            );
            $condition = array(
                'cooperator_id' => $cooperator_id
            );
            $this->sys_model_cooperator->updateCooperator($condition, $data);

            // 清空合伙人所有的区域，重新绑定区域
            $condition = array(
                'cooperator_id' => $cooperator_id
            );
            $this->sys_model_region->deleteCooperatorToRegion($condition);
            if (!empty($input['region']) && is_array($input['region'])) {
                foreach ($input['region'] as $region_id) {
                    $data = array(
                        'cooperator_id' => $cooperator_id,
                        'region_id' => $region_id
                    );
                    $this->sys_model_region->addCooperatorToRegion($data);
                }
            }

            $this->session->data['success'] = '编辑合伙人成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '编辑合伙人：' . $cooperator_id,
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 6,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $filter = $this->request->get(array('cooperator_name', 'login_time', 'login_ip', 'state'));

            $this->load->controller('common/base/redirect', $this->url->link('cooperator/cooperator', $filter, true));
        }

        $this->assign('title', '编辑合伙人');
        $this->getForm();
    }

    /**
     * 删除合伙人
     */
    public function delete() {
        if (isset($this->request->get['cooperator_id']) && $this->validateDelete()) {
            $condition = array(
                'cooperator_id' => $this->request->get['cooperator_id']
            );
            $this->sys_model_cooperator->deleteCooperator($condition);

            $this->session->data['success'] = '删除合伙人成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '删除合伙人：' . $this->request->get['cooperator_id'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 6,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

        }
        $filter = $this->request->get(array('cooperator_name', 'login_time', 'login_ip', 'state'));
        $this->load->controller('common/base/redirect', $this->url->link('cooperator/cooperator', $filter, true));
    }

    /**
     * 合伙人详情
     */
    public function info() {
        $this->load->library('sys_model/admin', true);
        // 编辑时获取已有的数据
        $cooperator_id = $this->request->get('cooperator_id');
        $condition = array(
            'cooperator_id' => $cooperator_id
        );
        $info = $this->sys_model_cooperator->getCooperatorInfo($condition);
        if (!empty($info)) {
            $state = get_cooperator_state();
            $info['state'] = isset($state[$info['state']]) ? $state[$info['state']] : '';
            $info['login_time'] = !empty($info['login_time']) ? date('Y-m-d H:i:s', $info['login_time']) : '';
            $info['add_time'] = !empty($info['add_time']) ? date('Y-m-d H:i:s', $info['add_time']) : '';
        }

        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('cooperator/cooperator'));
        $this->assign('region_action', $this->url->link('cooperator/region', 'cooperator_id='.$cooperator_id));
        $this->assign('account_action', $this->url->link('cooperator/account', 'cooperator_id='.$cooperator_id));
        $this->assign('role_action', $this->url->link('cooperator/role', 'cooperator_id='.$cooperator_id));
        $this->response->setOutput($this->load->view('cooperator/cooperator_info', $this->output));
    }

    /**
     * 更改合伙人管辖的区域
     */
    public function update_cooperator_region() {
        $input = $this->request->post(array('cooperator_id', 'regions'));

        // 删除原有的区域
        $condition = array(
            'cooperator_id' => $input['cooperator_id']
        );
        $this->sys_model_region->deleteCooperatorToRegion($condition);

        // 重新绑定区域
        if (!empty($input['regions'])) {
            $regions = explode(',', $input['regions']);
            if (is_array($regions) && !empty($regions)) {
                foreach ($regions as $region_id) {
                    $data = array(
                        'cooperator_id' => $input['cooperator_id'],
                        'region_id' => $region_id
                    );
                    $this->sys_model_region->addCooperatorToRegion($data);
                }
            }
        }
        $this->response->showSuccessResult('', '修改成功');
    }

    /**
     * 导入合伙人
     */
    public function import() {
        // 获取上传EXCEL文件数据
        $excelData = $this->load->controller('common/base/importExcel');

        if (is_array($excelData) && !empty($excelData)) {
            $count = count($excelData);
            // 从第3行开始
            if ($count >= 3) {
                for ($i = 3; $i <= $count; $i++) {
                    $data = array(
                        'bicycle_sn' => isset($excelData[$i][0]) ? $excelData[$i][0] : '',
                        'type' => 1,
                        'lock_sn' => isset($excelData[$i][1]) ? $excelData[$i][1] : '',
                        'add_time' => TIMESTAMP
                    );
                    $this->sys_model_bicycle->addBicycle($data);
                }
            }
        }

        $this->response->showSuccessResult('', '导入成功');
    }

    /**
     * 导出合伙人
     */
    public function export() {
        $bicycle_ids = $this->request->post("selected");

        $condition = array(
            'bicycle_id' => array('in', $bicycle_ids)
        );
        $order = 'bicycle.add_time DESC';
        $limit = '';
        $field = 'bicycle.*,cooperator.cooperator_name';

        $join = array(
            'cooperator' => 'cooperator.cooperator_id=bicycle.cooperator_id'
        );
        $bicycles = $this->sys_model_bicycle->getBicycleList($condition, $order, $limit, $field, $join);
        $list = array();
        if (is_array($bicycles) && !empty($bicycles)) {
            $bicycle_types = get_bicycle_type();
            $use_states = get_common_boolean();
            foreach ($bicycles as $bicycle) {
                $list[] = array(
                    'bicycle_sn' => $bicycle['bicycle_sn'],
                    'lock_sn' => $bicycle['lock_sn'],
                    'type' => $bicycle_types[$bicycle['type']],
                    'region_name' => $bicycle['region_name'],
                    'cooperator_name' => $bicycle['cooperator_name'],
                    'is_using' => $use_states[$bicycle['is_using']]
                );
            }
        }

        $data = array(
            'title' => '单车列表',
            'header' => array(
                'bicycle_sn' => '单车编号',
                'lock_sn' => '车锁编号',
                'type' => '单车类型',
                'region_name' => '区域',
                'cooperator_name' => '合伙人',
                'is_using' => '是否使用中',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('cooperator_name', 'mobile', 'admin_name', 'password', 'role_id', 'confirm', 'region', 'state'));
        $cooperator_id = $this->request->get('cooperator_id');
        if (isset($this->request->get['cooperator_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'cooperator_id' => $this->request->get['cooperator_id']
            );
            $info = $this->sys_model_cooperator->getCooperatorInfo($condition);

        }
        if (empty($inf['role_id'])) {
            $info['role_id'] = '8';
        }

        if (empty($info['region'])) {
            $info['region'] = array();
            //  编辑时读取已选的区域
            if (isset($this->request->get['cooperator_id'])) {
                $condition = array(
                    'cooperator_id' => $this->request->get['cooperator_id']
                );
                $cooperatorRegion = $this->sys_model_region->getCooperatorToRegionList($condition);
                if (is_array($cooperatorRegion) && !empty($cooperatorRegion)) {
                    foreach ($cooperatorRegion as $val) {
                        $info['region'][] = $val['region_id'];
                    }
                }
            }
        }

        // 加载区域 model
        $this->load->library('sys_model/rbac', true);
        $condition = array(
            'cooperator_id' => 0
        );
        $roles = $this->sys_model_rbac->getRoleList($condition);

        $regions = $this->sys_model_region->getRegionList();
        $this->assign('cooperator_id', $cooperator_id);
        $this->assign('data', $info);
        $this->assign('roles', $roles);
        $this->assign('regions', $regions);
        $this->assign('return_action', $this->url->link('cooperator/cooperator'));
        $this->assign('action', $this->cur_url . '&cooperator_id=' . $cooperator_id);
        $this->assign('error', $this->error);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('cooperator/cooperator_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        // 添加合伙人时判断必填参数
        if ($this->request->get('route') == 'cooperator/cooperator/add') {
            $input = $this->request->post(array('cooperator_name', 'mobile', 'admin_name', 'password', 'role_id', 'region'));
            foreach ($input as $k => $v) {
                if (empty($v)) {
                    $this->error[$k] = '请完善此项！';
                }
            }
            $this->load->library('sys_model/admin', true);
            // 管理员账号是否已存在
            $condition = array(
                'admin_name' => $input['admin_name']
            );
            $total = $this->sys_model_admin->getTotalAdmins($condition);
            if ($total) {
                $this->error['admin_name'] = '管理员账号已存在！';
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