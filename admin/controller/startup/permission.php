<?php
class ControllerStartupPermission extends Controller {
	
	public function index() {
		if (isset($this->request->get['route'])) {
			$action = $this->request->get['route'];
            // 以下是不进行权限控制的操作，包括：
            // 1、登录、登出、忘记密码、重置密码
            // 2、通用的错误处理（页面找不到和没有权限）
            // 3、所有用户都能进行的操作（例如个人中心）
            // 4、为实现业务操作而必须的内部操作（含依赖于业务操作的内部动作，例如加载选项内容、加载自动完成项）
            // 5、由第三方代码调用的操作（如ueditor，第三方支付）
			$ignore = array(
                // 1、登录、登出、忘记密码、重置密码
				'common/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'common/qrcode',
                'common/upload',
                'admin/index',
                //支付宝回调地址
                'payment/alipay',
                'payment/alipay/index',
                'payment/alipay/refund',
                'payment/alipayutil/refund_check',
                //微信回调地址
                'payment/wxpay/index',
                'system/test',

                //为实现业务操作而必须的内部操作
                'admin/index/apiGetMarker',
                'admin/index/apiGetFaults',
                'admin/index/apiGetNormalParking',
                'admin/index/apiGetIllegalParking',
                'admin/index/apiGetFeekbacks',
                'admin/index/apiGetUsedHistory',
                'admin/index/apiHeatmapData',
                'admin/index/lockInfo',
                'admin/index/search',
                'admin/index/cooperator',
                'admin/index/apiGetUsageCount',
                'user/user/mass_sms',
                'admin/index/userInfo',
                'admin/index/cashapply',
                'admin/index/coupon',
                'admin/index/points',
                'admin/index/order',
                'admin/index/couponAdd',
                'admin/index/refund',
                'admin/index/finishOrder1',

                //运维人员管理
                'operation/operationLocation/apiGetMarker',
                'operation/operationLocation/openLockRecords',
                'operation/operationLocation/getOperators',
                'operation/operationLocation/handleFaultList',
                'operation/operationLocation/getOperatorsPosition',

                // for test only
                'advertisement/list'
            );

			if (!in_array($action, $ignore) && !$this->logic_admin->hasPermission($action)) {
				return new Action('error/permission');
			}
		}
	}
}
