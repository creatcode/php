<?php

/**
 * 判断是否登录，ignore为忽略列表
 * Class ControllerStartupLogin
 */
class ControllerYunWeiAboutMe extends Controller
{

    /**
     * 获取联系方式
     */

    public function index()
    {
        #$this->load->language("");
        #var_dump($this->language);
        #echo $this->language->get('text_minute');  exit;
        #echo md5("4MB95O7NF7HVGDQ8L");
        $company_tell_phone = $this->config->get('config_phone');  #公司电话
        if(isset($_COOKIE["user_id"]) && $_COOKIE["user_id"] != ''){
            $user_info = $this->load->controller('common/base/getAdminInfoById',$_COOKIE["user_id"]);
            #若是平台的则可以看到全部
            if($user_info['cooperator_id'] != 0){
                $this->load->library('sys_model/cooperator');
                $cooperInfo = $this->sys_model_cooperator->getCooperatorInfo(array('cooperator_id' => $user_info['cooperator_id']));
                $company_tell_phone = $cooperInfo['mobile'] ? $cooperInfo['mobile'] : $this->config->get('config_phone'); #合伙人电话
            }
        }
        $data = array(
            'wechat' => '小强单车',
            'phone' => $company_tell_phone,
            'email' => 'service@s-bike.cn',
            'web' => 'http://www.s-bike.cn'
        );
        $this->response->showSuccessResult($data,'关于我们信息');

    }


}