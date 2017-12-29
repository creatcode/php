<?php

class ControllerOperationFault extends Controller
{
    private $cur_url = null;
    private $error = null;

    public function __construct($registry)
    {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载fault Model
        $this->load->library('sys_model/fault', true);
    }

    /**
     * 故障记录列表
     */
    public function index()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //AJAX请求
            if ($this->request->get('method') == 'json') {
                $this->apiIndex();
                return;
            }
        }

        $filter = $this->request->get(array('filter_type', 'bicycle_sn', 'lock_sn', 'fault_type', 'processed', 'user_name', 'add_time', 'processed', 'cooperator_name'));

        $condition = array();
        if (!empty($filter['bicycle_sn'])) {
            $condition['fault.bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['lock_sn'])) {
            $condition['fault.lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (is_numeric($filter['fault_type'])) {
            $condition['_string'] = 'find_in_set(' . (int)$filter['fault_type'] . ', fault.fault_type)';
        }
        if (!empty($filter['user_name'])) {
            $condition['fault.user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (is_numeric($filter['processed'])) {
            $condition['processed'] = (int)$filter['processed'];
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['fault.add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        if (!empty($filter['cooperator_name'])) {
            $condition['cooperator.cooperator_name'] = array('like', "%{$filter['cooperator_name']}%");
        }
        if (isset($filter['processed']) && $filter['processed'] != '' && $filter['processed'] > -1) {
            $condition['fault.processed'] = $filter['processed'];
        }

        $filter_types = array(
            'bicycle_sn' => '单车编号',
            'lock_sn' => '车锁编号',
            'user_name' => '用户名',
            'cooperator_name' => '所属合伙人',
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

        $field = 'fault.*, admin.nickname,cooperator.cooperator_name';
        $result = $this->sys_model_fault->getFaultWithRepairList($condition, $order, $limit, $field);
        $total = $this->sys_model_fault->getTotalFaultsWithRepair($condition);

        $fault_types = $this->fault_types();
        $process_states = get_fault_processed();

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $fault_type = '';
                $fault_type_ids = array_unique(explode(',', $item['fault_type']));
                foreach ($fault_type_ids as $fault_type_id) {
                    $fault_type .= isset($fault_types[$fault_type_id]) ? ',' . $fault_types[$fault_type_id] : '';
                }
                $item['fault_type_id'] = $item['fault_type'];
                $item['fault_type'] = !empty($fault_type) ? substr($fault_type, 1) : '';
                $item['add_time_delta'] = !empty($item['add_time']) && !$item['processed'] ? $this->formatDeltaTime(time(), $item['add_time']) : '';
                $item['handling_time_delta'] = !empty($item['handling_time']) ? $this->formatDeltaTime($item['handling_time'], $item['add_time']) : '';
                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['handling_time'] = !empty($item['handling_time']) ? date('Y-m-d H:i:s', $item['handling_time']) : '';
                $item['edit_action'] = $this->url->link('operation/fault/edit', 'fault_id=' . $item['fault_id']);
                $item['delete_action'] = $this->url->link('operation/fault/delete', 'fault_id=' . $item['fault_id']);
                $item['info_action'] = $this->url->link('operation/fault/info', ['bicycle_id' => $item['bicycle_id'], 'fault_id' => $item['fault_id']]);
                $item['processed'] = isset($process_states[$item['processed']]) ? $process_states[$item['processed']] : '';
                $item['cooperator_name'] = $item['cooperator_name'] ? $item['cooperator_name'] : '-';
                $item['nickname'] = $item['nickname'] ? $item['nickname'] : '-';
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('fault_types', $fault_types);
        $this->assign('process_states', $process_states);
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

        $this->assign('index_action', $this->url->link('admin/index'));

        $this->assign('export_action', $this->url->link('operation/fault/export'));
        $this->assign('export_unused_action', $this->url->link('operation/fault/export_unused'));

        $this->response->setOutput($this->load->view('operation/fault_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns()
    {
        $this->setDataColumn('单车编号');
        $this->setDataColumn('故障类型');
        $this->setDataColumn('用户名');
        $this->setDataColumn('合伙人');
        $this->setDataColumn('上报时间');
        $this->setDataColumn('处理状态');
        $this->setDataColumn('处理时间');
        $this->setDataColumn('处理人');
        $this->setDataColumn('合伙人');
        return $this->data_columns;
    }

    /**
     * index AJAX请求
     */
    protected function apiIndex()
    {
        if (isset($this->request->post['page'])) {
            $page = (int)$this->request->post['page'];
        } else {
            $page = 1;
        }

        $condition = array(
            'fault.processed' => 0
        );
        $order = 'fault.add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_fault->getFaultWithRepairList($condition, $order, $limit);
        $total = $this->sys_model_fault->getTotalFaultsWithRepair($condition);
        $this->load->library('sys_model/bicycle');
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'bicycle_sn' => $v['bicycle_sn'],
                    'add_time' => date('Y-m-d H:i:s', $v['add_time']),
                    'uri' => $this->url->link('admin/index'),
                    'bicycle_id' => $this->sys_model_bicycle->getBicycleInfo(array('bicycle_sn' => $v['bicycle_sn']))['bicycle_id'],
                );
            }
        }

        $statisticsMessages = $this->load->controller('common/base/statisticsMessages');

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
     * 添加故障记录
     */
    public function add()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('bicycle_sn', 'type', 'lock_sn'));
            $now = time();
            $data = array(
                'fault_sn' => $input['fault_sn'],
                'type' => (int)$input['type'],
                'lock_sn' => $input['lock_sn'],
                'add_time' => $now
            );
            $this->sys_model_fault->addFault($data);

            $this->session->data['success'] = '添加故障记录成功！';

            $filter = $this->request->get(array('filter_type', 'fault_sn', 'type', 'lock_sn', 'is_using'));

            $this->load->controller('common/base/redirect', $this->url->link('operation/fault', $filter, true));
        }

        $this->assign('title', '故障记录添加');
        $this->getForm();
    }

    /**
     * 编辑故障记录
     */
    public function edit()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('fault_sn', 'type', 'lock_sn'));
            $fault_id = $this->request->get['fault_id'];
            $data = array(
                'fault_sn' => $input['fault_sn'],
                'type' => (int)$input['type'],
                'lock_sn' => $input['lock_sn']
            );
            $condition = array(
                'fault_id' => $fault_id
            );
            $this->sys_model_fault->updateFault($condition, $data);

            $this->session->data['success'] = '编辑故障记录成功！';

            $filter = $this->request->get(array('fault_sn', 'type', 'lock_sn', 'is_using'));

            $this->load->controller('common/base/redirect', $this->url->link('operation/fault', $filter, true));
        }

        $this->assign('title', '编辑故障记录');
        $this->getForm();
    }

    /**
     * 删除故障记录
     */
    public function delete()
    {
        if (isset($this->request->get['fault_id']) && $this->validateDelete()) {
            $condition = array(
                'fault_id' => $this->request->get['fault_id']
            );
            $this->sys_model_fault->deleteFault($condition);

            $this->session->data['success'] = '删除故障记录成功！';
        }
        $filter = $this->request->get(array('filter_type', 'fault_sn', 'type', 'lock_sn', 'is_using'));
        $this->load->controller('common/base/redirect', $this->url->link('operation/fault', $filter, true));
    }

    /**
     * 处理故障
     */
    public function handling()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $input = $this->request->post();

            if (empty($input['is_handling']) || !(empty($input['fault_id']) || empty($input['parking_id']))) {
                $this->response->showSuccessResult('没有故障被处理');
            }

            foreach ($input as $v) {
                if (empty($v)) {
                    //$this->response->showErrorResult('提交数据不完整');
                }
            }

            $this->load->library('sys_model/repair', true);

            //处理违停
            if ($input['parking_id']) {
                $IllegalParkingList = $this->sys_model_fault->getIllegalParkingList(array('processed' => 0, 'parking_id' => $input['parking_id']));
                if ($IllegalParkingList) {
                    $parkingResult = $this->sys_model_fault->updateIllegalParking(array('processed' => 0, 'parking_id' => $input['parking_id']), array('processed' => 1));
                    $repair_data = array(
                        'bicycle_id' => $IllegalParkingList[0]['bicycle_id'],
                        'repair_type' => $input['repair_type'],
                        'remarks' => $input['handle_content'],
                        'image' => HTTP_IMAGE . $input['handle_image'],
                        'add_time' => TIMESTAMP,
                        'admin_id' => $this->logic_admin->getId(),
                        'admin_name' => $this->logic_admin->getadmin_name(),
                        'parking_id' => $input['parking_id']
                    );
                    $this->sys_model_repair->addRepair($repair_data);
                    $countParking = count($IllegalParkingList);
                    if ($countParking == 1 && $parkingResult) {
                        $this->sys_model_bicycle->updateBicycle(array('bicycle_sn' => $IllegalParkingList[0]['bicycle_sn']), array('illegal_parking' => 0));
                    }
                    $this->response->showSuccessResult('操作成功');
                } else {
                    $this->response->showErrorResult('故障记录不存在，请检查');
                }
            }

            $where = array(
                'fault_id' => $input['fault_id']
            );

            $info = $this->sys_model_fault->getFaultInfo($where);

            if (empty($info)) {
                $this->response->showErrorResult('故障记录不存在，请检查');
            }

            $data = array(
                'content' => $input['handle_content'],
                'handling_time' => TIMESTAMP,
                'handler' => $this->logic_admin->getId(),
                'processed' => 1,
            );

            $repair_data = array(
                'bicycle_id' => $info['bicycle_id'],
                'repair_type' => $input['repair_type'],
                'remarks' => $input['handle_content'],
                'image' => HTTP_IMAGE . $input['handle_image'],
                'add_time' => TIMESTAMP,
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'fault_id' => $input['fault_id']
            );

            if ($info['processed'] == 1) {
                $this->response->showErrorResult('故障已处理过，请不要重复提交');
            }

            try {
                $this->db->begin();
                if ($this->sys_model_fault->updateFault($where, $data) && $this->sys_model_repair->addRepair($repair_data)) {
                    $faultAllOk = !$this->sys_model_fault->getFaultList(array('bicycle_id' => $info['bicycle_id'], 'processed' => 0), null, 1);
                    if ($faultAllOk) {
                        $this->load->library('sys_model/bicycle');
                        $ok = $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $info['bicycle_id']), array('fault' => 0));
                    }
                    $this->db->commit();
                    $this->response->showSuccessResult('操作成功');
                }
            } catch (\Exception $e) {
                $this->db->rollback();
                $this->response->showErrorResult('操作失败');
            }
        }
    }

    /**
     * 批量处理故障
     */
    public function batchHandling()
    {

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $input = $this->request->post();
            if (empty($input['is_batch_handling']) || empty($input['bicycle_sn'])) {
                $this->response->showSuccessResult('没有故障被处理');
            }

            $this->load->library('sys_model/repair', true);

            //违停处理
            $IllegalParking = $this->sys_model_fault->getIllegalParkingList(array('processed' => 0, 'bicycle_sn' => $input['bicycle_sn']));
            if ($IllegalParking) {
                $parkingResult = $this->sys_model_fault->updateIllegalParking(array('processed' => 0, 'bicycle_sn' => $input['bicycle_sn']), array('processed' => 1));
                foreach ($IllegalParking as $item) {
                    $repair_data = array(
                        'bicycle_id' => $item['bicycle_id'],
                        'repair_type' => $input['repair_type'],
                        'remarks' => $input['handle_content'],
                        'image' => HTTP_IMAGE . $input['handle_image'],
                        'add_time' => TIMESTAMP,
                        'admin_id' => $this->logic_admin->getId(),
                        'admin_name' => $this->logic_admin->getadmin_name(),
                        'parking_id' => $item['parking_id']
                    );
                    $this->sys_model_repair->addRepair($repair_data);
                }
                if ($parkingResult) {
                    $this->sys_model_bicycle->updateBicycle(array('bicycle_sn' => $input['bicycle_sn']), array('illegal_parking' => 0));
                }
            }

            if (is_string($input['fault_ids'])) {
                $fault_ids = json_decode($input['fault_ids'], TRUE);
                if (empty($fault_ids)) {
                    $input['fault_ids'] = explode(',', $input['fault_ids']);
                } else {
                    $input['fault_ids'] = $fault_ids;
                }
            }
            if (empty($input['fault_ids'])) {
                $this->response->showSuccessResult('没有故障被处理');
            }

            $condition = array(
                'fault.fault_id' => array('in', (array)$input['fault_ids']),
                'fault.processed' => 0
            );
            $faultList = $this->sys_model_fault->getFaultWithRepairList($condition);
            if (empty($faultList)) {
                $this->response->showSuccessResult('没有故障被处理');
            }

            try {
                $admin_id = $this->logic_admin->getId();
                $admin_name = $this->logic_admin->getadmin_name();
                $this->db->begin();
                foreach ($faultList AS $fault) {
                    $data = array(
                        'content' => $input['handle_content'],
                        'handling_time' => TIMESTAMP,
                        'handler' => $this->logic_admin->getId(),
                        'processed' => 1,
                    );

                    $repair_data = array(
                        'bicycle_id' => $fault['bicycle_id'],
                        'repair_type' => $input['repair_type'],
                        'remarks' => $input['handle_content'],
                        'image' => HTTP_IMAGE . $input['handle_image'],
                        'add_time' => TIMESTAMP,
                        'admin_id' => $admin_id,
                        'admin_name' => $admin_name,
                        'fault_id' => $fault['fault_id']
                    );
                    $where = array(
                        'fault_id' => $fault['fault_id']
                    );
                    $ok = $this->sys_model_fault->updateFault($where, $data) && $this->sys_model_repair->addRepair($repair_data);
                    if ($ok) {
                        $faultAllOk = !$this->sys_model_fault->getFaultList(array('bicycle_id' => $fault['bicycle_id'], 'processed' => 0), null, 1);
                        if ($faultAllOk) {
                            $this->load->library('sys_model/bicycle');
                            $ok = $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $fault['bicycle_id']), array('fault' => 0));
                        }
                    }
                    if (!$ok) {
                        $this->db->rollback();
                        $this->response->showErrorResult('操作失败');
                    }
                }
                $this->db->commit();
                $this->response->showSuccessResult('操作成功');
            } catch (\Exception $e) {
                $this->db->rollback();
                $this->response->showErrorResult('操作失败');
            }
        }
    }

    /**
     * 故障记录详情
     */
    public function info()
    {
        // 编辑时获取已有的数据
        $bicycle_id = $this->request->get('bicycle_id');
        $fault_id = $this->request->get('fault_id');

        $this->load->library('sys_model/cooperator', true);
        $this->load->library('sys_model/region', true);
        $this->load->library('sys_model/lock', true);
        $this->load->library('sys_model/bicycle', true);

        $condition = array(
            'bicycle_id' => $bicycle_id
        );
        //获取单车基本信息
        $bicycle = $this->sys_model_bicycle->getBicycleInfo($condition);

        //获取锁基本信息
        $condition = array(
            'lock_id' => $bicycle['lock_id']
        );
        $lock = $this->sys_model_lock->getLockInfo($condition);
        if ($lock) {
            $bicycle['lng'] = $lock['lng'];
            $bicycle['lat'] = $lock['lat'];
        }

        //获取合伙人信息
        $condition = array(
            'cooperator_id' => $bicycle['cooperator_id']
        );
        $cooperator = $this->sys_model_cooperator->getCooperatorInfo($condition);

        //获取区域信息
        $condition = array(
            'region_id' => $bicycle['region_id']
        );
        $region = $this->sys_model_region->getRegionInfo($condition);

        $fault_types = $this->fault_types();
        $repair_types = get_repair_type();

        //获取单车所有未处理的故障记录(待处理故障)
        $condition = array(
            'fault.bicycle_id' => $bicycle_id,
            'fault.processed' => 0
        );
        $order = "IF(fault.fault_id='{$fault_id}',1,0) DESC, fault.fault_id DESC";
        $faultList = $this->sys_model_fault->getFaultWithRepairList($condition, $order);

        if (is_array($faultList) && !empty($faultList)) {
            foreach ($faultList as &$item) {
                $fault_type = '';
                $fault_type_ids = array_unique(explode(',', $item['fault_type']));
                foreach ($fault_type_ids as $fault_type_id) {
                    $fault_type .= isset($fault_types[$fault_type_id]) ? ',' . $fault_types[$fault_type_id] : '';
                }
                $item['parking_id'] = '';
                $item['fault_type'] = !empty($fault_type) ? substr($fault_type, 1) : '';
                $item['add_time_delta'] = !empty($item['add_time']) && !$item['processed'] ? $this->formatDeltaTime(time(), $item['add_time']) : '';
                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['edit_action'] = $this->url->link('operation/fault/edit', 'fault_id=' . $item['fault_id']);
                $item['delete_action'] = $this->url->link('operation/fault/delete', 'fault_id=' . $item['fault_id']);
                $item['info_action'] = $this->url->link('operation/fault/info', 'bicycle_id=' . $item['bicycle_id']);
            }
            unset($item);
        }

        //获取单车所有已处理的故障记录(故障历史)
        $condition = array(
            'fault.bicycle_id' => $bicycle_id,
            'fault.processed' => 1
        );
        $order = 'repair.add_time DESC';
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'fault.*, repair.repair_id,repair.repair_type,repair.remarks AS repair_remarks,repair.image AS repair_image,repair.add_time AS repair_time,repair.admin_id,repair.admin_name';
        $faultHistoryList = $this->sys_model_fault->getFaultHistoryList($condition, $order, $limit, $field);

        if (is_array($faultHistoryList) && !empty($faultHistoryList)) {
            foreach ($faultHistoryList as &$item) {
                $fault_type = '';
                $fault_type_ids = array_unique(explode(',', $item['fault_type']));
                foreach ($fault_type_ids as $fault_type_id) {
                    $fault_type .= isset($fault_types[$fault_type_id]) ? ',' . $fault_types[$fault_type_id] : '';
                }
                $item['fault_type'] = !empty($fault_type) ? substr($fault_type, 1) : '';
                $item['repair_type'] = !empty($repair_types[$item['repair_type']]) ? $repair_types[$item['repair_type']] : '-';
                $item['repair_time_delta'] = !empty($item['repair_time']) ? $this->formatDeltaTime($item['repair_time'], $item['add_time']) : '';
                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['repair_time'] = !empty($item['repair_time']) ? date('Y-m-d H:i:s', $item['repair_time']) : '';
            }
            unset($item);
        }

        // 未处理的违停记录
        $w = "illegal_parking.processed = 0 AND illegal_parking.bicycle_id = " . $bicycle_id;
        $park_list = $this->sys_model_fault->getParkingInfo($w);
        if (!empty($park_list)) {
            $this->load->library("sys_model/bicycle");
            foreach ($park_list as &$v) {
                $v['fault_type'] = '违停';
                $v['fault_content'] = '违停';
                $v['fault_image'] = $v['file_image'];
                $v['fault_id'] = '';
                $v['add_time_delta'] = !empty($v['add_time']) && !$v['processed'] ? $this->formatDeltaTime(time(), $v['add_time']) : '';
                $v['add_time'] = !empty($v['add_time']) ? date('Y-m-d H:i:s', $v['add_time']) : '';
                $v['info_action'] = $this->url->link('operation/fault/info', 'bicycle_id=' . $v['bicycle_id']);
                $faultList[] = $v;
            }
        }

        $this->assign('bicycle', $bicycle);
        $this->assign('cooperator', $cooperator);
        $this->assign('region', $region);
        $this->assign('faultList', $faultList);
        $this->assign('faultHistoryList', $faultHistoryList);
        $this->assign('admin_name', $this->logic_admin->getadmin_name());
        $this->assign('return_action', $this->url->link('operation/fault'));
        $this->assign('upload_url', $this->url->link('common/upload'));
        $this->assign('handling_action', $this->url->link('operation/fault/handling'));
        $this->assign('batch_handling_action', $this->url->link('operation/fault/batchHandling'));
        $this->assign('history_action', $this->url->link('operation/fault/history', array('bicycle_id' => $bicycle_id)));

        $this->response->setOutput($this->load->view('operation/fault_info', $this->output));
    }

    public function history()
    {
        $this->load->library('sys_model/bicycle', true);

        $bicycle_id = $this->request->get('bicycle_id');

        $condition = array(
            'bicycle_id' => $bicycle_id
        );
        //获取单车基本信息
        $bicycle = $this->sys_model_bicycle->getBicycleInfo($condition);

        $fault_types = $this->fault_types();
        $repair_types = get_repair_type();

        //获取单车所有已处理的故障记录(故障历史)
        $condition = array(
            'fault.bicycle_id' => $bicycle_id,
            'fault.processed' => 1
        );
        $order = 'repair.add_time DESC';
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'fault.*, repair.repair_id,repair.repair_type,repair.remarks AS repair_remarks,repair.image AS repair_image,repair.add_time AS repair_time,repair.admin_id,repair.admin_name';
        $faultHistoryList = $this->sys_model_fault->getFaultHistoryList($condition, $order, $limit, $field);

        if (is_array($faultHistoryList) && !empty($faultHistoryList)) {
            foreach ($faultHistoryList as &$item) {
                $fault_type = '';
                $fault_type_ids = array_unique(explode(',', $item['fault_type']));
                foreach ($fault_type_ids as $fault_type_id) {
                    $fault_type .= isset($fault_types[$fault_type_id]) ? ',' . $fault_types[$fault_type_id] : '';
                }
                $item['fault_type'] = !empty($fault_type) ? substr($fault_type, 1) : '';
                if (!empty($repair_type_arr = explode(',',$item['repair_type']))) {
                    $repair_type_str = '';
                    foreach ($repair_type_arr as $v) {
                        $ss = !empty($repair_types[$v]) ? $repair_types[$v] : '没有填写';
                        $repair_type_str = $repair_type_str . "-" . $ss;
                    }
                    $item['repair_type'] = substr($repair_type_str, 1);
                } else {
                    $item['repair_type'] = !empty($repair_types[$item['repair_type']]) ? $repair_types[$item['repair_type']] : '-';
                }

                $item['repair_time_delta'] = !empty($item['repair_time']) ? $this->formatDeltaTime($item['repair_time'], $item['add_time']) : '';
                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['repair_time'] = !empty($item['repair_time']) ? date('Y-m-d H:i:s', $item['repair_time']) : '';
            }
            unset($item);
        }

        // 处理的违停记录
        #$park_list = $this->sys_model_fault->getIllegalParkingList(array('processed' => 1, 'bicycle_id' => $bicycle_id));
        $w = "processed = 1 AND illegal_parking.bicycle_id = " . $bicycle_id;
        $park_list = $this->sys_model_fault->getParkingInfoed($w, $bicycle_id);

        if (!empty($park_list)) {
            $this->load->library("sys_model/bicycle");
            $this->load->library("sys_model/repair");
            foreach ($park_list as &$v) {
                $v['fault_type'] = '违停';
                $v['fault_content'] = '违停';
                $v['fault_image'] = $v['file_image'];
                $v['fault_id'] = $v['parking_id'];
                $v['add_time_delta'] = !empty($v['add_time']) && !$v['processed'] ? $this->formatDeltaTime(time(), $v['add_time']) : '';
                $v['add_time'] = !empty($v['add_time']) ? date('Y-m-d H:i:s', $v['add_time']) : '';
                $v['info_action'] = $this->url->link('operation/fault/info', 'bicycle_id=' . $v['bicycle_id']);
                $v['repair_type'] = !empty($repair_types[$v['repair_type']]) ? $repair_types[$v['repair_type']] : '-';
                $faultHistoryList[] = $v;
            }
        }
        $faultHistoryList = $this->array_multisort_my($faultHistoryList, 'repair_time', 'desc');
        $this->assign('bicycle', $bicycle);
        $this->assign('faultHistoryList', $faultHistoryList);
        $this->assign('info_action', $this->url->link('operation/fault/info', array('bicycle_id' => $bicycle_id)));
        $this->response->setOutput($this->load->view('operation/fault_history', $this->output));
    }

    /**
     * 导出
     */
    public function export()
    {
        $filter = $this->request->post(array('filter_type', 'bicycle_sn', 'lock_sn', 'fault_type', 'user_name', 'add_time', 'processed'));

        $condition = array();
        if (!empty($filter['bicycle_sn'])) {
            $condition['fault.bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['lock_sn'])) {
            $condition['fault.lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (is_numeric($filter['fault_type'])) {
            $condition['_string'] = 'find_in_set(' . (int)$filter['fault_type'] . ', fault.fault_type)';
        }
        if (!empty($filter['user_name'])) {
            $condition['fault.user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (is_numeric($filter['processed'])) {
            $condition['fault.processed'] = $filter['processed'];
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['fault.add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        $order = 'fault.add_time DESC';
        $faults = $this->sys_model_fault->getFaultWithRepairList($condition, $order);

        $fault_types = $this->fault_types();

        $list = array();
        if (is_array($faults) && !empty($faults)) {
            foreach ($faults as $fault) {
                $fault_type = '';
                $fault_type_ids = array_unique(explode(',', $fault['fault_type']));
                foreach ($fault_type_ids as $fault_type_id) {
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

    /**
     * 导出五天未使用的单车
     */
    public function export_unused()
    {
        $this->load->library('sys_model/bicycle');
        $filter = $this->request->post(array('cooperator_name'));

        $now = time();

        $condition = array(
			'bicycle.lock_sn' => array('neq', ''),
            'last_used_time' => array(
                'elt', ($now - 432000)
            ),
        );
        if (!empty($filter['cooperator_name'])) {
            $condition['cooperator.cooperator_name'] = array('like', "%{$filter['cooperator_name']}%");
        }
        $order = 'bicycle.last_used_time DESC';
        $limit = '';
        $field = 'bicycle.*,cooperator.cooperator_name,lock.lock_type';
        $join = array(
            'cooperator' => 'cooperator.cooperator_id=bicycle.cooperator_id',
            'lock' => 'lock.lock_sn=bicycle.lock_sn',
        );
        $bicycles = $this->sys_model_bicycle->getBicycleList($condition, $order, $limit, $field, $join);
        $list = array();
        $lock_types = get_lock_type();
        if (is_array($bicycles) && !empty($bicycles)) {
            foreach ($bicycles as $bicycle) {
                $unused_days = $bicycle['last_used_time'] == 0 ? '从未使用过' : floor(($now - $bicycle['last_used_time']) / 86400);
                $list[] = array(
                    'bicycle_sn' => $bicycle['bicycle_sn'],
                    'lock_sn' => $bicycle['lock_sn'],
                    'lock_type' => isset($lock_types[$bicycle['lock_type']]) ? $lock_types[$bicycle['lock_type']] : '',
                    'cooperator_name' => $bicycle['cooperator_name'],
                    'region_name' => $bicycle['region_name'],
                    'unused_days' => $unused_days,
                );
            }
        }

        $data = array(
            'title' => '故障记录列表',
            'header' => array(
                'bicycle_sn' => '单车编号',
                'lock_sn' => '锁编号',
                'lock_type' => '锁类型',
                'cooperator_name' => '合伙人',
                'unused_days' => '未使用天数',
            ),
            'list' => $list
        );

        $this->load->controller('common/base/exportExcel', $data);
    }


    private function getForm()
    {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('fault_sn', 'type', 'lock_sn'));
        $fault_id = $this->request->get('fault_id');
        if (isset($this->request->get['fault_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'fault_id' => $this->request->get['fault_id']
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
    private function validateForm()
    {
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
    private function validateDelete()
    {
        return !$this->error;
    }

    /**
     * 格式化时间差
     * @param int $time1 时间戳 整型
     * @param int $time2 时间戳 整型
     */
    private function formatDeltaTime($time1, $time2)
    {
        $delta = $time1 - $time2;
        $days = floor($delta / 86400);
        $hours = floor($delta % 86400 / 3600);
        $minutes = floor($delta / 60);
        return ($days > 0 || $hours > 0) ? sprintf('%d天%d小时', $days, $hours) : sprintf('%d分钟', $minutes);
    }

    private function fault_types()
    {
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
        return $fault_types;
    }

    /* 用于根据指定字段排序二维数组，保留原有键值
     * $array array 输入二维数组
     * $sortField string 要排序的字段名
     * $sortBy string 要排序的方式(ASC|DESC)
     * return array
     * author www.phpernote.com
     */
    function array_multisort_my($array, $sortField, $sortBy = 'ASC')
    {
        $result = array();
        foreach ($array as $k => $v) {
            $result[$k] = $v[$sortField];
        }
        $sortBy = strtoupper($sortBy);
        $sortBy == 'ASC' ? asort($result) : ($sortBy == 'DESC' ? arsort($result) : '');
        foreach ($result as $k => $v) {
            $result[$k] = $array[$k];
        }
        return $result;
    }
}
