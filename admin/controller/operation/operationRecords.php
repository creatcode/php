<?php

/**
 * Class ControllerOperationOperationRecords
 */
class ControllerOperationOperationRecords extends Controller
{
    private $cur_url = null;
    private $error = null;

    /**
     * @var \Sys_Model\Repair
     */
    private $sys_model_repair;

    /**
     * ControllerOperationOperationRecords constructor.
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->sys_model_repair = new Sys_Model\Repair($registry);
//        $this->load->library('sys_model/repair', true);
        $this->load->library('sys_model/cooperator', true);
        $this->sys_model_fault = new Sys_Model\Fault($registry);
        $this->sys_model_bicycle = new Sys_Model\Bicycle($registry);
        $this->sys_model_admin = new Sys_Model\Admin($registry);
    }

    /**
     * 运维记录列表
     * get
     * @param string $filter ['admin_name']  搜索用户信息
     * @param string $filter ['add_time']   添加时间
     */
    public function index()
    {
        $filter = $this->request->get(array('cooperator_id', 'add_time', 'admin_name'));
        //获取合伙人
        $condition = [];
        $admin_condition = ['type' => 3, 'state' => 1];
        $cooperator_condition = [];

        if (!empty($filter['admin_name'])) {
            $admin_condition['admin_name'] = array('like', "%{$filter['admin_name']}%");
        }

        if (!empty($filter['cooperator_id'])) {
            $cooperator_condition['cooperator_id'] = $filter['cooperator_id'];
        }

        if (!isset($filter['add_time'])) {
            $filter['add_time'] = date('Y-m-d', (time() - 3 * 24 * 60 * 60)) . ' 至 ' . date('Y-m-d', time());
        }

        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', strtotime($add_time[1]))
            );
        }

        $cooperators = $this->sys_model_cooperator->getCooperatorList([]);

        $cooperatorsTree = $this->sys_model_cooperator->getCooperatorList($cooperator_condition);

        //手动添加平台
        $platform = [
            'cooperator_id' => '0',
            'cooperator_name' => '平台'
        ];

        if (!isset($cooperator_condition['cooperator_id'])) {
            array_push($cooperatorsTree, $platform);
        } else {
            if ($cooperator_condition['cooperator_id'] === '999') {
                $cooperatorsTree = [$platform];
            }
        }


        //统计运维人员处理了多少故障
        $field = 'MAX(add_time) as add_time,count(fault_id) as handle_num';
        $group = 'admin_id';
//        $result = $this->sys_model_repair->getRepairList($condition, $order, '', $field, $join, '', $group);
        //获取运维人员
        $admin_list = $this->sys_model_admin->getAdminList($admin_condition, '', '', 'admin.cooperator_id,admin.admin_id,admin.admin_name,admin.nickname');
        //开始生成树，合伙人->下面的运维人员
        foreach ($cooperatorsTree as &$cooperator) {
            $cooperator['repair_count'] = 0;
            $cooperator['operator_count'] = 0;
            $cooperator['need_fault_count'] = 0;
            $cooperator['not_used_count'] = 0;
            //获取待处理故障数
            $cooperator['need_fault_count'] = $this->sys_model_fault->getTotalFaults(['cooperator_id' => $cooperator['cooperator_id'], 'processed' => 0]);
            //获取5天未挪动单车数
            $cooperator['not_used_count'] = $this->sys_model_bicycle->getTotalBicycles(['cooperator_id' => $cooperator['cooperator_id'], 'last_used_time' => ['elt', (time() - 5 * 24 * 60 * 60)]]);
            foreach ($admin_list as $item) {
                //判断属于是哪个合伙人
                if ($cooperator['cooperator_id'] == $item['cooperator_id']) {
                    //获取运维人员的维修记录
                    $condition['admin_id'] = $item['admin_id'];
                    $item['repair_info'] = $this->sys_model_repair->getRepairInfo($condition, $field, $group);
                    $item['info_action'] = $this->url->link('operation/operationRecords/detail', 'admin_id=' . $item['admin_id']);
                    if ($item['repair_info']) {
                        $item['repair_info']['add_time'] = date('Y-m-d H:i:s', $item['repair_info']['add_time']);
                        //计算合伙人的总处理数
                        $cooperator['repair_count'] += $item['repair_info']['handle_num'];
                    }
                    $cooperator['operators'][] = $item;
                    $cooperator['operator_count'] += 1;
                }
            }
        }
        $this->assign('cooperators', $cooperators);
        $this->assign('data_rows', $cooperatorsTree);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('location_url', $this->url->link('operation/operationLocation'));
        $this->assign('repair_url', $this->url->link('operation/operationRecords'));
        $this->assign('repair_detail_url', $this->url->link('operation/operationRecords/detail'));
        $this->assign('area_url', $this->url->link('operation/operationArea'));
        $this->response->setOutput($this->load->view('operation/operation_records', $this->output));
    }

    /**
     * 运维人员处理详情
     * get
     * @param int $filter ['admin_id'] 运维人员id
     * @param string $filter ['add_time'] 处理时间
     */
    public function detail()
    {
        $filter = $this->request->get(array('cooperator_id', 'add_time', 'admin_name'));

        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

        $condition = [];

        if (!empty($filter['admin_name'])) {
            $condition['admin.admin_name'] = array('like', "%{$filter['admin_name']}%");
        }
        if (!empty($filter['cooperator_id'])) {
            $condition['admin.cooperator_id'] = $filter['cooperator_id'];
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['repairTable.add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
        }

        $cooperators = $this->sys_model_cooperator->getCooperatorList([]);


        $order = 'repairTable.add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'repairTable.add_time,repairTable.fault_id,repairTable.parking_id,repairTable.bicycle_id,admin.admin_name';
        $join = [
            'admin' => 'admin.admin_id = repairTable.admin_id',
        ];
        $joinTypeSelect = 'inner';
        $result = $this->sys_model_repair->getRepairList($condition, $order, $limit, $field, $join, $joinTypeSelect);
        $total = $this->sys_model_repair->getTotalRepairList($condition, $join, $joinTypeSelect);
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                isset($item['fault_id']) ? $item['handle_type'] = '处理故障' : $item['handle_type'] = '处理违停';
                $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
                $item['info_action'] = $this->url->link('operation/fault/info', 'bicycle_id=' . $item['bicycle_id']);
            }
        }

        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('cooperators', $cooperators);
        $this->assign('action', $this->cur_url);
        $this->assign('location_url', $this->url->link('operation/operationLocation'));
        $this->assign('repair_url', $this->url->link('operation/operationRecords'));
        $this->assign('repair_detail_url', $this->url->link('operation/operationRecords/detail'));
        $this->assign('area_url', $this->url->link('operation/operationArea'));
        $page_info = $this->page($total, $page, $rows, $filter, $offset);
        $this->assign('pagination', $page_info['pagination']);
        $this->assign('results', $page_info['results']);
        $this->response->setOutput($this->load->view('operation/operation_records_detail', $this->output));
    }

    /**
     * 分页
     * @param int $total 数据总数
     * @param int $page 当前页数
     * @param int $rows 一页多少条记录
     * @param array $filter 链接的参数
     * @param int $offset
     * @return array
     */
    protected function page($total, $page, $rows, $filter, $offset)
    {
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));
        return array('pagination' => $pagination, 'results' => $results);
    }
}
