<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/9
 * Time: 10:25
 */
namespace Logic;
class Sms {
    public function __construct($registry)
    {
        $this->sysmodel_sms = new \Sys_Model\Sms($registry);
        $this->tool_phone_code = new \Tool\Phone_code();
    }

    /**
     * 生成验证码
     * @return string
     */
    public function createVerifyCode() {
        return token(4, 'int');
    }

    /**
     * 发送短信，type为类型，注册或者其他
     * @param $mobile
     * @param $code
     * @param string $type
     * @return bool
     */
    public function sendSms($mobile, $code, $type = 'register') {
        if (empty($code)) {
            return false;
        }
        //第二个参数是数组
//        $result = $this->tool_phone_code->sendSMS($mobile, array($code, SMS_TIMEOUT / 60));
        $result = $this->tool_phone_code->send_sms($mobile, $code);
//        if (!$result) {
//            return false;
//        }
        if (!$result['state']) {
            if ($result['data']['code']) {
                return callback(false, '', array('code'=>160040));
            } else {
                return callback(false);
            }
        }

        $data = array(
            'mobile' => $mobile,
            'code' => $code,
            'type' => $type,
            'add_time' => TIMESTAMP,
            'ip' => getIP()
        );

        $result = $this->sysmodel_sms->addSms($data);
//        return $result;
        return callback(true);
    }

    /**
     * 改变短信的状态
     * @param $mobile
     * @param $code
     * @param $state
     * @param string $type
     * @return bool
     */
    public function changeSmsStatus($mobile, $code, $state, $type = 'register') {
        if (empty($code)) return false;
        return $this->sysmodel_sms->updateSmsStatus(array('mobile' => $mobile, 'code' => $code, 'type' => $type), $state);
    }

    public function enValidated($mobile, $code, $type = 'register') {
        if (empty($code)) return false;
        $result = $this->sysmodel_sms->getSmsInfo(array('mobile' => $mobile,'code' => $code, 'type' => $type));
        if ($result['state'] == 2) {
            return false;
        } elseif ($result['state'] == 1) {
            return true;
        }
        return $this->changeSmsStatus($mobile, $code, 1, $type);
    }

    /**
     * 注册最后一步检测短信是否发送过，并且没有使用
     * @param $mobile
     * @param $code
     * @param string $type
     * @return bool
     */
    public function disableInvalid($mobile, $code, $type = 'register') {
        if (empty($mobile) || empty($code)) return false;
        if($mobile=='15088159005' || $mobile=='13926578916' || $mobile=='18819843907' || $mobile=='13035837339' || $mobile=='13416632205') return true; //苹果测试人员的特别通道。
        $result = $this->sysmodel_sms->getSmsInfo(array('mobile' => $mobile, 'code' => $code, 'type' => $type));
        if (!$result) {
            return false;
        }
        if ($result['state'] != 0) {
            return false;
        }
        return true;
    }

    /**
     * 如果验证码还没有使用 返回真 并且将短信设置为已经使用状态
     * @param $mobile
     * @param $code
     * @param string $type
     * @return bool
     */
    public function enInvalid($mobile, $code, $type = 'register') {
        if($mobile=='15088159005' || $mobile=='13926578916' || $mobile=='18819843907' || $mobile=='13035837339' || $mobile =='13416632205') return true; //苹果测试人员的特别通道。
        if (empty($code)) return false;
        $result = $this->sysmodel_sms->getSmsInfo(array('mobile' => $mobile, 'code' => $code, 'type' => $type));
        if ($result['state'] != 0) {
            return false;
        }
        return $this->changeSmsStatus($mobile, $code, 1, $type);
    }

    public function validateCode($code, $type = 'register') {
        return $this->_dealCode($code, $type, 'enValidated');
    }

    public function submitCode($code, $type = 'register') {
        return $this->_dealCode($code, $type, 'enInvalid');
    }

    public function _dealCode($code, $type = 'register', $method = 'enValidated') {
        if (empty($code)) return false;
        $where = array(
            'code' => $code,
            'type' => $type
        );
        $result = $this->sysmodel_sms->getSmsInfo($where);
        if ($result) {
            if ($result['add_time'] > TIMESTAMP - SMS_TIMEOUT) {
                $update = $this->$method($code, $type);
                if ($update) {
                    return $update;
                }
            }
        }
        return false;
    }

    /**
     * [checkSendSmsLimit 检查发送频率是否]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-27T14:09:28+0800
     */
	public function isOutOfSendLimit($mobile,$type,$ip){
        /*//add vincent : 2017-08-21 增加是否手机访问来源判断
        if($type=='register'){//来源判断只拦截注册短信
            if(!is_mobile_req()){
                //file_put_contents(DIR_LOGS.'/send_code_err'.'.log', '['.date('Y-m-d H:i:s')."] [not from mobile] ".$_SERVER['HTTP_USER_AGENT'].' '.$ip.' '.$type.' '.$mobile."\n",FILE_APPEND);
                return true;
            }
        }*/
        //fix vincemt : 2017-08-25 检查User-Agent是否合法
        /*if(!check_user_agent()){
            return true;
        }*/
		if(empty($mobile) || empty($type) || empty($ip)){
			return true;
		}

		$where['mobile']	= $mobile;
		$where['type']		= $type;
		$now_time			= time();

		//检查一分钟内是否有发送
		$start_time 		= $now_time - 60;
		$where['add_time']	= array('between',array($start_time,$now_time));
		$res 	= $this->sysmodel_sms->getSmsTotal($where);
		if($res) return true;

		//检查一小时内是否超过发送次数:一小时不超过3次
		$start_time 		= $now_time - 60 * 60;
		$where['add_time']	= array('between',array($start_time,$now_time));
		$res 	= $this->sysmodel_sms->getSmsTotal($where);
		if($res >= 3) return true;

		//检查一天内是否超过发送次数:一天不超过10次
		$start_time 		= $now_time - 60 * 60 * 24;
		$where['add_time']	= array('between',array($start_time,$now_time));
		$res 	= $this->sysmodel_sms->getSmsTotal($where);
		if($res >= 10) return true;

		//同一ip十分钟只能发送100次
		unset($where['mobile']);
		$start_time 		= $now_time - 60 * 60;
		$where['add_time']	= array('between',array($start_time,$now_time));
		$where['ip']		= $ip;
		$res 	= $this->sysmodel_sms->getSmsTotal($where);
/*        if($type=='register'){//注册类短信只开放2条
            if($res >= 2) return true;
        }
*/		if($res >= 100) return true;

		return false;
	}
}
