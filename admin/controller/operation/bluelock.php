<?php
class ControllerOperationBluelock extends Controller {
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载fault Model
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/lock', true);
        $this->load->library('sys_model/fault', true);
    }

    /**
     * 故障记录列表
     */
    public function index() {

//        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
//            //AJAX请求
//            if ($this->request->get('method') == 'json') {
//                $this->apiIndex();
//                return;
//            }
//        }

        $filter = $this->request->get(array('filter_type', 'bicycle_sn', 'cooperator_id', 'lock_sn', 'fault_type', 'user_name', 'add_time', 'processed', 'cooperator_name'));

        $condition = array('l.lock_type' => '2');

        if (!empty($filter['bicycle_sn'])) {
            $condition['b.bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }

        if (!empty($filter['lock_sn'])) {
            $condition['l.lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }

        if (!empty($filter['cooperator_id'])) {
            $condition['l.cooperator_id'] = array('like', "%{$filter['cooperator_id']}%");
        }

        if (!empty($filter['cooperator_name'])) {
            $condition['cooperator.cooperator_name'] = array('like', "%{$filter['cooperator_name']}%");
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_bicycle->getBlueBikeInfo($condition,'', $limit);
        $total = $this->sys_model_bicycle->getBlueBikeInfototal($condition);

        #get_bicycle_type '1' => '小强一代', '2' => '小强二代',
        #0-锁车，1-开锁，2-异常 get_lock_status
        #1GPRS，2蓝牙，3机械，4GPRS+蓝牙 get_lock_type
        $lock_type_arr = get_lock_type();
        $lock_status_arr = get_lock_status();
        $bicycle_type_arr = get_bicycle_type();
        $this->load->library('sys_model/cooperator', true);
        $cooperator = $this->sys_model_cooperator->getCooperatorList();
        $cooperator_arr = array();
        foreach($cooperator as $v){
            $cooperator_arr[$v['cooperator_id']] = $v['cooperator_name'];
        }

        foreach($result as &$v ){
            $v['bicycle_type']      = isset($bicycle_type_arr[$v['type']]) ? $bicycle_type_arr[$v['type']] : '';
            $v['lock_type']         = isset($lock_type_arr[$v['lock_type']]) ? $lock_type_arr[$v['lock_type']] : '';
            $v['lock_status']       = isset($lock_status_arr[$v['lock_status']]) ? $lock_status_arr[$v['lock_status']] : '';
            $v['cooperator_name']   = isset($cooperator_arr[$v['cooperator_id']]) ? $cooperator_arr[$v['cooperator_id']] : '平台';
            $v['fault_types']       = '';
            $v['fault_type_id']     = '';
            $v['info_action']       = $this->url->link('bicycle/bicycle/info','bicycle_id='.$v['bicycle_id']);
            $w['bicycle_id']        = $v['bicycle_id'];
            $w['fault_type']        = '12';
            $w['processed']         = '0';
            $fault_process0         = $this->sys_model_fault->getFaultList($w);
            unset($w['processed']);
            $fault_process_all      = $this->sys_model_fault->getFaultList($w);
            $v['fault_num']     = count($fault_process0);
            $v['faultd_num']     = count($fault_process_all);
        }


        $data_columns = $this->getDataColumns();
        $this->assign('fault_types', '');
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('cooperator_id', $filter['cooperator_id']);
        $this->assign('cooperator_arr', $cooperator_arr);

        $this->assign('action', $this->cur_url);
        $this->assign('return_action', $this->url->link('operation/bluelock'));
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

        $this->response->setOutput($this->load->view('operation/bluelock_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('单车编号');
        $this->setDataColumn('异常结束(未处理/已处理)');
        $this->setDataColumn('锁电压');
        $this->setDataColumn('合伙人');
        $this->setDataColumn('锁编号');
        $this->setDataColumn('单车类型');
        $this->setDataColumn('电量');
        $this->setDataColumn('区域');
        $this->setDataColumn('锁状态');
        return $this->data_columns;
    }


}