<?php

class ControllerOperationFaultAnalyze extends Controller
{

    public function __construct($registry)
    {
        parent::__construct($registry);
        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/fault', true);
        $this->load->library('sys_model/intelligent', true);
        $this->assign('lang', $this->language->all());
    }


    /**
     * 故障单车统计
     */
    public function index()
    {
        $filter = $this->request->get(array('fault_type_id', 'processed', 'add_time'));
        $condition = array();
        $processed_str = '';
        if (!empty($filter['fault_type_id'])) {
            $condition['_string'] = 'find_in_set(' . (int)$filter['fault_type_id'] . ',fault_type)';
        }
        if (isset($filter['processed']) && $filter['processed'] > -1) {
            $condition['processed'] = $filter['processed'];
            $processed_str = '&processed=' . $filter['processed'];
        }
        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);
            $starttime = strtotime($pdr_add_time[0]);
            $endtime = strtotime($pdr_add_time[1]);
            $condition['add_time'] = [array('egt', $starttime), array('elt', $endtime)];
        }
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }
        $fault_type_list = $this->sys_model_fault->getFaultTypeList(array('is_show' => 1), '', '', 'fault_type_id,fault_type_name');
        //获取同一辆单车的故障数目
        $order = 'fault_num desc';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'count(fault_id) as fault_num,bicycle_sn';
        $group = 'bicycle_id';
        $result = $this->sys_model_fault->getFaultList($condition, $order, $limit, $field, '', $group);
//        echo($this->db->getLastSql());
        $total = count($this->sys_model_fault->getFaultList($condition, '', '', 'DISTINCT bicycle_id'));
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['info_action'] = $this->url->link('operation/fault', 'bicycle_sn=' . $item['bicycle_sn'] . '&fault_type=' . $filter['fault_type_id'] . $processed_str);
            }
        }
        $this->assign('data_rows', $result);
        $this->assign('total_bicycle', $total);
        $this->assign('filter', $filter);
        $this->assign('fault_type_list', $fault_type_list);
        $this->assign('action', $this->cur_url);
        $this->assign('chart_url', $this->url->link('operation/faultAnalyze/pieChart'));
        $page_info = $this->page($total, $page, $rows, $filter, $offset);
        $this->assign('pagination', $page_info['pagination']);
        $this->assign('results', $page_info['results']);
        $this->assign('time_type',get_time_type());
        $this->response->setOutput($this->load->view('intelligent/fault_bicycle_list', $this->output));
    }

    /**
     * 故障单车饼图
     */
    public function pieChart()
    {
        $filter = $this->request->get(array('add_time'));
        $condition = [];
        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);
            $starttime = strtotime($pdr_add_time[0]);
            $endtime = strtotime($pdr_add_time[1]);
            $condition['add_time'] = [array('egt', $starttime), array('elt', $endtime)];
        }
        //获取获取同样故障的单车
        $field = 'count(fault_id) as `count`,fault_type';
        $group = 'fault_type';
        $result = $this->sys_model_fault->getFaultList($condition, '', '', $field, '', $group);
//        echo($this->db->getLastSql());
        $total = $this->sys_model_fault->getTotalFaults($condition);
        $field = 'fault_type_id,fault_type_name';
        $type_list = $this->sys_model_fault->getFaultTypeList(array('is_show' => 1), '', '', $field);
        $type_list=array(
            array(
                'fault_type_id'=>1,
                'fault_type_name'=>'锁'
            ),
            array(
                'fault_type_id'=>2,
                'fault_type_name'=>'刹车'
            ),
            array(
                'fault_type_id'=>3,
                'fault_type_name'=>'龙头'
            ),
            array(
                'fault_type_id'=>4,
                'fault_type_name'=>'车铃'
            ),
            array(
                'fault_type_id'=>5,
                'fault_type_name'=>'传动轴'
            ),
            array(
                'fault_type_id'=>6,
                'fault_type_name'=>'脚踏板'
            ),
            array(
                'fault_type_id'=>7,
                'fault_type_name'=>'车座'
            ),
            array(
                'fault_type_id'=>8,
                'fault_type_name'=>'轮胎'
            ),
            array(
                'fault_type_id'=>9,
                'fault_type_name'=>'控制盒故障'
            ),
            array(
                'fault_type_id'=>10,
                'fault_type_name'=>'读卡器'
            ),
            array(
                'fault_type_id'=>11,
                'fault_type_name'=>'低电量'
            ),
            array(
                'fault_type_id'=>12,
                'fault_type_name'=>'其他'
            ),
            
            
        );
        foreach ($type_list as &$type) {
            foreach ($result as $re) {
                if ($re['fault_type']) {
                    $re_fault = explode(',', $re['fault_type']);
                    if (array_intersect($type, $re_fault)) {
                        if (!isset($type['count'])) {
                            $type['count'] = 0;
                        }
                        $type['count'] += $re['count'];
                    }
                }
            }
        }
        $result = [];
        foreach ($type_list as $item) {
            $result[] = [
                'label' => $item['fault_type_name'],
                'value' => isset($item['count']) ? $item['count'] : 0
            ];
        }
//        var_dump(json_encode($result));exit();
        $this->assign('data', json_encode($result));
        $this->assign('total', $total);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('fault_analyze_url', $this->url->link('operation/faultAnalyze'));
        $this->assign('time_type',get_time_type());
        $this->response->setOutput($this->load->view('intelligent/chart', $this->output));
    }


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