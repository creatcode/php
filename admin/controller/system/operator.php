<?php
class ControllerSystemOperator extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载bicycle Model
        $this->load->library('logic/setting', true);
        $this->assign('lang',$this->language->all());
    }

    /**
     * 单车运营设置
     */
    public function index() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->valibikeForm()) {
            $data = $this->request->post(array('config_bike_rent', 'config_bike_return'));
            
            $this->logic_setting->editSetting($data);

            $this->session->data['success'] = $this->language->get('t20');

            $this->load->controller('common/base/redirect', $this->url->link('system/operator', '', true));
        }

        $this->assign('title_bike', $this->language->get('t3'));
        $this->assign('title_lock', $this->language->get('t4'));
         
        $this->assign('lock_action', $this->url->link('system/operator/lockfrom'));

        $this->getForm_bike();
    }

    
   /**
    * 桩车运营设置
    */
     public function lockfrom(){
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->valilockForm()) {
            $data = $this->request->post(array('config_lock_rent','config_lock_return','config_lock_free_time','config_max_piles','config_full_threshold','config_low_threshold','config_site_time','config_electricity_ratio','config_upload_cycle'));
            
            $this->logic_setting->editSetting($data);

            $this->session->data['success'] = $this->language->get('t20');

            $this->load->controller('common/base/redirect', $this->url->link('system/operator/lockfrom', '', true));
        }

        $this->assign('title_bike', $this->language->get('t3'));
        $this->assign('title_lock', $this->language->get('t4'));
        $this->assign('oper_action', $this->url->link('system/operator'));

         
        $this->getForm_lock();
        
    }

    

    /**
     * @Author   Estronger
     * @DateTime 2017-12-14
     * @license  单车相关
     * @return   [type]     [description]
     */
    private function getForm_bike(){
        $data = array();
        // 长时未租
        if (isset($this->request->post['config_bike_rent'])) {
            $data['config_bike_rent'] = $this->request->post['config_bike_rent'];
        } else {
            $data['config_bike_rent'] = $this->config->get('config_bike_rent');
        }

        // 长时未还
        if (isset($this->request->post['config_bike_return'])) {
            $data['config_bike_return'] = $this->request->post['config_bike_return'];
        } else {
            $data['config_bike_return'] = $this->config->get('config_bike_return');
        }
       
        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }
        $this->assign('data', $data);
        $this->assign('action', $this->cur_url);
        $this->assign('error', $this->error);
        $this->assign('action',$this->url->link('system/operator'));


        $this->response->setOutput($this->load->view('system/operator_form_bike', $this->output));
    }

    /**
     * @Author   Estronger
     * @DateTime 2017-12-14
     * @license  桩车相关
     * @return   [type]     [description]
     */
    private function getForm_lock(){
        $data = array();
        // 锁桩空闲时长
        if (isset($this->request->post['config_lock_free_time'])) {
            $data['config_lock_free_time'] = $this->request->post['config_lock_free_time'];
        } else {
            $data['config_lock_free_time'] = $this->config->get('config_lock_free_time');
        }

         // 车辆长时未租
        if (isset($this->request->post['config_lock_rent'])) {
            $data['config_lock_rent'] = $this->request->post['config_lock_rent'];
        } else {
            $data['config_lock_rent'] = $this->config->get('config_lock_rent');
        }

         // 车辆长时未还
        if (isset($this->request->post['config_lock_return'])) {
            $data['config_lock_return'] = $this->request->post['config_lock_return'];
        } else {
            $data['config_lock_return'] = $this->config->get('config_lock_return');
        }

        // 站点实时上传周期
        if (isset($this->request->post['config_upload_cycle'])) {
            $data['config_upload_cycle'] = $this->request->post['config_upload_cycle'];
        } else {
            $data['config_upload_cycle'] = $this->config->get('config_upload_cycle');
        }

        // 低电量比率
        if (isset($this->request->post['config_electricity_ratio'])) {
            $data['config_electricity_ratio'] = $this->request->post['config_electricity_ratio'];
        } else {
            $data['config_electricity_ratio'] = $this->config->get('config_electricity_ratio');
        }

        // 站点云端同步时间
        if (isset($this->request->post['config_site_time'])) {
            $data['config_site_time'] = $this->request->post['config_site_time'];
        } else {
            $data['config_site_time'] = $this->config->get('config_site_time');
        }

        // 低电量阈值
        if (isset($this->request->post['config_low_threshold'])) {
            $data['config_low_threshold'] = $this->request->post['config_low_threshold'];
        } else {
            $data['config_low_threshold'] = $this->config->get('config_low_threshold');
        }

        // 满电量阈值
        if (isset($this->request->post['config_full_threshold'])) {
            $data['config_full_threshold'] = $this->request->post['config_full_threshold'];
        } else {
            $data['config_full_threshold'] = $this->config->get('config_full_threshold');
        }

        // 同时最大可充锁桩个数
        if (isset($this->request->post['config_max_piles'])) {
            $data['config_max_piles'] = $this->request->post['config_max_piles'];
        } else {
            $data['config_max_piles'] = $this->config->get('config_max_piles');
        }


        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $this->assign('data', $data);
        $this->assign('action', $this->cur_url);
        $this->assign('error', $this->error);
        $this->assign('action',$this->url->link('system/operator/lockfrom'));


        $this->response->setOutput($this->load->view('system/operator_form_lock', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function valibikeForm() {
        $input = $this->request->post(array('config_bike_return','config_bike_rent'));
        


        foreach ($input  as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = $this->language->get('t21');
            }
        }


        if ($this->error) {
            $this->error['warning'] = $this->language->get('t22');
        }
        
        return !$this->error;
    }



    private function valilockForm() {
        $input = $this->request->post(array('config_lock_free_time','config_lock_rent','config_lock_return','config_upload_cycle','config_electricity_ratio','config_site_time','config_low_threshold','config_full_threshold','config_max_piles'));


        foreach ($input  as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = $this->language->get('t21');
            }
        }


        if ($this->error) {
            $this->error['warning'] = $this->language->get('t22');
        }
       
        return !$this->error;
    }


    private function validateForm() {
         $input = $this->request->post(array('config_phone', 'config_hotline', 'config_email','config_web'));


        foreach ($input  as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = $this->language->get('t21');
            }
        }


        if ($this->error) {
            $this->error['warning'] = $this->language->get('t22');
        }
        
        return !$this->error;
    }


    /**
     * @Author   Estronger
     * @DateTime 2017-12-14
     * @license  运营相关
     * @return   [type]     [description]
     */
    
    public function yunyin(){
         if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $data = $this->request->post(array('config_phone', 'config_hotline', 'config_email','config_web'));

            $this->logic_setting->editSetting($data);

            $this->session->data['success'] = $this->language->get('t20');

            $this->load->controller('common/base/redirect', $this->url->link('system/operator/yunyin', '', true));
        }

        $this->assign('title', $this->language->get('t2'));
        $this->getForm();
    }



    private function getForm() {
        $data = array();
       


        // 联系电话
        if (isset($this->request->post['config_phone'])) {
            $data['config_phone'] = $this->request->post['config_phone'];
        } else {
            $data['config_phone'] = $this->config->get('config_phone');
        }

        // 客服电话
        if (isset($this->request->post['config_hotline'])) {
            $data['config_hotline'] = $this->request->post['config_hotline'];
        } else {
            $data['config_hotline'] = $this->config->get('config_hotline');
        }

        // 电子邮箱
        if (isset($this->request->post['config_email'])) {
            $data['config_email'] = $this->request->post['config_email'];
        } else {
            $data['config_email'] = $this->config->get('config_email');
        }

        // 官网
        if (isset($this->request->post['config_web'])) {
            $data['config_web'] = $this->request->post['config_web'];
        } else {
            $data['config_web'] = $this->config->get('config_web');
        }

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $this->assign('data', $data);
        $this->assign('action', $this->cur_url);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('system/operator_form', $this->output));
    }
}