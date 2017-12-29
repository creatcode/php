<?php

/**
 * 判断是否登录，ignore为忽略列表
 * Class ControllerStartupLogin
 */
class ControllerYunWeiReport extends Controller
{

    /**
     * 获取联系方式
     */

    public function index()
    {
        $user_id = $this->request->post['user_id'];
        $sign = $this->request->post['sign'];
        $data['user_id'] = $user_id;
        $check_result = $this->logic_user->checkUserSign($data, $sign);
        if(!$check_result['state']){
            $this->response->showErrorResult('未登录请先登录', 98);
        }

        # 获取改维护人员的维修记录；


        $this->response->showSuccessResult($data,'关于我们信息');

    }


}