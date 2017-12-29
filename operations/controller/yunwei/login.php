<?php

/**
 * 判断是否登录，ignore为忽略列表
 * Class ControllerStartupLogin
 */
class ControllerYunWeiLogin extends Controller
{
    public function index()
    {


    }

    /**
     * 登录，数据库操作
     * @param $cooperator_name
     * @param $password
     * @return mixed
     */
    function checklogin($admin_name, $password) {

        $rec = $this->logic_operations->login($admin_name, $password);
        return $rec['state'];

    }

    public function login() {

        if (!isset($this->request->post['user_name']) || !isset($this->request->post['uuid']) || !isset($this->request->post['password'])) {
            $this->response->showErrorResult('参数错误或缺失', 1);
        }
        $user_name = trim($this->request->post['user_name']);
        $device_id = $this->request->post['uuid'];
        $password = $this->request->post['password'];
        if (empty($user_name)) {
            $this->response->showErrorResult('账号不能为空', 103);
        }
        if (empty($password)) {
            $this->response->showErrorResult('密码不能为空', 104);
        }
        if (empty($device_id)) {
            $this->response->showErrorResult('设备ID不能空', 105);
        }
        $this->load->library('logic/operations', true);
        $result = $this->logic_operations->login($user_name, $password, $device_id);
        if (!$result['state']) {
            $this->response->showErrorResult($result['msg'], 106);
        }

        # 处理cookie
        $expire     = 60 * 60 * 24 * 30 * 12;
        $cookie_key = "xiaoqing_bike_2017";
        $cookie_id  = md5($result['data']['admin_id'] . $cookie_key);
        setcookie("user_id", $result['data']['admin_id'], time() + $expire, '/');
        setcookie("cookie_id", $cookie_id, time() + $expire, '/');

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

    public function encryption(){
        #$data, $key = "public", $crypt = "en"
        #$arr['data'] = "Whe8kGhh+14Or/voCwpVYNAH8hnMb6bwe5kkR2tg4c26skoEBvH6M2oKmCUToyROYgLnqaWXp1YLD2Wy208txeprgbBYRIYjtKv5wEdo24T2PwRnHsIsYDtMXMQHZwDrsBt4iPz+eXX/XlZ322QsIwBWy+Faj08JjRnFDuB36D0=";
        $arr['data'] = "weihu";
        $arr['key'] = "private";
        $arr['crypt'] = "en";
        $result = $this->load->controller("common/base/rsa",$arr);

        $this->response->showSuccessResult($result, "success");
    }

    public function dncryption(){
        #$data, $key = "public", $crypt = "en"
        if(!isset( $this->request->post['data']) ||  $this->request->post['data'] == ''){
            $this->response->showErrorResult('缺少参数', 110);
        }
        $arr['data'] = $this->request->post['data'];
        file_put_contents("test.txt",$this->request->post['data'],FILE_APPEND);
        #$arr['data'] = "kq6/1gznJ7B8kiGXCAntRz06CAefRdgrXXV3s/k01Tau7QtlY99oFPYWqghwFtH2oX4XJ3qzSuNNHW7JlQGWUqWEH6NHAAIqdP0lj5yYZ7Pnlon04ZYgiUIBvZlqnJlggmCJT19zQ19EMUnWTdbvBaV1+FucIzGd8+oBLCvEEZ0=";
        #$arr['data'] = "weihu";
        $arr['key'] = "public";
        $arr['crypt'] = "dn";
        $parr = array();
        $result = $this->load->controller("common/base/rsa",$arr);
        #$parr['pdata'] = $arr['data'];
        #$parr['wwww'] = 113;
        $parr['data'] = $result;
        $this->response->showSuccessResult($parr, "success");

    }

    public function puencryption(){
        #$data, $key = "public", $crypt = "en"
        #$arr['data'] = "Whe8kGhh+14Or/voCwpVYNAH8hnMb6bwe5kkR2tg4c26skoEBvH6M2oKmCUToyROYgLnqaWXp1YLD2Wy208txeprgbBYRIYjtKv5wEdo24T2PwRnHsIsYDtMXMQHZwDrsBt4iPz+eXX/XlZ322QsIwBWy+Faj08JjRnFDuB36D0=";
        $arr['data'] = "这是一段将要使用'.der'文件加密的字符串!";
        $arr['key'] = "public";
        $arr['crypt'] = "en";
        $result = $this->load->controller("common/base/rsa",$arr);

        $this->response->showSuccessResult($result, "success");
    }


    public function pudncryption(){
        #$data, $key = "public", $crypt = "en"
        if(!isset( $this->request->post['data']) ||  $this->request->post['data'] == ''){
            $this->response->showErrorResult('缺少参数', 110);
        }
        $arr['data'] = $this->request->post['data'];
        $result['pdata'] = $arr['data'];
        #$arr['data'] = "kq6/1gznJ7B8kiGXCAntRz06CAefRdgrXXV3s/k01Tau7QtlY99oFPYWqghwFtH2oX4XJ3qzSuNNHW7JlQGWUqWEH6NHAAIqdP0lj5yYZ7Pnlon04ZYgiUIBvZlqnJlggmCJT19zQ19EMUnWTdbvBaV1+FucIzGd8+oBLCvEEZ0=";
        #$arr['data'] = "weihu";
        $arr['key'] = "private";
        $arr['crypt'] = "dn";
        $result = $this->load->controller("common/base/rsa",$arr);
        $this->response->showSuccessResult($result, "success");

    }

}