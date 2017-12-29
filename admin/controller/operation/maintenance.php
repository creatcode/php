<?php
class ControllerOperationMaintenance extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载system_maintenance Model
        $this->load->library('sys_model/system_maintenance', true);
    }

    /**
     * 系统维护日志列表
     */
    public function index() {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_system_maintenance->getSystemMaintenanceList($condition, $order, $limit);
        $total = $this->sys_model_system_maintenance->getTotalSystemMaintenances($condition);
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['start_time'] = isset($item['start_time']) && !empty($item['start_time']) ? date('Y-m-d H:i', $item['start_time']) : '';
                $item['end_time'] = isset($item['end_time']) && !empty($item['end_time']) ? date('Y-m-d H:i', $item['end_time']) : '';
                $item['add_time'] = isset($item['add_time']) && !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['edit_action']   = $this->url->link('operation/maintenance/edit', 'maintenance_id='.$item['maintenance_id']);
                $item['delete_action'] = $this->url->link('operation/maintenance/delete', 'maintenance_id='.$item['maintenance_id']);
                $item['info_action']   = $this->url->link('operation/maintenance/info', 'maintenance_id='.$item['maintenance_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);
        $this->assign('add_action', $this->url->link('operation/maintenance/add'));
        $this->assign('return_action', $this->url->link('operation/maintenance'));

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

        $this->response->setOutput($this->load->view('operation/maintenance_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('开始时间');
        $this->setDataColumn('结束时间');
        $this->setDataColumn('维护内容');
        $this->setDataColumn('添加时间');
        return $this->data_columns;
    }

    /**
     * 添加系统维护日志
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('start_time', 'end_time', 'content'));
            $now = time();
            $data = array(
                'start_time' => strtotime($input['start_time']),
                'end_time' => strtotime($input['end_time']),
                'content' => $input['content'],
                'add_time' => $now
            );

            $maintenance_id = $this->sys_model_system_maintenance->addSystemMaintenance($data);

            // 同步维护信息
            $maintenanceData = array(
                'id' => $maintenance_id,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
            );
            $this->syncMaintenanceInfo($maintenanceData);

            $this->session->data['success'] = '添加系统维护日志成功！';
            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '添加系统维护日志，维护时间' . date('Y-m-d H:i', $data['start_time']) . '-'. date('Y-m-d H:i', $data['end_time']));
            
            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('operation/maintenance', $filter, true));
        }

        $this->assign('title', '系统维护日志添加');
        $this->getForm();
    }

    /**
     * 编辑系统维护日志
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('start_time', 'end_time', 'content'));
            $maintenance_id = $this->request->get['maintenance_id'];
            $data = array(
                'start_time' => strtotime($input['start_time']),
                'end_time' => strtotime($input['end_time']),
                'content' => $input['content']
            );
            $condition = array(
                'maintenance_id' => $maintenance_id
            );
            $this->sys_model_system_maintenance->updateSystemMaintenance($condition, $data);

            // 同步维护信息
            $maintenanceData = array(
                'id' => $maintenance_id,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
            );
            $this->syncMaintenanceInfo($maintenanceData);

            $this->session->data['success'] = '编辑系统维护日志成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '编辑系统维护日志，维护时间' . date('Y-m-d H:i:s', $data['start_time']) . '-'. date('Y-m-d H:i:s', $data['end_time']));

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('operation/maintenance', $filter, true));
        }

        $this->assign('title', '编辑系统维护日志');
        $this->getForm();
    }

    /**
     * 删除系统维护日志
     */
    public function delete() {
        if (isset($this->request->get['maintenance_id']) && $this->validateDelete()) {
            $condition = array(
                'maintenance_id' => $this->request->get['maintenance_id']
            );
            $this->sys_model_system_maintenance->deleteSystemMaintenance($condition);

            // 同步维护信息
            $maintenanceData = array(
                'id' => $this->request->get['maintenance_id'],
            );
            $this->syncMaintenanceInfo($maintenanceData);
            $this->session->data['success'] = '删除系统维护日志成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '删除系统维护日志：' . $this->request->get['maintenance_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('operation/maintenance', $filter, true));
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('start_time', 'end_time', 'content'));
        $maintenance_id = $this->request->get('maintenance_id');
        if (isset($this->request->get['maintenance_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'maintenance_id' => $this->request->get['maintenance_id']
            );
            $info = $this->sys_model_system_maintenance->getSystemMaintenanceInfo($condition);
			$info['start_time'] = isset($info['start_time']) && !empty($info['start_time']) ? date('Y-m-d H:i', $info['start_time']) : '';
			$info['end_time'] = isset($info['end_time']) && !empty($info['end_time']) ? date('Y-m-d H:i', $info['end_time']) : '';
        }

        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&maintenance_id=' . $maintenance_id);
        $this->assign('return_action', $this->url->link('operation/maintenance'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('operation/maintenance_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('start_time', 'end_time', 'content'));

        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }
        if (strtotime($input['start_time']) >= strtotime($input['end_time'])) {
            $this->error['start_time'] = '开始时间不能少于等于结束时间';
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

    private function syncMaintenanceInfo($data) {
        $uri = 'http://backup.s-bike.cn/maintenance/api.php?' . http_build_query($data);
        $curlObj = new \Tool\Curl($uri);
        return $curlObj->getData();
    }
}