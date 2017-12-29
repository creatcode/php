<?php

class ControllerOperationAbnormalUser extends Controller
{
    private $sys_model_orders;
    private $sys_model_intelligent;

    public function __construct($registry)
    {
        parent::__construct($registry);
        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
//        $this->load->library('sys_model/orders', true);
//        $this->load->library('sys_model/intelligent', true);
        $this->sys_model_orders = new Sys_Model\orders($registry);
        $this->sys_model_intelligent = new Sys_Model\intelligent($registry);
    }


    /**
     * 用户异常分析
     */
    public function index()
    {
        $filter = $this->request->get(array('abnormal_state', 'abnormal_num', 'add_time'));
        $condition = array();
        $having = '';
        //状态为0，正常
        $condition['abnormal_state'] = array('neq', '0');
        if (!empty($filter['abnormal_state'])) {
            $condition['abnormal_state'] = $filter['abnormal_state'];
        }
        if (!empty($filter['abnormal_num'])) {
            $abnormal_num = intval($filter['abnormal_num']);
            $having = "count(order_id) > $abnormal_num";
        }
        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);
            $starttime = strtotime($pdr_add_time[0]);
            $endtime = strtotime($pdr_add_time[1]);
            $condition['add_time'] = array('egt', $starttime);
            $condition['add_time'] = array('elt', $endtime);
        }
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        //获取用户异常订单次数和订单id
        //待优化
        $order = 'abnormal_count  desc';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'count(order_id) as `abnormal_count`,GROUP_CONCAT(`order_id`) as `order_ids`,user_id,user_name';
        $group = 'user_id';
        $result = $this->sys_model_intelligent->getAbnormalOrdersList($condition, $order, $limit, $field, '', $group, $having);
        $total = count($this->sys_model_intelligent->getAbnormalOrdersList($condition, $order, '', $field, '', $group, $having));

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['info_action'] = $this->url->link('operation/AbnormalUser/info', 'order_ids=' . $item['order_ids']);
            }
        }
        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }
        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('total_bicycle', $total);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $page_info = $this->page($total, $page, $rows, $filter, $offset);
        $this->assign('pagination', $page_info['pagination']);
        $this->assign('results', $page_info['results']);

        $this->response->setOutput($this->load->view('intelligent/abnormal_user_list', $this->output));
    }

    /**
     * 用户异常订单列表
     */
    public function info()
    {
        $order_ids = $this->request->get('order_ids');
        $result = [];
        if ($order_ids) {
            $condition['order_id'] = array('in', $order_ids);
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
        $this->setDataColumn('用户');
        $this->setDataColumn('手机号');
        $this->setDataColumn('异常次数');
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