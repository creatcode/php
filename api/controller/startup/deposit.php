<?php
/**
 * 检测用户是否交押金
 */
class ControllerStartupDeposit extends Controller {
    /**
     * 检测是否交押金
     */
    public function index() {
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
        $route = strtolower($route);

        $in_array = array(
            'operator/operator/openlock',
            'account/order/book'
        );

        $order_array = array(
            'operator/operator/openlock',
            'account/order/book',
            'account/order/current',
            'account/account/getorderdetail',
            'account/order/getorderinfo',
        );

        if (in_array($route, $order_array)) {
            $user_id = $this->startup_user->userId();
            $this->load->library('sys_model/orders', true);
            $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $user_id, 'order_state' => -2, 'add_time' => array('elt', time() - $this->config->get('config_cancel_under_riding_time') + 0)));
            if ($order_info) {
                $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('order_state' => -1));
            }
        }

        if (in_array($route, $in_array)) {
            $user_info = $this->startup_user->getUserInfo();
            if (empty($user_info)) {
                $this->response->showErrorResult('用户信息为空');
            }
            //微信专版
            if ($this->request->get_request_header('sing') == 'BBC') {

            } elseif ($user_info['deposit_state'] == INIT_STATE) {
                $this->response->showErrorResult('用户尚未交押金，不能开锁骑车，请交押金', 1);
            }
            if ($user_info['verify_state'] == INIT_STATE) {
                $this->response->showErrorResult('用户尚未实名认证，不能开锁骑车，请实名认证后再试', 2);
            }
            if ($user_info['available_deposit'] + $user_info['present_amount'] <= 0) {
                if ($user_info['card_expired_time'] < time()) {
                    $this->response->showErrorResult('您的余额不足，请充值余额再使用单车', 3);
                } else {
                    if ($user_info['available_deposit'] + $user_info['present_amount'] < 0) {
                        $this->response->showErrorResult('使用骑行卡，但骑行有欠费，请联系客服查明原因');
                    }
                }
            }
            
            if ($this->request->get_request_header('sing') == 'BBC') {

            } elseif ($user_info['available_state'] == INIT_STATE) {
                $this->response->showErrorResult('余额不足或未交押金');
            }
            if ($user_info['is_freeze'] == 1) {
                $this->response->showErrorResult('您的账户暂时被冻结');
            }
        }
    }
}
