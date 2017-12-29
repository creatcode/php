<?php
class ControllerLockLock extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载Lock Model
        $this->load->library('sys_model/lock', true);
        $this->load->library('sys_model/bicycle', true);
    }

    /**
     * 锁列表
     */
    public function index() {
        $filter = $this->request->get(array('lock_sn', 'lock_name', 'cooperator_name', 'battery', 'system_time', 'open_nums', 'lock_status'));

        $condition = array();
        if (!empty($filter['lock_sn'])) {
            $condition['lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (!empty($filter['lock_name'])) {
            $condition['lock_name'] = array('like', "%{$filter['lock_name']}%");
        }
        if (!empty($filter['cooperator_name'])) {
            $condition['cooperator_name'] = array('like', "%{$filter['cooperator_name']}%");
        }
        if (is_numeric($filter['battery'])) {
            $condition['battery'] = (int)$filter['battery'];
        }
        if (!empty($filter['system_time'])) {
            $system_time = explode(' 至 ', $filter['system_time']);
            $condition['system_time'] = array(
                array('egt', strtotime($system_time[0])),
                array('elt', bcadd(86399, strtotime($system_time[1])))
            );
        }
        if (is_numeric($filter['open_nums'])) {
            $condition['open_nums'] = (int)$filter['open_nums'];
        }
        if (is_numeric($filter['lock_status'])) {
            $condition['lock_status'] = (int)$filter['lock_status'];
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = array();

        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $field = 'l.*,cooperator.cooperator_name';

        $join = array(
            'cooperator' => 'cooperator.cooperator_id=l.cooperator_id'
        );

        $result = $this->sys_model_lock->getLockList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_lock->getTotalLocks($condition, $join);

        $lock_status = get_lock_status();
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['system_time'] = $item['system_time'] == 0 ? '没有更新过' : date('Y-m-d H:i:s', $item['system_time']);
                $item['lock_status'] = isset($lock_status[$item['lock_status']]) ? $lock_status[$item['lock_status']] : '';
                $item['battery'] = $item['battery'] > 0 ? abs($item['battery']) . '（正在充电）' : abs($item['battery']);

                $item['edit_action'] = $this->url->link('lock/lock/edit', 'lock_id='.$item['lock_id']);
                $item['delete_action'] = $this->url->link('lock/lock/delete', 'lock_id='.$item['lock_id']);
                $item['info_action'] = $this->url->link('lock/lock/info', 'lock_id='.$item['lock_id']);
            }
        }

        $filter_types = array(
            'lock_sn' => '车锁编号',
            'lock_name' => '车锁名称',
            'cooperator_name' => '合伙人'
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        // 加载Lock Model
        $this->load->library('sys_model/bicycle', true);
        // 所有单车数
        $condition = array();
        $total_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);
        // 使用中单车数
        $condition = array(
            'is_using' => 1
        );
        $using_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);
        // 故障单车数
        $condition = array(
            'fault' => 1
        );
        $fault_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('total_bicycle', $total_bicycle);
        $this->assign('using_bicycle', $using_bicycle);
        $this->assign('fault_bicycle', $fault_bicycle);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('lock_status', $lock_status);
        $this->assign('action', $this->cur_url);
        $this->assign('import_action', $this->url->link('lock/lock/import'));
        $this->assign('add_action', $this->url->link('lock/lock/add'));
        $this->assign('export_action', $this->url->link('lock/lock/export'));
        $this->assign('bicycle_action', $this->url->link('bicycle/bicycle'));

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

        $this->response->setOutput($this->load->view('lock/lock_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('锁编号');
        $this->setDataColumn('锁名称');
        $this->setDataColumn('合伙人');
        $this->setDataColumn('当前电量（百分比）');
        $this->setDataColumn('开锁次数');
        $this->setDataColumn('更新时间');
        $this->setDataColumn('状态');
        return $this->data_columns;
    }

    /**
     * 添加锁
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('lock_sn', 'lock_name', 'automatch','lock_type','cooperator_id','lock_platform','lock_factory','batch_num'));
            $data = array(
                'lock_sn' => $input['lock_sn'],
                'lock_name' => $input['lock_name'],
                'lock_type' => $input['lock_type'],
                'cooperator_id' => $input['cooperator_id'],
                'lock_platform' => $input['lock_platform'],
                'lock_factory' => $input['lock_factory'],
                'batch_num' => $input['batch_num'],
            );
            $lock_id = $this->sys_model_lock->addLock($data);
            // 自动匹配单车
            if ($input['automatch']) {
                // 获取空余单车
                $condition = array(
                    'lock_sn' => ''
                );
                $bicycle = $this->sys_model_bicycle->getBicycleInfo($condition);

                // 绑定空余单车
                if (is_array($bicycle) && !empty($bicycle)) {
                    $condition = array(
                        'bicycle_id' => $bicycle['bicycle_id']
                    );
                    $data = array(
                        'lock_sn' => $input['lock_sn']
                    );
                    $this->sys_model_bicycle->updateBicycle($condition, $data);
                }
            }

            $this->session->data['success'] = '添加锁成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加锁：' . $data['lock_sn'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 1,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);
            
            $filter = $this->request->get(array('lock_sn', 'lock_name', 'battery', 'system_time', 'open_nums', 'lock_status'));

            $this->load->controller('common/base/redirect', $this->url->link('lock/lock', $filter, true));
        }

        $this->assign('title', '锁添加');
        $this->assign('showAutomatch', true);
        $this->getForm();
    }

    /**
     * 编辑锁
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('lock_name','lock_type','cooperator_id','lock_platform','lock_factory','batch_num'));
            $lock_id = $this->request->get['lock_id'];
            $data = array(
                'lock_name' => $input['lock_name'],
                'lock_type' => $input['lock_type'],
                'cooperator_id' => $input['cooperator_id'],
                'lock_platform' => $input['lock_platform'],
                'lock_factory' => $input['lock_factory'],
                'batch_num' => $input['batch_num']
            );
            $condition = array(
                'lock_id' => $lock_id
            );
            $this->sys_model_lock->updateLock($condition, $data);

            $this->session->data['success'] = '编辑锁成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '编辑锁：' . $lock_id,
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 1,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $filter = $this->request->get(array('lock_sn', 'lock_name', 'battery', 'system_time', 'open_nums', 'lock_status'));

            $this->load->controller('common/base/redirect', $this->url->link('lock/lock', $filter, true));
        }

        $this->assign('title', '编辑锁');
        $this->assign('showAutomatch', false);
        $this->getForm();
    }

    /**
     * 删除锁
     */
    public function delete() {
        if (isset($this->request->get['lock_id']) && $this->validateDelete()) {
            $condition = array(
                'lock_id' => $this->request->get['lock_id']
            );
            $this->sys_model_lock->deleteLock($condition);

            $this->session->data['success'] = '删除锁成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '删除锁：' . $this->request->get['lock_id'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 1,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);
        }
        $filter = $this->request->get(array('lock_sn', 'lock_name', 'battery', 'system_time', 'open_nums', 'lock_status'));
        $this->load->controller('common/base/redirect', $this->url->link('lock/lock', $filter, true));
    }

    /**
     * 锁详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $lock_id = $this->request->get('lock_id');
        $condition = array(
            'lock_id' => $lock_id
        );
        $info = $this->sys_model_lock->getLockInfo($condition);
        if (!empty($info)) {
            $lock_status = get_lock_status();
            $info['system_time'] = $info['system_time'] == 0 ? '没有更新过' : date('Y-m-d H:i:s', $info['system_time']);
            $info['lock_status'] = isset($lock_status[$info['lock_status']]) ? $lock_status[$info['lock_status']] : '';
            $info['gx'] = $info['gx'] * 0.01;
            $info['gy'] = $info['gy'] * 0.01;
            $info['gz'] = $info['gz'] * 0.01;
        }

        $this->assign('data', $info);

        $this->response->setOutput($this->load->view('lock/lock_info', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
//        library('PHPExcel/IOFactory');
        require_once DIR_SYSTEM . "library/PHPExcel/IOFactory.php";
        $filter = $this->request->post(array('lock_sn', 'lock_name', 'cooperator_name', 'battery', 'system_time', 'open_nums', 'lock_status'));

        $condition = array();
        if (!empty($filter['lock_sn'])) {
            $condition['lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (!empty($filter['lock_name'])) {
            $condition['lock_name'] = array('like', "%{$filter['lock_name']}%");
        }
        if (!empty($filter['cooperator_name'])) {
            $condition['cooperator_name'] = array('like', "%{$filter['cooperator_name']}%");
        }
        if (is_numeric($filter['battery'])) {
            $condition['battery'] = (int)$filter['battery'];
        }
        if (!empty($filter['system_time'])) {
            $system_time = explode(' 至 ', $filter['system_time']);
            $condition['system_time'] = array(
                array('egt', strtotime($system_time[0])),
                array('elt', bcadd(86399, strtotime($system_time[1])))
            );
        }
        if (is_numeric($filter['open_nums'])) {
            $condition['open_nums'] = (int)$filter['open_nums'];
        }
        if (is_numeric($filter['lock_status'])) {
            $condition['lock_status'] = (int)$filter['lock_status'];
        }
        $field = 'l.*,cooperator.cooperator_name';
        $order = '';
        $limit = '';
        $join = array(
            'cooperator' => 'cooperator.cooperator_id=l.cooperator_id'
        );
        $result = $this->sys_model_lock->getLockList($condition, $order, $limit, $field, $join);

        $list = array();
        if (is_array($result) && !empty($result)) {
            $lock_status = get_lock_status();
            foreach ($result as $item) {
                $list[] = array(
                    'lock_sn' => $item['lock_sn'],
                    'lock_name' => $item['lock_name'],
                    'cooperator_name' => $item['cooperator_name'],
                    'gy' => $item['gy'],
                    'open_nums' => $item['open_nums'],
                    'system_time' => $item['system_time'] == 0 ? '没有更新过' : date('Y-m-d H:i:s', $item['system_time']),
                    'lock_status' => isset($lock_status[$item['lock_status']]) ? $lock_status[$item['lock_status']] : ''
                );
            }
        }

        $data = array(
            'title' => '车锁列表',
            'header' => array(
                'lock_sn' => '锁编号',
                'lock_name' => '锁名称',
                'cooperator_name' => '合伙人',
                'gy' => '当前电量（百分比）',
                'open_nums' => '开锁次数',
                'system_time' => '更新时间',
                'lock_status' => '状态'
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 导入
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
                        'lock_sn' => isset($excelData[$i][0]) ? $excelData[$i][0] : '',
                        'lock_name' => isset($excelData[$i][1]) ? $excelData[$i][1] : ''
                    );
                    $lock_id = $this->sys_model_lock->addLock($data);
                    // 自动匹配单车
                    $condition = array(
                        'lock_sn' => ''
                    );
                    $field = '*';
                    $order = 'bicycle_id asc';
                    $bicycle = $this->sys_model_bicycle->getBicycleInfo($condition, $field, $order);

                    // 绑定空余单车
                    if (is_array($bicycle) && !empty($bicycle)) {
                        $condition = array(
                            'bicycle_id' => $bicycle['bicycle_id']
                        );
                        $data = array(
                            'lock_sn' => $data['lock_sn']
                        );
                        $this->sys_model_bicycle->updateBicycle($condition, $data);
                    }
                }
            }
        }
        $this->response->showSuccessResult('', '导入成功');
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('lock_sn', 'lock_name', 'bicycle_sn','lock_factory','lock_platform','cooperator_id','batch_num','lock_type'));
        $lock_id = $this->request->get('lock_id');
        if (isset($this->request->get['lock_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'lock_id' => $this->request->get['lock_id']
            );
            $info = $this->sys_model_lock->getLockInfo($condition);
            $condition = array(
                'lock_id' => $this->request->get['lock_id']
            );
            $bicycle = $this->sys_model_bicycle->getBicycleInfo($condition);
            $info['bicycle_sn'] = $bicycle['bicycle_sn'];
        }

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }
        $this->load->library('sys_model/cooperator', true);
        $cooperators = $this->sys_model_cooperator->getCooperatorList();

        $this->assign('lock_id', $lock_id);
        $this->assign('data', $info);
        $this->assign('lock_status', get_lock_status());
        $this->assign('cooperators', $cooperators);
        $this->assign('action', $this->cur_url . '&lock_id=' . $lock_id);
        $this->assign('return_action', $this->url->link('lock/lock'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('lock/lock_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {

        $input = $this->request->post(array('lock_name','lock_type','lock_factory','batch_num'));
        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        if(!$this->request->post('edit_lock_sn')){
            $lock_sn = $this->request->post('lock_sn');
            $condition = array(
                'lock_sn' => $lock_sn
            );
            $had_lock_sn = $this->sys_model_lock->getLockInfo($condition);
            if($had_lock_sn){
                $this->error['lock_sn'] = '锁sn已经存在！';
            }
        }

        $lock_platform = $this->request->post('lock_platform');

        $cooperator_id = $this->request->post('cooperator_id');


        if (!is_numeric($cooperator_id)) {
            $this->error['cooperator_id'] = '请输入完整！';
        }

        if (!is_numeric($lock_platform)) {
            $this->error['lock_platform'] = '请输入完整！';
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
