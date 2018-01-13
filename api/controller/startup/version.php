<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2017/2/24
 * Time: 9:24
 */
class ControllerStartupVersion extends Controller {
    public function index() {
//        $ignore = array(
//            'system/common/version',
//            'payment/alipay/notify',
//            'payment/alipay',
//            'payment/wxpay/notify',
//            'payment/wxpay'
//        );
//        $route = strtolower($this->request->get['route']);
//        if (!isset($this->request->get['version']) && !in_array(strtolower($this->request->get['route']), $ignore)) {
//            $this->response->showErrorResult('请在url上附带上version参数');
//        }
//
//        if (!isset($this->request->get['version'])) {
//            $version = 1;
//        } else {
//            $version = $this->request->get['version'];
//        }
//
//        if ($version < VERSION_FAIL && !in_array(strtolower($this->request->get['route']), $ignore)) {
//            $this->response->showErrorResult('版本过低请升级后使用', 1024);
//        }

        //需要传锁ID的地方
        $in = array(
            'operator/operator/openlock',
            'operator/operator/beeplock',
            'operator/operator/lockposition',
            'account/order/book',
        );
	//调试阶段 不需要判断版本
	return;

        $route = $this->request->get['route'];
        $route = strtolower($route);
	
	//测试阶段不做版本比较 不知道为什么这里会有点错误 后面再调试
	return;
	
        if (in_array($route, $in)) {
            $this->load->library('sys_model/bicycle');
            $this->load->library('sys_model/region');
            $device_id = $this->request->post['device_id'];
            $region_city_code = $region_city_ranking = $bicycle_sn = '';
            if (strlen($device_id) == 11) {
                sscanf($device_id, '%03d%02d%06d', $region_city_code, $region_city_ranking, $bicycle_sn);
                $bicycle_sn = sprintf('%06d', $bicycle_sn);
            }
            $condition = array(
                'region_city_code' => $region_city_code,
                'region_city_ranking' => $region_city_ranking
            );
            $region = $this->sys_model_region->getRegionInfo($condition);

            $condition = array(
                'bicycle_sn' => $bicycle_sn,
                //'region_id' => $region['region_id']
            );

            $device_info = $this->sys_model_bicycle->getBicycleInfo($condition, 'bicycle_id, bicycle_sn, lock_sn, is_scenic');
            if (empty($device_info)) {
                $this->response->showErrorResult('系统不存在此单车编号');
            }
            if (empty($device_info['lock_sn'])) {
                $this->response->showErrorResult('此单车未绑定锁');
            }
            //如果是景区单车
            if ($this->request->get_request_header('sing') == 'BBC' && $device_info['is_scenic'] && $this->request->get_request_header('client') == 'miniapp') {
                $recharge_sn = $this->request->post['recharge_sn'];
                if (!$recharge_sn) {
                    $this->response->showErrorResult('景区单车先充值', 201314);
                }
		
                $this->load->library('sys_model/deposit');
                //$this->response->showErrorResult('fuck');
                $recharge_info = $this->sys_model_deposit->getOneRecharge(array('pdr_sn' => $recharge_sn));
		
                if (!$recharge_info) {
                    $this->response->showErrorResult('不存在此充值号', 201314);
                }

                if (!$recharge_info['pdr_payment_state']) {
                    $this->response->showErrorResult('充值等待确认中', 201314);
                }

                $this->load->library('sys_model/orders');
                $order_info = $this->sys_model_orders->getOrdersInfo(array('recharge_sn' => $recharge_sn));
                if ($order_info) {
                    $this->response->showErrorResult('请重新充值', 201314);
                }
		//$this->response->showErrorResult('能跑到最后');
            } elseif ($this->request->get_request_header('client') == 'miniapp' && $this->request->get_request_header('sing') == 'BBC' && !$device_info['is_scenic']) {
                $this->response->showErrorResult('该二维码不属于景区二维码');
            }
                        

            $this->request->post['bicycle_id'] = $device_info['bicycle_id'];
            $this->request->post['bicycle_sn'] = $this->request->post['device_id'];
            $this->request->post['device_id'] = $device_info['lock_sn'];
            $this->request->post['is_scenic'] = $device_info['is_scenic'];
        }

        $in = array(
            'account/account/info',
            'operator/operator/openlock',
            'account/account/sendregistercode'
        );

        if ($this->config->get('config_forced_to_update')) {
            if (in_array($route, $in)) {
                if ($this->request->get_request_header('client') == 'wechat' || $this->request->get_request_header('client') == 'miniapp') {

                } else {
                    $gets = $this->request->get(array('fromApi', 'version'));
                    if ($gets['fromApi'] == 'android') {
                        if ($gets['version'] < $this->config->get('config_android_fail_version')) {
                            $this->response->showErrorResult('请更新版本', 1024);
                        }
                    } elseif ($gets['fromApi'] == 'ios') {
                        if ($gets['version'] < $this->config->get('config_ios_fail_version')) {
                            //if ($route == 'account/account/sendregistercode')
                                //$this->response->showErrorResult('请更新版本', 1024);
                        }
                    } else {
                        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')|| strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){

                        } else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
                            if ($gets['version'] < $this->config->get('config_android_fail_version')) {
                                $this->response->showErrorResult('请更新版本', 1024);
                            }
                        }
                    }
                }
            }
        }
    }
}
