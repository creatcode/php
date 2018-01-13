<?php
use Tool\Email;
class ControllerAccountCreditcard extends Controller {

    public function __construct($registry) {
        parent::__construct($registry);
        $this->sys_model_creditcard = new \Sys_Model\Credit_Card($registry);
    }

    /**
     * 用户信用卡列表
     */
    public function card_list() {
        $user_id = $this->startup_user->userId();
        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;
        $limit = ($page - 1) . ',1';
        $condition = array(
            'user_id' => $user_id,
            'isvalid' => 1
        );
        $data = $this->sys_model_creditcard->getCreditcardList($condition, 'id desc', $limit, '*', []);
        $this->response->showSuccessResult($data);
    }

    /**
     * 添加信用卡
     */
    public function add() {
        $user_id = $this->startup_user->userId();
        $post = $this->request->post(array('type', 'number', 'expiry_month', 'expiry_year', 'cvv', 'cardhold'));
        foreach ($post as $key => $val) {
            if (!isset($val) || empty($val)) {
                $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
            }
        }
        $data = array(
            'type' => $post['type'],
            'number' => $post['number'],
            'expiry_month' => $post['expiry_month'],
            'expiry_year' => $post['expiry_year'],
            'cvv' => $post['cvv'],
            'cardhold' => $post['cardhold'],
            'isvalid' => 1,
            'user_id' => $user_id,
        );
        $card_id = $this->sys_model_creditcard->addCreditcard($data);
        if ($card_id) {
            $this->response->showSuccessResult(['card_id'=>$card_id]);
        } else {
            $this->response->showErrorResult();
        }
    }
    /**
     * 删除信用卡
     */
    public function delete() {
        $user_id = $this->startup_user->userId();
        $post = $this->request->post(array('card_id'));
        foreach ($post as $key => $val) {
            if (!isset($val) || empty($val)) {
                $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
            }
        }
        $data = array(
            'isvalid' => 0,
        );
        $condition=array(
            'id'=>$post['card_id']
        );
        $card_id = $this->sys_model_creditcard->updateCreditcard($condition, $data);
        if ($card_id) {
            $this->response->showSuccessResult();
        } else {
            $this->response->showErrorResult();
        }
    }

    public function cc() {
        /*$url='23.106.154.237/firebase.php';
        $fields=array(
            'to'=>'fEGH2TyeatE:APA91bHn7SmBRu2n_U84MtNldeFRGi0rA4aAGCe_GVJsjAUr-OE3J3VL5zittgzLxLGL8mMJV_0__om83GCDIoMq2GqLNVKlhzbXmLbsr5S4tkpVayCSIPNYFV2Q9xKm662kO68nM749',
            'content'=>json_encode(['type'=>4,'b'=>2])
        );
        $ch = curl_init();
       //参数设置  
       $res= curl_setopt ($ch, CURLOPT_URL,$url);  
       curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
       curl_setopt ($ch, CURLOPT_HEADER, 0);
	
        //设置post数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

       $result = curl_exec ($ch);
       var_dump($result);*/
        $this->load->library('logic/calculate');
       var_dump( $this->logic_calculate->co(419476));
    }

}
