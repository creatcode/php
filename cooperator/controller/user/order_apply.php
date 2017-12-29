<?php
class ControllerUserOrderApply  extends Controller {
    private $cur_url = null;
    private $error = null;
    private $cooperator_id = 0;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载log Model
        $this->load->library('sys_model/orders', true);

        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');
    }

    /**
     * 申请列表
     */
    public function index() {
        $filter = $this->request->get(array('apply_user_name', 'order_sn', 'apply_add_time', 'apply_audit_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id,
            'apply_state' => 1
        );
        if (!empty($filter['apply_user_name'])) {
            $condition['apply_user_name'] = array('like', "%{$filter['apply_user_name']}%");
        }
        if (!empty($filter['order_sn'])) {
            $condition['order_sn'] = $filter['order_sn'];
        }
        if (!empty($filter['apply_add_time'])) {
            $apply_add_time = explode(' 至 ', $filter['apply_add_time']);
            $condition['apply_add_time'] = array(
                array('egt', strtotime($apply_add_time[0])),
                array('elt', bcadd(86399, strtotime($apply_add_time[1])))
            );
        }
        if (!empty($filter['apply_audit_time'])) {
            $apply_audit_time = explode(' 至 ', $filter['apply_audit_time']);
            $condition['apply_audit_time'] = array(
                array('egt', strtotime($apply_audit_time[0])),
                array('elt', bcadd(86399, strtotime($apply_audit_time[1])))
            );
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'apply_add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'orders_apply.*';
        $join = array(
            'orders' => 'orders.order_sn=orders_apply.order_sn',
        );
        $result = $this->sys_model_orders->getOrderApplyList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_orders->getTotalOrderApply($condition, $join);

        $apply_states = get_apply_states();
        // 是否拥有审核权限
        $show_audit_action = $this->logic_admin->hasPermission('user/refund_apply/audit');
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item = array(
                    'apply_user_name' => $item['apply_user_name'],
                    'order_sn' => $item['order_sn'],
                    'apply_cash_amount' => $item['apply_cash_amount'],
                    'apply_cash_reason' => $item['apply_cash_reason'],
                    'apply_audit_result' => $item['apply_audit_result'],
                    'apply_audit_time' => !empty($item['apply_audit_time']) ? date('Y-m-d H:i:s', $item['apply_audit_time']) : '',
                    'audit_action' => $show_audit_action && $item['apply_state'] == 0 ? $this->url->link('user/order_apply/audit', 'apply_id='. $item['apply_id']) : ''
                );
            }
        }

        $filter_types = array(
            'apply_user_name' => '用户名称',
            'order_sn' => '订单sn',
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('apply_states', $apply_states);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);

        $order_filter = array('order_state'=>2);
        $this->assign('add_action', $this->url->link('user/order', $order_filter, true));

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

        $this->assign('export_action', $this->url->link('user/order_apply/export'));

        $this->response->setOutput($this->load->view('user/order_apply_list', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('apply_user_name', 'order_sn', 'apply_add_time', 'apply_audit_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id,
            'apply_state' => 1
        );
        if (!empty($filter['apply_user_name'])) {
            $condition['apply_user_name'] = array('like', "%{$filter['apply_user_name']}%");
        }
        if (!empty($filter['order_sn'])) {
            $condition['order_sn'] = $filter['order_sn'];
        }
        if (!empty($filter['apply_add_time'])) {
            $apply_add_time = explode(' 至 ', $filter['apply_add_time']);
            $condition['apply_add_time'] = array(
                array('egt', strtotime($apply_add_time[0])),
                array('elt', bcadd(86399, strtotime($apply_add_time[1])))
            );
        }
        if (!empty($filter['apply_audit_time'])) {
            $apply_audit_time = explode(' 至 ', $filter['apply_audit_time']);
            $condition['apply_audit_time'] = array(
                array('egt', strtotime($apply_audit_time[0])),
                array('elt', bcadd(86399, strtotime($apply_audit_time[1])))
            );
        }

        $order = 'apply_id DESC';
        $limi = '';
        $field = 'orders_apply.*';
        $join = array(
            'orders' => 'orders.order_sn=orders_apply.order_sn',
        );
        $result = $this->sys_model_orders->getOrderApplyList($condition, $order, $limi, $field, $join);
        echo $this->db->getLastSql();
        $apply_states = get_apply_states();
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'apply_user_name' => $v['apply_user_name'],
                    'order_sn' => $v['order_sn'],
                    'apply_cash_amount' => $v['apply_cash_amount'],
                    'apply_cash_reason' => $v['apply_cash_reason'],
                    'apply_add_time' => !empty($v['apply_add_time']) ? date('Y-m-d H:i:s', $v['apply_add_time']) : '',
                    'apply_audit_result' => $v['apply_audit_result'],
                    'apply_audit_time' => !empty($v['apply_audit_time']) ? date('Y-m-d H:i:s', $v['apply_audit_time']) : '',
                );
            }
        }

        $data = array(
            'title' => '订单修改金额申请',
            'header' => array(
                'apply_user_name' => '用户名称',
                'order_sn' => '订单sn',
                'apply_cash_amount' => '金额',
                'apply_cash_reason' => '申请理由',
                'apply_audit_time' => '审核时间',
                'apply_audit_result' => '审核结果',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('用户名称');
        $this->setDataColumn('订单sn');
        $this->setDataColumn('金额');
        $this->setDataColumn('申请理由');
        $this->setDataColumn('审核时间');
        $this->setDataColumn('审核结果');
        return $this->data_columns;
    }
}