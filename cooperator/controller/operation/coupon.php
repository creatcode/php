<?php
class ControllerOperationCoupon extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载coupon Model
        $this->load->library('sys_model/coupon', true);
        $this->load->library('sys_model/user', true);
    }

    /**
     * 优惠券列表
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

        $result = $this->sys_model_coupon->getCouponList($condition, $order, $limit);
        $total = $this->sys_model_coupon->getTotalCoupons($condition);

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $coupon_name = '';
                switch ($item['coupon_type']) {
                    case '1' :
                        $coupon_name = sprintf('%d分钟用车券', $item['number']);
                        break;
                    case '2' :
                        $coupon_name = '单次体验券';
                        break;
                    case '3' :
                        $coupon_name = sprintf('%d元代金券', $item['number']);
                        break;
                }
                $item['coupon_name'] = $coupon_name;

                $item['effective_time'] = !empty($item['effective_time']) ? date('Y-m-d', $item['effective_time']) : '';
                $item['failure_time'] = !empty($item['failure_time']) ? date('Y-m-d', $item['failure_time']) : '';


                $item['edit_action'] = $this->url->link('operation/coupon/edit', 'coupon_id='.$item['coupon_id']);
                $item['delete_action'] = $this->url->link('operation/coupon/delete', 'coupon_id='.$item['coupon_id']);
                $item['info_action'] = $this->url->link('operation/coupon/info', 'coupon_id='.$item['coupon_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('operation/coupon/add'));

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

        $this->assign('export_action', $this->url->link('operation/violation/export'));

        $this->response->setOutput($this->load->view('operation/coupon_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('用户名称');
        $this->setDataColumn('优惠券号码');
        $this->setDataColumn('优惠券名称');
        $this->setDataColumn('生效时间');
        $this->setDataColumn('失效时间');
        return $this->data_columns;
    }

    /**
     * 添加优惠券
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('coupon_type', 'number', 'valid_time', 'mobiles'));
            $now = time();

            $data = array(
                'used' => 0,
                'add_time' => $now,
                'obtain' => 0,
            );
            // 有效时间
            $valid_time = explode(' 至 ', $input['valid_time']);
            if (is_array($valid_time) && !empty($valid_time)) {
                $data['effective_time'] = strtotime($valid_time[0] . ' 00:00:00');
                $data['failure_time'] = strtotime($valid_time[1] . ' 23:59:59');
            }

            $data['coupon_type'] = $input['coupon_type'];
            $data['number'] = $input['number'];
            if ($input['coupon_type'] == 2) {
                $data['number'] = 1;
            }

            // 优惠券类型
            if ($data['coupon_type'] == 1) {
                $data['left_time'] = $data['number'];
            } else {
                $data['left_time'] = 1;
            }

            // 派发用户
            $mobiles = explode(PHP_EOL, $input['mobiles']);
            if (is_array($mobiles) && !empty($mobiles)) {
                foreach ($mobiles as $mobile) {
                    $condition = array(
                        'mobile' => $mobile
                    );
                    $user = $this->sys_model_user->getUserInfo($condition, 'user_id');
                    if ($user) {
                        $data['user_id'] = $user['user_id'];
                        $data['coupon_code'] = $this->buildCouponCode();
                        $this->sys_model_coupon->addCoupon($data);
                    }
                }
            }

            $this->session->data['success'] = '添加优惠券成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：添加优惠券,派发的用户手机号码：'.$input['mobiles']."，优惠券时间：".$input['valid_time'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 9,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);
            
            $filter = array();

            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }

        $this->assign('title', '优惠券添加');
        $this->getForm();
    }

//    /**
//     * 导出
//     */
//    public function export() {
//        $ids = $this->request->post("selected");
//
//        $condition = array(
//            'coupon_id' => array('in', $ids)
//        );
//        $order = 'add_time DESC';
//        $limit = '';
//
//        $result = $this->sys_model_coupon->getCouponList($condition, $order, $limit);
//        $list = array();
//        if (is_array($result) && !empty($result)) {
//            foreach ($result as $v) {
//                $list[] = array(
//                    'user_name' => $v['user_name'],
//                    'coupon_code' => $v['coupon_code'],
//                    'description' => $v['description'],
//                    'effective_time' => date("Y-m-d",$v['effective_time']),
//                    'failure_time' => date("Y-m-d",$v['failure_time']),
//                );
//            }
//        }
//
//        $data = array(
//            'title' => '违规停放列表',
//            'header' => array(
//                'user_name' => '用户名称',
//                'coupon_code' => '优惠券号码',
//                'description' => '优惠券名称',
//                'effective_time' => '生效时间',
//                'failure_time' => '失效时间',
//            ),
//            'list' => $list
//        );
//        $this->load->controller('common/base/exportExcel', $data);
//    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('coupon_type', 'number', 'valid_time', 'mobiles'));
        $coupon_id = $this->request->get('coupon_id');
        if (isset($this->request->get['coupon_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'coupon_id' => $this->request->get['coupon_id']
            );
            $info = $this->sys_model_coupon->getCouponInfo($condition);
        }

        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&coupon_id=' . $coupon_id);
        $this->assign('return_action', $this->url->link('operation/coupon'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('operation/coupon_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('coupon_type', 'valid_time', 'mobiles'));

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
     * 生成优惠券唯一码
     */
    private function buildCouponCode() {
        $coupon_code = token(32);
        $condition = array(
            'coupon_code' => $coupon_code,
            'used' => 0
        );
        $total = $this->sys_model_coupon->getTotalCoupons($condition);
        if ($total == 0) {
            return $coupon_code;
        } else {
            return self::buildCouponCode();
        }
    }
}