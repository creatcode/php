<?php

class ControllerOperationOperationLocation extends Controller
{
    private $cur_url = null;
    private $sys_model_operations_position;
    private $sys_model_open_Lock;
    private $sys_model_cooperator;
    private $sys_model_admin;
    private $sys_model_repair;
    private $cooperator_id;


    /**
     * ControllerOperationOperationLocation constructor.
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');
        $this->sys_model_operations_position = new Sys_Model\Operations_Position($registry);
        $this->sys_model_open_Lock = new Sys_Model\Open_Lock($registry);
        $this->sys_model_cooperator = new Sys_Model\Cooperator($registry);
        $this->sys_model_admin = new Sys_Model\Admin($registry);
        $this->sys_model_repair = new Sys_Model\Repair($registry);
        $this->sys_model_orders = new Sys_Model\Orders($registry);
    }

    /**
     * 运维人员定位首页
     */
    public function index()
    {
        $cooperatorList = $this->sys_model_cooperator->getCooperatorList(['cooperator_id' => $this->cooperator_id]);
        $this->assign('data_columns', '');
        $this->assign('cooperatorList', $cooperatorList);
        $this->assign('location_url', $this->url->link('operation/operationLocation'));
        $this->assign('repair_url', $this->url->link('operation/operationRecords'));
        $this->assign('area_url', $this->url->link('operation/operationArea'));
        $this->assign('repair_detail_url', $this->url->link('operation/operationRecords/detail'));
        $this->response->setOutput($this->load->view('operation/operation_location', $this->output));
    }

    /**
     * 获取运维人员坐标
     */
    public function apiGetMarker()
    {
        $condition = [
            'admin.cooperator_id' => $this->cooperator_id,
            'admin.type' => 3,
            'admin.state' => 1,
        ];
        //连表查运维人员所属的片区
        $join = [
            'operations_to_region' => 'operations_to_region.admin_id=admin.admin_id',
        ];
        //获取所有正常的运维人员
        $operators = $this->sys_model_admin->getAdminList($condition, '', '', 'admin.admin_id,admin.admin_name,admin.nickname,admin.mobile,operations_to_region.region_name', $join);
        $markers = [];
        foreach ($operators as &$operator) {
            $condition = [
                'operator_id' => $operator['admin_id'],
                'add_time' => [
                    ['gt', strtotime(date('Y-m-d'))],
                    ['elt', time()]
                ]
            ];
            //获取这个运维人员的最后一次定位
            $operator_position = $this->sys_model_operations_position->getOperationsPosition($condition, 'lng,lat');
            if ($operator_position) {
                $operator['lng'] = $operator_position['lng'];
                $operator['lat'] = $operator_position['lat'];
                $markers[] = $operator;
            }
        }
        $this->response->showSuccessResult($markers);
    }

    /**
     * 获取开锁记录
     * @param page
     * @param string $operator_name 运维人员名称
     */
    public function openLockRecords()
    {
        $page = isset($this->request->post['page']) ? $this->request->post['page'] : 1;
        $operator_name = $this->request->request['operator_name'];

        $condition['user_name'] = $operator_name;

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $result = $this->sys_model_open_Lock->getOrdersList($condition, $order, $limit, 'full_bicycle_sn,add_time');
        foreach ($result as &$item) {
            $item['bicycle_sn'] = substr($item['full_bicycle_sn'], -6);
        }
        $this->assign('page', $page + 1);
        $this->assign('data', $result);
        $this->assign('config_limit_admin', $rows);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('operation/open_lock_records', $this->output));
    }

    /**
     * 运维处理列表
     * @param int $page
     * @param string $admin_name 运维人员名称
     */
    public function handleFaultList()
    {
        $page = isset($this->request->post['page']) ? $this->request->post['page'] : 1;
        $admin_name = $this->request->post['operator_name'];
        $admin_info = $this->sys_model_admin->getAdminInfo(['admin_name' => $admin_name]);
        $condition['repairTable.admin_id'] = $admin_info['admin_id'];
        $order = 'repairTable.add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'repairTable.add_time,repairTable.fault_id,repairTable.parking_id,repairTable.bicycle_id,admin.admin_name,bicycle.bicycle_sn';
        $join = [
            'admin' => 'admin.admin_id = repairTable.admin_id',
            'bicycle' => 'bicycle.bicycle_id = repairTable.bicycle_id',
        ];
        $result = $this->sys_model_repair->getRepairList($condition, $order, $limit, $field, $join);
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                isset($item['fault_id']) ? $item['handle_type'] = '处理故障' : $item['handle_type'] = '处理违停';
            }
        }
        $this->assign('page', $page + 1);
        $this->assign('data', $result);
        $this->assign('config_limit_admin', $rows);
        $this->assign('static', HTTP_IMAGE);
        $this->response->setOutput($this->load->view('operation/map_repair_records', $this->output));
    }

    /**
     * 获取合伙人运维人员
     * @param cooperator_id
     */
    public function getOperators()
    {
        $condition['cooperator_id'] = $this->request->post['cooperator_id'];
        //admin的运维人员标识
        $condition['type'] = 3;
        //是否禁用
        $condition['state'] = 1;
        $result = $this->sys_model_admin->getAdminList($condition, '', '', 'admin_id,admin_name,nickname');
        $this->response->showSuccessResult($result);
    }

    /**
     * 获取某个运维人员的定位
     */
    public function getOperatorsPosition()
    {
        //改变时区
        date_default_timezone_set('PRC');
        //获取运维人员的id
        $condition = ['admin_name' => $this->request->post['operator_name']];
        $admin_info = $this->sys_model_admin->getAdminInfo($condition);
        //搜索当天的定位记录
        $condition = [
            'operator_id' => $admin_info['admin_id'],
            'add_time' => [
                ['gt', strtotime(date('Y-m-d'))],
                ['elt', time()]
            ]
        ];
        $result = $this->sys_model_operations_position->getOperationsPositionList($condition, 'lng,lat,add_time', 'add_time ASC');
        $path = [];
        foreach ($result as $item) {
            $condition = [
                'admin_id' => $admin_info['admin_id'],
                'add_time' => [
                    ['gt', strtotime(date('Y-m-d'))],
                    ['elt', $item['add_time']]
                ]
            ];
            $repair_num = $this->sys_model_repair->getTotalRepairs($condition);
            $path[] = [
                'add_time' => date('Y-m-d H:i:s', $item['add_time']) . ' 维修数 ' . $repair_num,
                'lnglat' => [$item['lng'], $item['lat']],
            ];
        }
        $data['name'] = $admin_info['nickname'] . '运维轨迹';
        $data['points'] = $path;
        $this->response->showSuccessResult($data);
    }
}
