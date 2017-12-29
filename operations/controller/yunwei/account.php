<?php
class ControllerYunweiAccount extends Controller {

    /**
     * 获取个人信息
     */
    public function info() {
        $result =  $this->logic_operations->getUserInfo();
        $info = array(
            'nickname' => $result['admin_name'],
        );
        if(!empty($result)) {
            $this->response->showSuccessResult($info, '操作成功');
        } else {
            $this->response->showErrorResult('数据库操作失败', 4);
        }
    }

    /**
     * 更新个人信息（暂时只有更新昵称）
     */
    public function updateInfo() {
        if (!isset($this->request->post['nickname']) || empty($this->request->post['nickname'])) {
            $this->response->showErrorResult('昵称不能为空', 114);
        }

        $user_id = $this->startup_user->userId();
        $result = $this->startup_user->updateUserInfo($user_id, array('nickname'=>$this->request->post['nickname']));
        if ($result['state']) {
            $this->response->showSuccessResult();
        } else {
            $this->response->showErrorResult('数据库操作失败', 4);
        }
    }

    /**
     * 更新个人头像
     */
    public function updateAvatar() {
        $uploader = new \Uploader(
            'avatar',  //字段名
            array( // 配置项
                'allowFiles'=>array('.jpg', '.jpeg', '.png'),
                'maxSize'=>10*1024*1024,
                'pathFormat'=>'avatar/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:4}'
            ),
            empty($this->request->files['avatar']) ? 'base64' : 'upload', //类型，可以是upload，base64或者remote
            $this->request->files //文件上传变量数组，base64的不用提供，内部直接用$_POST[字段名]作为数据
        );

        $fileInfo = $uploader->getFileInfo();
        if($fileInfo['state']=='SUCCESS') {
            $user_id = $this->startup_user->userId();
            $result = $this->startup_user->updateUserInfo($user_id, array('avatar'=>$fileInfo['url']));
            if ($result['state']) {
                $this->response->showSuccessResult(array('user_id'=>$user_id, 'avatar'=>$fileInfo['url']), '操作成功');
            } else {
                $this->response->showErrorResult('数据库操作失败', 4);
            }
        }
        else {
            $this->response->showErrorResult($fileInfo['state'], 5);
        }
    }

    /**
     * 更新手机号码
     */
    public function updateMobile() {
        //能进来到这里都是有userInfo的
        $userInfo = $this->startup_user->getUserInfo();
        $this->log->write(print_r($userInfo, true));
        if (empty($userInfo['verify_state']) //  verify_state=='0'，没有通过实名验证
            || empty($userInfo['real_name']) || empty($userInfo['identification']) ) // 用户实名或者身份证信息为空
        {
            $this->response->showErrorResult('还没通过实名验证，请先进行实名验证',115);
        }

        if (!isset($this->request->post['code']) || empty($this->request->post['code'])) {
            $this->response->showErrorResult('验证码不能为空',116);
        }

        if (!isset($this->request->post['real_name']) || empty($this->request->post['real_name'])) {
            $this->response->showErrorResult('姓名不能为空',117);
        }

        if (!isset($this->request->post['identification']) || empty($this->request->post['identification'])) {
            $this->response->showErrorResult('身份证号码不能为空',118);
        }

        if (!isset($this->request->post['mobile']) || empty($this->request->post['mobile'])) {
            $this->response->showErrorResult('新手机号不能为空',119);
        }

        if (!is_mobile($this->request->post['mobile'])) {
            $this->response->showErrorResult('手机号码不正确',2);
        }

        if (time() < $userInfo['last_update_mobile_time'] + UPDATE_MOBILE_INTERVAL) {
            $this->response->showErrorResult('三个月内只允许更换一次手机号',120);
        }

        $existMobile = $this->startup_user->existMobile($this->request->post['mobile']);
        if($existMobile['state']) {
            $this->response->showErrorResult('手机号码已经存在',121);
        }

        // 验证短信码
        $this->load->library('logic/sms', true);
        if (!$this->logic_sms->disableInvalid($this->request->post['mobile'], $this->request->post['code'], 'register')) {
            $this->response->showErrorResult('短信未验证，或者已失效',3);
        }
        //更新短信的
        $update = $this->logic_sms->enInvalid($this->request->post['mobile'], $this->request->post['code'], 'register');


        if($this->request->post['real_name']!=$userInfo['real_name']) {
            $this->response->showErrorResult('姓名与实名认证的不一致',122);
        }

        if($this->request->post['identification']!=$userInfo['identification']) {
            $this->response->showErrorResult('身份证号码与实名认证的不一致',123);
        }

        $result = $this->startup_user->updateUserInfo($userInfo['user_id'], array(
            'mobile'=>$this->request->post['mobile'],
            'last_update_mobile_time' => time()
        ));

        if ($result['state']) {
            $this->response->showSuccessResult();
        } else {
            $this->response->showErrorResult('数据库操作失败',4);
        }
    }

    /**
     * 获取信用积分记录
     */
    public function getCreditLog() {
        $userInfo = $this->startup_user->getUserInfo();

        $this->load->library('logic/credit', true);

        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;

        $count = $this->logic_credit->getCreditPointsCount($userInfo['user_id']);

        $result = array(
            'credit_point' => $userInfo['credit_point'],
            'total_items_count' => $count,
            'total_pages' => ceil($count/10.0),
            'items' => $this->logic_credit->getCreditPoints($userInfo['user_id'], $page)
        );

        $this->response->showSuccessResult($result);
    }

    /**
     * 获取钱包信息
     */
    public function getWalletInfo() {
        $userInfo = $this->startup_user->getUserInfo();

        $result = array(
            'deposit' => $userInfo['deposit'],  //押金
            'deposit_state' => $userInfo['deposit_state'], //是否已交押金（0未交，1已交）
            'available_deposit' => $userInfo['available_deposit'], //余额
            'freeze_deposit' => $userInfo['freeze_deposit'] //冻结余额
        );
        $this->response->showSuccessResult($result);

    }

    /**
     * 获取钱包明细
     */
    public function getWalletDetail() {
        $userInfo = $this->startup_user->getUserInfo();

        $this->load->library('logic/deposit', true);

        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;

        $count = $this->logic_deposit->getDepositLogCountByUserId($userInfo['user_id']);
        $items = $this->logic_deposit->getDepositLogByUserId($userInfo['user_id'], $page);

        if($items['state']) {
            $result = array(
                'total_items_count' => $count,
                'total_pages' => ceil($count/10.0),
                'items' => $items['data']
            );
            $this->response->showSuccessResult($result);
        }
        else {
            $this->response->showErrorResult('数据库操作失败',4);
        }
    }

    /**
     * 获取我的行程列表
     */
    public function getOrders() {
        $userInfo = $this->startup_user->getUserInfo();

        $this->load->library('logic/orders', true);

        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;

        $count = $this->logic_orders->getOrdersCountByUserId($userInfo['user_id']);
        $items = $this->logic_orders->getOrdersByUserId($userInfo['user_id'], $page);

        $result = array(
            'total_items_count' => $count,
            'total_pages' => ceil($count/10.0),
            'items' => $items
        );
        $this->response->showSuccessResult($result);
    }

    /**
     * 获取行程详情
     */
    public function getOrderDetail() {
        if (!isset($this->request->post['order_id']) || empty($this->request->post['order_id'])) {
            $this->response->showErrorResult('订单id不能为空',124);
        }

        $this->load->library('logic/orders', true);

        $result = $this->logic_orders->getOrderDetail($this->request->post['order_id']);
        $user_info = $this->startup_user->getUserInfo();
        $result['user_info'] = $user_info;
        $this->response->showSuccessResult($result);
    }

    public function getOrderDetailByEncrypt() {
        if (!isset($this->request->post['order_id']) || empty($this->request->post['order_id'])) {
            $this->response->showErrorResult('订单id不能为空',124);
        }

        $encrypt_code = $this->request->post['encrypt_code'];
        $code = decrypt($encrypt_code);
        if (!strpos($code, '_')) {
            $this->response->showErrorResult('解析数据失败');
        }

        $arr = explode('_', $code);
        $user_id = $arr[0];

        $this->load->library('sys_model/user', true);
        $this->load->library('logic/orders', true);

        $result = $this->logic_orders->getOrderDetail($this->request->post['order_id']);
        $user_info = $this->sys_model_user->getUserInfo(array('user_id' => $user_id), 'avatar,nickname,mobile');
        $user_info['mobile'] = substr($user_info['mobile'], 0, 3) . '****' . substr($user_info['mobile'], -4);

        $result['user_info'] = $user_info;
        $this->response->showSuccessResult($result);
    }

    /**
     * 获取我的消息列表
     */
    public function getMessages() {
        $this->load->library('logic/message', true);

        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;

        $count = $this->logic_message->getMessagesCount();
        $items = $this->logic_message->getMessages($page);

        $result = array(
            'total_items_count' => $count,
            'total_pages' => ceil($count/10.0),
            'items' => $items
        );
        $this->response->showSuccessResult($result);
    }

    /**
     * 生成押金充值订单
     */
    public function deposit() {
        $amount = DEPOSIT;
        if (floatval($amount) == 0) {
            $this->response->showErrorResult('充值金额不能为0或空',200);
        }
        $data['type'] = 1;//押金充值
        $data['amount'] = floatval($amount);
        $user_info = $this->startup_user->getUserInfo();
        $data['user_id'] = $user_info['user_id'];
        $data['user_name'] = $user_info['mobile'];
        $this->load->library('logic/deposit', true);
        $this->load->library('logic/user', true);
        $checked = $this->logic_user->checkDeposit($data['user_id']);
        //检测押金是否已交，如果已经交了押金
        if ($checked['state'] == false) {
            $this->response->showErrorResult($checked['msg']);
        }

        $result = $this->logic_deposit->addRecharge($data);
        if ($result['state']) {
            $this->response->showSuccessResult($result['data'], '生成押金充值订单成功');
        } else {
            $this->response->showErrorResult('数据库操作失败，生成押金充值订单失败',4);
        }
    }

    /**
     * 申请退押金
     */
    public function cashApply() {
        $user_info = $this->startup_user->getUserInfo();
        if (!$user_info['deposit_state']) {
            $this->response->showErrorResult('您未充值押金，不能申请退款',201);
        }

        $this->load->library('sys_model/deposit', true);
        $cash_info = $this->sys_model_deposit->getDepositCashInfo(array('pdc_user_id' => $user_info['user_id'], 'pdc_payment_state' => '0'));
        if (!empty($cash_info)) {
            $this->response->showErrorResult('您已经申请了提现，请勿重复提交',202);
        }

        $deposit_recharge = $this->sys_model_deposit->getOneRecharge(array('pdr_user_id' => $user_info['user_id'], 'pdr_type' => 1, 'pdr_payment_state' => 1), '*', 'pdr_add_time DESC');

        if (empty($deposit_recharge)) {
            $this->response->showErrorResult('找不到您充值押金的记录，不能申请退款', 203);
        }

        $result = $this->sys_model_deposit->cashApply($deposit_recharge);
        $result['state'] ? $this->response->showSuccessResult('', '申请成功') : $this->response->showErrorResult($result['msg'],204);
    }

    /**
     * 生成充值订单
     */
    public function charging() {
        $amount = $this->request->post['amount'];
        $amount = floatval($amount);

        if ($amount > MAX_RECHARGE) {
            $this->response->showErrorResult('您输入的金额大于最大充值金额', 205);
        }

        if($amount < MIN_RECHARGE) {
            $this->response->showErrorResult('您输入的金额小于最小充值金额', 206);
        }

        $data['type'] = '0';//普通充值
        $data['amount'] = floatval($amount);
        $user_info = $this->startup_user->getUserInfo();
        $data['user_id'] = $user_info['user_id'];
        $data['user_name'] = $user_info['mobile'];
        $this->load->library('logic/deposit', true);
        $result = $this->logic_deposit->addRecharge($data);
        if (!$result) {
            $this->response->showErrorResult('数据库操作失败，生成支付订单失败', 4);
        }
        $this->response->showSuccessResult($result['data'], '生成充值订单成功');
    }

    /**
     * 实名认证
     */
    public function identity() {
        $data['real_name'] = $this->request->post['real_name'];
        $data['identity'] = $this->request->post['identity'];
        if (empty($data['real_name'])) {
            $this->response->showErrorResult('姓名不能为空', 107);
        }
        if (empty($data['identity'])) {
            $this->response->showErrorResult('证件号码不能为空',108);
        }
        //加入限制，1个身份证正能验证一次
        $exist = $this->startup_user->getUserInfo(array('identification' => $data['identity']));
        if ($exist) {
            $this->response->showErrorResult('此身份证号已被使用',109);
        }

        $user_info = $this->startup_user->getUserInfo();
        if (empty($user_info)) {
            $this->response->showErrorResult('参数错误或缺失',1);
        }
        if (intval($user_info['verify_state']) > 0) {
            $this->response->showErrorResult('用户已实名认证',110);
        }

        if (!intval($user_info['deposit_state'])) {
            $this->response->showErrorResult('您尚未交押金',111);
        }

        $this->load->library('YinHan/YinHan');
        $this->YinHan_YinHan->setIDCondition($data['real_name'], $data['identity']);
        $result = $this->YinHan_YinHan->idCardAuth();
        //判断验证结果
        if (!$result->data) {
            $this->response->showErrorResult($result->msg->codeDesc,112);
        } elseif ($result->data[0]->record[0]->resCode && (string)$result->data[0]->record[0]->resCode != '00') {
            $this->response->showErrorResult($result->data[0]->record[0]->resDesc,112);
        } elseif ($result->data[0]->record[0]->resCode && (string)$result->data[0]->record[0]->resCode == '00') {
            $data['verify_sn'] = $result->header->qryBatchNo;
        }


        $update = $this->startup_user->verify_identity($user_info['user_id'], $data);
        if ($update) {
            $this->load->library('logic/credit', true);
            $this->logic_credit->addCreditPointOnVerification($user_info['user_id']);

            $this->response->showSuccessResult('', '实名认证成功');
        }
        $this->response->showErrorResult('数据库操作失败',4);
    }

    /**
     * 注册推荐码
     */
    public function signRecommend() {
        if (!isset($this->request->post['mobile']) || empty($this->request->post['mobile'])) {
            $this->response->showErrorResult('参数错误或缺失',1);
        }
        $mobile = $this->request->post['mobile'];
        if (!is_mobile($mobile)) {
            $this->response->showErrorResult('手机号码不正确',2);
        }
        $this->load->library('sys_model/user');
        $user_info = $this->sys_model_user->getUserInfo(array('mobile' => $mobile), 'user_id');
        if (empty($user_info)) {
            $this->response->showErrorResult('您的推荐人不存在',113);
        }
        $data = array(
            'recommend_num' => array('exp', 'recommend_num+1'),
            'credit_point' => array('exp', 'credit_point+' . RECOMMEND_POINT)
        );
        $update = $this->sys_model_user->updateUser(array('user_id' => $user_info['user_id']), $data);
        if (!$update) {
            $this->response->showErrorResult('数据库操作失败',4);
        }
        $this->response->showSuccessResult('', '推荐成功');
    }

    /**
     * 退出登录
     */
    public function logout() {
        $user_id = $this->startup_user->userId();
        $this->startup_user->logout($user_id);
        $this->response->showSuccessResult();
    }

    //分享时候用到
    public function getEncryptCode() {
        $user_id = $this->startup_user->userId();
        $time = time();
        $code = $user_id . '_' . $time;
        $encrypt_code = encrypt($code);
        $this->response->showSuccessResult(array('encrypt_code' => $encrypt_code), '生成成功');
    }

    //通过encrypt获取用户的部分信息，无需登录
    public function getUserInfoByEncrypt() {
        if (!isset($this->request->post['encrypt_code'])) {
            $this->response->showErrorResult('参数错误');
        }
        $encrypt_code = $this->request->post['encrypt_code'];
        $code = decrypt($encrypt_code);
        if (!strpos($code, '_')) {
            $this->response->showErrorResult('解析数据失败');
        }
        $arr = explode('_', $code);
        $user_id = $arr[0];
        $this->load->library('sys_model/user');
        $user_info = $this->sys_model_user->getUserInfo(array('user_id' => $user_id), 'avatar,real_name,mobile');
        if (empty($user_info)) {
            $this->response->showErrorResult('获取用户信息失败');
        }

        $user_info['mobile'] = substr($user_info['mobile'], 0, 3) . '****' . substr($user_info['mobile'], -4);
        $this->response->showSuccessResult($user_info, '获取成功');
    }


    /**
     * 上传定位
     * @api
     * @param user_name
     * @param sign
     * @param lng 经度
     * @param lat 纬度
     */
    public function uploadPosition()
    {
        if (!isset($this->request->post['lng']) || empty($this->request->post['lng'])) {
            $this->response->showErrorResult('参数错误或缺失', 1);
        }
        if (!isset($this->request->post['lat']) || empty($this->request->post['lat'])) {
            $this->response->showErrorResult('参数错误或缺失', 1);
        }
		if($this->request->post['lng'] == 0 && $this->request->post['lat'] == 0){
            $this->response->showSuccessResult('', '不保存0点');
        }
        $condition['operator_id'] = $this->startup_user->adminId();
        $data['operator_id'] = $this->startup_user->adminId();
        $data['lng'] = $this->request->post['lng'];
        $data['lat'] = $this->request->post['lat'];
        $data['add_time'] = time();
        $this->load->library('sys_model/operations_position');
        $this->sys_model_operations_position->addOperationsPosition($data);
        $this->response->showSuccessResult('', '添加成功');
    }
	
	/**
     * 获取运维人员一天的轨迹
     * @api
     * @param string $date 例子 2017-08-25
     */
    public function getPosition()
    {
        if (!isset($this->request->post['date']) || empty($this->request->post['date'])) {
            $this->response->showErrorResult('参数错误或缺失', 1);
        }
        if (!isset($this->request->post['admin_id']) || empty($this->request->post['admin_id'])) {
            $this->response->showErrorResult('参数错误或缺失', 1);
        }
        date_default_timezone_set('PRC');
        $this->load->library('sys_model/operations_position');
        $start_time =  strtotime($this->request->post['date']);
        $end_time = $start_time + 24*60*60;
        $condition = [
            'operator_id' => $this->request->post['admin_id'],
            'add_time' => [
                ['gt', $start_time],
                ['elt', $end_time]
            ]
        ];
        $result = $this->sys_model_operations_position->getOperationsPositionList($condition, 'lng,lat,add_time', 'add_time ASC');
        foreach ($result as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        $this->response->showSuccessResult($result);
    }
}