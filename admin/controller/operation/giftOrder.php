<?php
class ControllerOperationGiftOrder extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载gift Model
        $this->load->library('sys_model/gift', true);
    }

    /**
     * 礼品订单列表
     */
    public function index() {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'gift_orders.*, user.mobile, gift_activity.activity_title';
        $join = array(
            'user' => 'user.user_id=gift_orders.user_id',
            'gift_activity' => 'gift_activity.activity_id=gift_orders.activity_id',
        );
        $states = get_gift_orders_state();
        $result = $this->sys_model_gift->getGiftOrderList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_gift->getTotalGiftOrders($condition);
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['activity_state'] = isset($states[$item['state']]) ? $states[$item['state']] : '';
                $item['activity_title'] = !empty($item['activity_title']) ? $item['activity_title'] : '-';

                $item['shipping_action']   = $this->url->link('operation/giftOrder/shipping', 'order_id='.$item['order_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);
        $this->assign('add_action', $this->url->link('operation/gift/add'));
        $this->assign('return_action', $this->url->link('operation/gift'));


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

        $this->response->setOutput($this->load->view('operation/gift_order_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('活动名称');
        $this->setDataColumn('礼品');
        $this->setDataColumn('用户');
        $this->setDataColumn('兑换数量');
        $this->setDataColumn('状态');
        return $this->data_columns;
    }

    /**
     * 编辑礼品
     */
    public function shipping() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('shipping_company', 'shipping_code', 'remark'));
            $order_id = $this->request->get['order_id'];
            $now = time();
            $condition = array(
                'order_id' => $order_id
            );
            $orderInfo = $this->sys_model_gift->getGiftOrderInfo($condition);
            $condition = array(
                'activity_id' => $orderInfo['activity_id']
            );
            $activityInfo = $this->sys_model_gift->getGiftActivityInfo($condition);
            $condition = array(
                'order_id' => $order_id
            );
            $data = array(
                'shipping_company' => $input['shipping_company'],
                'shipping_code' => (int)$input['shipping_code'],
                'remark' => $input['remark'],
                'shipping_time' => $now,
                'state' => 1
            );
            $res = $this->sys_model_gift->updateGiftOrder($condition, $data);
            if ($res) {
                // TODO 发送系统消息
                $data = array(
                    'user_id' => $orderInfo['user_id'],
                    'msg_image' => !empty($activityInfo['activity_image']) ? (HTTP_IMAGE . $activityInfo['activity_image']): HTTP_IMAGE . 'images/default.jpg'
                );
                $this->addMessage($data);
            }

            $this->session->data['success'] = '礼品发货成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '礼品发货：' . $order_id);

            $this->response->showSuccessResult();
        }
        $this->response->showErrorResult($this->error['warning']);
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('shipping_company', 'shipping_code', 'remark'));

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
     * 添加消息
     * @param $data
     */
    private function addMessage($data) {
        $this->load->library('sys_model/message');
        $now = time();
        $input = array();
        $input['user_id'] = $data['user_id'];
        $input['msg_image'] = $data['msg_image'];
        $input['msg_link'] = 'http://www.baidu.com';            // TODO 礼品订单消息地址
        $input['msg_title'] = '就喜欢免费的！';
        $input['msg_content'] = '小强单车用户大回馈，精美电镀TPU手机壳，免费送不停。您的收货信息已发货，请查看快递信息，注意查询签收';
        $input['msg_abstract'] = '小强单车用户大回馈，精美电镀TPU手机壳，免费送不停。您的收货信息已发货，请查看快递信息，注意查询签收';
        $data = array(
            'user_id' => $input['user_id'],
            'msg_image' => $input['msg_image'],
            'msg_link' => $input['msg_link'],
            'msg_title' => $input['msg_title'],
            'msg_abstract' => $input['msg_abstract'],
            'msg_content' => $input['msg_content'],
            'msg_type' => 0,
            'msg_time' => $now,
        );
        $this->sys_model_message->addMessage($data);
    }
}