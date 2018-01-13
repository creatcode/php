<?php

class ControllerOperationContinuousUsed extends Controller
{

    public function __construct($registry)
    {
        parent::__construct($registry);
        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->load->library('sys_model/orders', true);
        $this->load->library('sys_model/intelligent', true);
        $this->assign('lang',$this->language->all());
    }


    /**
     * 占用单车列表
     * @param int $filter['used_num'] 占用次数
     */
    public function index()
    {
        $filter = $this->request->get(array('used_num', 'add_time'));

        $condition = array();
        //加上时间判断，先取出一个月数据
        $condition['add_time'] = [array('egt', time()-30*24*60*60), array('elt', time())];

        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);
            $starttime = strtotime($pdr_add_time[0]);
            $endtime = strtotime($pdr_add_time[1]);
            $condition['add_time'] = [array('egt', $starttime), array('elt', $endtime)];
        }
        //阀值，至少占用5次才会被统计
        $having = 'count(order_id) > 5';
        //筛选条件有次数就覆盖
        if (!empty($filter['used_num'])) {
            $having = 'count(order_id) > ' . intval($filter['used_num']);
        }
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }
        //筛选订单为完成状态的
        $condition['order_state'] = '2';
        $order = '`count` desc';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        //同一个用户使用单车次数，使用这部单车的时间
        $field = 'count(order_id) as `count`,user_id,user_name,bicycle_sn,MAX(add_time) as max_time,MIN(add_time) as min_time';
        $group = 'bicycle_id,user_id';
        $order_list = $this->sys_model_intelligent->getAbnormalOrdersList($condition, $order, '', $field, '', $group, $having);
        $re = array_chunk($order_list, $rows);
        $total = count($order_list);

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }
        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $re ? $re[$page - 1] : array());
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total_bicycle', $total);
        $page_info = $this->page($total, $page, $rows, $filter, $offset);
        $this->assign('pagination', $page_info['pagination']);
        $this->assign('results', $page_info['results']);
        $this->assign('info_url', $this->url->link('operation/continuousUsed/info'));
        $this->assign('time_type',get_time_type());
        $this->response->setOutput($this->load->view('intelligent/continuous_used_bike', $this->output));
    }

    /**
     * 用户异常订单列表
     */
    public function info()
    {
        $get = $this->request->get(array('user_id', 'bicycle_sn'));
        $result = [];
        if ($get) {
            $condition['user_id'] = $get['user_id'];
//            $condition['bicycle_sn'] = $get['bicycle_sn'];
            $order = 'order_id desc';
            $field = 'orders.*, cooperator.cooperator_name';
            $join = array(
                'cooperator' => 'cooperator.cooperator_id=orders.cooperator_id',
            );
            $result = $this->sys_model_orders->getOrdersList($condition, $order, '', $field, $join);
            $order_state = get_order_state();
            if (is_array($result) && !empty($result)) {
                foreach ($result as &$item) {
                    $item['order_state'] = isset($order_state[$item['order_state']]) ? $order_state[$item['order_state']] : '';
                    $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                    $item['start_time'] = !empty($item['start_time']) ? date('Y-m-d H:i:s', $item['start_time']) : '';
                    $item['end_time'] = !empty($item['end_time']) ? date('Y-m-d H:i:s', $item['end_time']) : '';
                    $item['info_action'] = $this->url->link('user/order/info', 'order_id=' . $item['order_id']);
                }
            }
        }
        $this->assign('data_rows', $result);
        $this->response->setOutput($this->load->view('intelligent/user_abnormal_order_list', $this->output));

    }

    protected function getDataColumns()
    {
        $this->setDataColumn($this->language->get('t2'));
        $this->setDataColumn($this->language->get('t3'));
        $this->setDataColumn($this->language->get('t4'));
        //$this->setDataColumn('占用次数');
        $this->setDataColumn($this->language->get('t5'));
        return $this->data_columns;
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