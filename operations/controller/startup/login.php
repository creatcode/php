<?php

/**
 * 判断是否登录，ignore为忽略列表
 * Class ControllerStartupLogin
 */
class ControllerStartupLogin extends Controller {
    public function index() {
        $route  = isset($this->request->get['route']) ? strtolower(trim($this->request->get['route'])) : '';
        $ignore = array(
            'yunwei/login/login',
            'yunwei/aboutme/index',
            'yunwei/location/getbicyclelocation',
            'yunwei/login/dncryption',
            'yunwei/login/encryption',
            'yunwei/login/pudncryption',
            'yunwei/login/puencryption',
            'system/common/version',
        );

        if (!in_array($route, $ignore)) {
            if (!isset($this->request->post['user_name']) || !isset($this->request->post['sign'])) {
                $this->response->showErrorResult('缺少登录参数', 98);
            }
            #验证token
            $this->load->library('logic/operations', true);
            $user_name  = $this->request->post['user_name'];
            $sign       = $this->request->post['sign'];
            #解密处理；

            $result     = $this->logic_operations->checkUserSign(array('user_name' => $user_name), $sign);
            if (!$result['state']) {
                $this->response->showErrorResult($result['msg'], 99);
            }
            # 验证cookie
            if (!isset($_COOKIE["user_id"]) || !isset($_COOKIE["cookie_id"])){
                $this->response->showErrorResult('还没有登录', 99);
            }
            #验证用户一致性 屏蔽非法提交的报错
            if(isset($_POST['user_id']) && (@$_POST['user_id'] != @$_COOKIE['user_id'])){
                $this->response->showErrorResult('非法请求', 99);
            }

            $this->registry->set('startup_user', $this->logic_operations);
        }

    }
}