<?php

class ControllerTransferOperator extends Controller
{
    /**
     * 指令回调地址
     */
    public function propelling()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $post = file_get_contents("php://input");

            if (empty($post)) {
                die('empty post');
            }
            $this->request->post = json_decode($post, true);
            if($this->request->post['deviceid']=='063072632892' || $this->request->post['deviceid']=='063072619956' || $this->request->post['deviceid']=='063072649805'){
                file_put_contents('/data/wwwroot/default/bike/locktest.log', date('Y-m-d H:i:s - ') . 'propelling:' . print_r($this->request->post, true) , FILE_APPEND);
            }
#            file_put_contents('aaa.log', print_r($this->request->post, true), FILE_APPEND);
            $user_id = $this->request->post['userid'];
            $cmd = $this->request->post['cmd'];
            $device_id = $this->request->post['deviceid'];
            $result = $this->request->post['result'];
            $info = $this->request->post['info'];
            $serialnum = $this->request->post['serialnum'];
            $open_time = time();
            $sign = $this->request->post['sign'];

            $data = array (
                'cmd' => $cmd,
                'cooperator_id' => $user_id,
                'device_id' => $device_id,
                'result' => $result,
                'info' => $info,
                'serialnum' => $serialnum,
                'open_time' => $open_time
            );

            $this->load->library('logic/orders', true);
            //接收指令回调

            $this->load->library('sys_model/instruction', true);
            $result = $this->sys_model_instruction->addInstructionRecord($data);

            switch (strtolower($data['cmd'])) {
                case 'open':
                    //如果指令发送失败就取消订单，指令成功才是订单生效
                    if (strtolower($data['result']) == 'fail') {
//                        $result = $this->logic_orders->cancelOrders($data);
                    } elseif (strtolower($data['result']) == 'ok') {
                        $result = $this->logic_orders->effectOrders($data);
                        if ($result['state'] == true) {
                            $arr = $this->response->_error['success'];
                            $arr['data'] = $result['data'];
                            $this->load->library('JPush/JPush', true);
//                            $send_result = $this->JPush_JPush->message($result['data']['user_id'], json_encode($arr));
                        }
                    }
                    break;
                case 'select':
                    break;
                case 'close':
                    break;
                case 'beep':
                    break;
            }

            if ($result['state']) {
                $this->response->showSuccessResult('', 'operator success!');
            } else {
                $this->response->showErrorResult($result['msg']);
            }
        } else {
            $this->response->showErrorResult('Request require post!');
        }
    }
}
