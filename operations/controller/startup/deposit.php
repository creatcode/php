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

        if (in_array($route, $in_array)) {
            $user_info = $this->startup_user->getUserInfo();
            if (empty($user_info)) {
                $this->response->showErrorResult('用户信息为空');
            }
            if ($user_info['deposit_state'] == INIT_STATE) {
                $this->response->showErrorResult('用户尚未交押金，不能开锁骑车，请交押金', 1);
            }
            if ($user_info['verify_state'] == INIT_STATE) {
                $this->response->showErrorResult('用户尚未实名认证，不能开锁骑车，请实名认证后再试', 2);
            }
            if ($user_info['available_deposit'] == 0) {
                $this->response->showErrorResult('您的余额不足，不能开锁骑车，请充值', 3);
            }
        }
    }
}