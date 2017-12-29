<?php
class ControllerAccountLogin extends Controller {
    public function login() {
        $post = $this->request->post(array('user_name', 'uuid', 'password', 'password'));
        if (!$post['user_name'] || !$post['uuid'] || !$post['password']) {
            $this->response->showErrorResult('参数错误');
        }
        $user_name = trim($post['user_name']);
        $device_id = $post['uuid'];
        $password = $post['password'];

        $this->load->library('logic/operations', true);
        $result = $this->logic_operations->login($user_name, $password, $device_id);
        if (!$result['state']) {
            $this->response->showErrorResult($result['msg'], 106);
        }

        $expire     = 60 * 60 * 24 * 30 * 12;
        $cookie_key = "xiaoqing_bike_2017";
        $cookie_id  = md5($result['data']['admin_id'] . $cookie_key);
        setcookie("user_id", $result['data']['admin_id'], time() + $expire, '/');
        setcookie("cookie_id", $cookie_id, time() + $expire, '/');
        $result['data']['cook_user_id'] = isset($_COOKIE["user_id"]) ? $_COOKIE["user_id"] : '';
        $result['data']['cook_cookie_id'] = isset($_COOKIE["cookie_id"]) ? $_COOKIE["cookie_id"] : '';
        $this->response->showSuccessResult($result['data'], $result['msg']);
    }

    /**
     * 退出登录
     */
    public function logout() {
        $result = $this->startup_user->logout();
        if($result['state']){
            $this->response->showSuccessResult(array(), "成功退出登录");
        }else{
            $this->response->showErrorResult('退出登录失败', 110);
        }
    }
}