<?php
class ControllerOperationFault extends Controller {
    private $cooperator_id = null;
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');

        // 加载fault Model
        $this->load->library('sys_model/fault', true);
    }

    /**
     * 故障记录列表
     */
    public function index() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //AJAX请求
            if ($this->request->get('method') == 'json') {
                $this->apiIndex();
                return;
            }
        }

        $filter = $this->request->get(array('filter_type', 'bicycle_sn', 'lock_sn', 'fault_type', 'user_name', 'add_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['lock_sn'])) {
            $condition['lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (is_numeric($filter['fault_type'])) {
            $condition['_string'] = 'find_in_set(' . (int)$filter['fault_type'] . ', fault_type)';
        }
        if (!empty($filter['user_name'])) {
            $condition['user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('gt', strtotime($add_time[0])),
                array('lt', bcadd(86399, strtotime($add_time[1])))
            );
        }

        $filter_types = array(
            'bicycle_sn' => '单车编号',
            'lock_sn' => '车锁编号',
            'user_name' => '用户名'
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
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

        $result = $this->sys_model_fault->getFaultList($condition, $order, $limit);
        $total = $this->sys_model_fault->getTotalFaults($condition);

        $condition = array(
          'is_show' => 1
        );
        $order = 'display_order ASC, add_time DESC';
        $tempFaultTypes = $this->sys_model_fault->getFaultTypeList($condition, $order);
        $fault_types = array();
        if (!empty($tempFaultTypes)) {
            foreach ($tempFaultTypes as $v) {
                $fault_types[$v['fault_type_id']] = $v['fault_type_name'];
            }
        }

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $fault_type = '';
                $fault_type_ids = explode(',', $item['fault_type']);
                foreach($fault_type_ids as $fault_type_id) {
                    $fault_type .= isset($fault_types[$fault_type_id]) ? ',' . $fault_types[$fault_type_id] : '';
                }
                $item['fault_type'] = !empty($fault_type) ? substr($fault_type, 1) : '';

                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['edit_action'] = $this->url->link('operation/fault/edit', 'fault_id='.$item['fault_id']);
                $item['delete_action'] = $this->url->link('operation/fault/delete', 'fault_id='.$item['fault_id']);
                $item['info_action'] = $this->url->link('operation/fault/info', 'fault_id='.$item['fault_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('fault_types', $fault_types);
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('return_action', $this->url->link('operation/fault'));
        $this->assign('add_action', $this->url->link('operation/fault/add'));

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

        $this->assign('export_action', $this->url->link('operation/fault/export'));

        $this->response->setOutput($this->load->view('operation/fault_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('单车编号');
        $this->setDataColumn('锁编号');
        $this->setDataColumn('故障类型');
        $this->setDataColumn('用户名');
        $this->setDataColumn('上报时间');
        return $this->data_columns;
    }

    /**
     * index AJAX请求
     */
    protected function apiIndex() {
        if (isset($this->request->post['page'])) {
            $page = (int)$this->request->post['page'];
        } else {
            $page = 1;
        }

        $condition = array(
            'cooperator_id' => $this->cooperator_id,
            'processed' => 0
        );
        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_fault->getFaultList($condition, $order, $limit);
        $total = $this->sys_model_fault->getTotalFaults($condition);

        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'bicycle_sn' => $v['bicycle_sn'],
                    'add_time' => date('Y-m-d H:i:s', $v['add_time']),
                    'uri' => $this->url->link('admin/index') . '#' . $v['bicycle_sn']
                );
            }
        }
        
        $statisticsMessages = $this->load->controller('common/base/statisticsMessages', $condition);

        $data = array(
            'title' => array(
                'bicycle_sn' => '单车编号',
                'add_time' => '上报时间'
            ),
            'list' => $list,
            'page' => $page,
            'total' => ceil($total / $rows),
            'statistics' => $statisticsMessages
        );

        $this->response->showSuccessResult($data, '获取成功');
    }

    /**
     * 处理故障
     */
    public function handling(){
        if($this->request->server['REQUEST_METHOD'] == 'POST'){
            $input = $this->request->post(array('fault_id', 'content'));

            foreach ($input as $v){
                if(empty($v)){
                    $this->response->showErrorResult('提交数据不完整');
                    exit;
                }
            }

            $where = array(
                'fault_id'=> $input['fault_id']
            );

            $data = array(
                'content'=> $input['content'],
                'handling_time'=> time(),
                'handler'=> $this->logic_admin->getId(),
                'processed'=> 1,
            );

            $info = $this->sys_model_fault->getFaultInfo($where);

            if($info['processed'] == 1){
                $this->response->showErrorResult('请不要重复提交');
                exit;
            }
            if($this->sys_model_fault->updateFault($where, $data)){
                $this->response->showSuccessResult('操作成功');
            };
        }
    }

    /**
     * 删除故障记录
     */
    public function delete() {
        if (isset($this->request->get['fault_id']) && $this->validateDelete()) {
            $condition = array(
                'fault_id' => $this->request->get['fault_id'],
                'cooperator_id' => $this->cooperator_id
            );
            $this->sys_model_fault->deleteFault($condition);

            $this->session->data['success'] = '删除故障记录成功！';
        }
        $filter = $this->request->get(array('filter_type', 'fault_sn', 'type', 'lock_sn', 'is_using'));
        $this->load->controller('common/base/redirect', $this->url->link('operation/fault', $filter, true));
    }

    /**
     * 故障记录详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $fault_id = $this->request->get('fault_id');
        $condition = array(
            'fault_id' => $fault_id,
            'cooperator_id' => $this->cooperator_id
        );
        $info = $this->sys_model_fault->getFaultInfo($condition);
        if (!empty($info)) {
            $condition = array(
                'is_show' => 1
            );
            $order = 'display_order ASC, add_time DESC';
            $tempFaultTypes = $this->sys_model_fault->getFaultTypeList($condition, $order);
            $fault_types = array();
            if (!empty($tempFaultTypes)) {
                foreach ($tempFaultTypes as $v) {
                    $fault_types[$v['fault_type_id']] = $v['fault_type_name'];
                }
            }
            $fault_type = '';
            $fault_type_ids = explode(',', $info['fault_type']);
            foreach($fault_type_ids as $fault_type_id) {
                $fault_type .= isset($fault_types[$fault_type_id]) ? ',' . $fault_types[$fault_type_id] : '';
            }
            $info['fault_type'] = !empty($fault_type) ? substr($fault_type, 1) : '';

            $info['add_time'] = !empty($info['add_time']) ? date('Y-m-d H:i:s', $info['add_time']) : '';
        }

        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('operation/fault'));

        $this->response->setOutput($this->load->view('operation/fault_info', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('filter_type', 'bicycle_sn', 'lock_sn', 'fault_type', 'user_name', 'add_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['lock_sn'])) {
            $condition['lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (is_numeric($filter['fault_type'])) {
            $condition['_string'] = 'find_in_set(' . (int)$filter['fault_type'] . ', fault_type)';
        }
        if (!empty($filter['user_name'])) {
            $condition['user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('gt', strtotime($add_time[0])),
                array('lt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        $order = 'add_time DESC';
        $faults = $this->sys_model_fault->getFaultList($condition, $order);

        $condition = array(
            'is_show' => 1
        );
        $order = 'display_order ASC, add_time DESC';
        $tempFaultTypes = $this->sys_model_fault->getFaultTypeList($condition, $order);

        $fault_types = array();
        if (!empty($tempFaultTypes)) {
            foreach ($tempFaultTypes as $v) {
                $fault_types[$v['fault_type_id']] = $v['fault_type_name'];
            }
        }

        $list = array();
        if (is_array($faults) && !empty($faults)) {
            foreach ($faults as $fault) {
                $fault_type = '';
                $fault_type_ids = explode(',', $fault['fault_type']);
                foreach($fault_type_ids as $fault_type_id) {
                    $fault_type .= isset($fault_types[$fault_type_id]) ? ',' . $fault_types[$fault_type_id] : '';
                }

                $list[] = array(
                    'bicycle_sn' => $fault['bicycle_sn'],
                    'lock_sn' => $fault['lock_sn'],
                    'fault_type' => !empty($fault_type) ? substr($fault_type, 1) : '',
                    'user_name' => $fault['user_name'],
                    'add_time' => !empty($fault['add_time']) ? date('Y-m-d H:i:s', $fault['add_time']) : '',
                );
            }
        }

        $data = array(
            'title' => '故障记录列表',
            'header' => array(
                'bicycle_sn' => '单车编号',
                'lock_sn' => '锁编号',
                'fault_type' => '	故障类型',
                'user_name' => '用户名',
                'add_time' => '上报时间',
            ),
            'list' => $list
        );

        $this->load->controller('common/base/exportExcel', $data);
    }


    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('fault_sn', 'type', 'lock_sn'));
        $fault_id = $this->request->get('fault_id');
        if (isset($this->request->get['fault_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'fault_id' => $this->request->get['fault_id'],
                'cooperator_id' => $this->cooperator_id
            );
            $info = $this->sys_model_fault->getFaultInfo($condition);
        }

        $this->assign('data', $info);
        $this->assign('types', get_fault_type());
        $this->assign('action', $this->cur_url . '&fault_id=' . $fault_id);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('operation/fault_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('fault_sn', 'type', 'lock_sn'));

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