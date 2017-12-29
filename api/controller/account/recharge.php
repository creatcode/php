<?php
class ControllerAccountRecharge extends Controller {
    public function checkRecharge() {
        $user_id = $this->startup_user->userId();
        $recharge_info = $this->db->table('temp_recharge')->where(array('user_id' => $user_id, 'used' => 0))->find();
        $output['has_recharge'] = false;
        if ($recharge_info) {
            $output['has_recharge'] = true;
            $output['recharge_sn'] = $recharge_info['recharge_sn'];
        }
        $this->response->showSuccessResult($output);
    }

    public function freeRecharge() {
        $order_id = $this->request->post['order_sn'];
        $this->load->library('sys_model/orders', true);
        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_sn' => $order_id));
        if (!$order_info) {
            $this->response->showErrorResult('不存在此订单');
        }
        if (!$order_info['recharge_sn']) {
            $this->response->showErrorResult('无需取消');
        }
        //$this->response->showErrorResult('你为什么总是请求我');
        $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('recharge_sn' => '0'));
        if ($update) {
            $this->db->table('temp_recharge')->where(array('recharge_sn' => $order_info['recharge_sn']))->update(array('used' => '0'));
        }
        $this->response->showSuccessResult();
    }

    public function test() {
//        $post = $this->request->post;
        $post = $_POST;
        $accessToken = $this->request->post['token'];
//        unset($post['user_id']);
//        unset($post['sign']);
        unset($post['token']);
        $makeToken = $this->makeToken($post);

        if ($accessToken == $makeToken) {
            $this->response->showSuccessResult(array(), '恭喜你访问成功');
        } else {
            $this->response->showErrorResult('token错误');
        }
    }

    private function makeToken($data, $accessKey = 'tHoPg8i7Kb3oKTLpFJUsWr5kBobcARAg') {
        ksort($data);
        $string = http_build_query($data);
        $string = $string . '&key=' . $accessKey;
        $string = md5($string);
        $string = strtoupper($string);
        return $string;
    }

    public function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }
}
