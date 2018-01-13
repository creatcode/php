<?php
class ControllerUserMessage extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载coupon Model
        $this->load->library('sys_model/message', true);
        $this->load->library('sys_model/user', true);
        $this->assign('lang',$this->language->all());
    }

    /**
     * 系统消息列表
     */
    public function index() {
        $filter = array();
        $condition = array();
        
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $fields = 'm.*';
        $order = 'msg_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $join = array(
            'user' => 'user.user_id=m.user_id'
        );
        $result = $this->sys_model_message->getMessageList($condition, $fields, $order, $limit);

        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList('');
        $filter_citys = $this->sys_model_city->getCityList('');
        $users = $this->sys_model_user->getUserList();


        $total = $this->sys_model_message->getTotalMessages($condition);
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                if ($item['user_type'] == '0') {
                    $item['user_name'] = '全部用户';
                } else if($item['user_type'] == '1'){
                    $item['user_name'] = '自定义';
                }else if($item['user_type'] == '2'){
                    if($item['city_id'] == '0'){
                        $item['user_name'] = '区域';
                    }else{
                        $item['user_name'] = '城市';
                    }
                }

                $item['msg_time'] = isset($item['msg_time']) && $item['msg_time'] > 0 ? date('Y-m-d H:i:s', $item['msg_time']) : '';
                $item['delete_action'] = $this->url->link('user/message/delete', 'msg_id='.$item['msg_id']);
                $item['info_action'] = $this->url->link('user/message/info', 'msg_id='.$item['msg_id']);
            }
        }

        
        // foreach ($filter_regions as $key2 => $val2) {
        //     $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        // }

        $data_columns = $this->getDataColumns();
        $this->assign('filter_regions', $filter_regions);
        $this->assign('filter_citys', $filter_citys);
        $this->assign('users', $users);
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/message/add'));

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->response->setOutput($this->load->view('user/message_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn($this->language->get('t44'));
        $this->setDataColumn($this->language->get('t11'));
        $this->setDataColumn($this->language->get('t45'));
        $this->setDataColumn($this->language->get('t46'));
        return $this->data_columns;
    }

    /**
     * @license  系统消息删除
     * @return   [type]     [description]
     */
    public function delete(){
        if (isset($this->request->get['msg_id'])&& $this->validateDelete() ) {
            $condition = array(
                'msg_id' => $this->request->get['msg_id']
            );
            $this->sys_model_message->deleteMessage($condition);

            $this->session->data['success'] = '删除系统消息成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '删除系统消息：' . $this->request->get['msg_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('user/message', '', true));
    }


     /**
     * 验证删除条件
     */
    private function validateDelete() {
        return !$this->error;
    }


    /**
     * 添加系统消息
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('msg_title', 'msg_image', 'user_type', 'mobiles', 'msg_abstract', 'msg_link', 'msg_content','city_id','region_id'));
            $now = time();
            // 全部用户
            $msg_id = '';
            if ($input['user_type'] == 0) {
                $data = array(
                    'user_type' =>$input['user_type'],
                    'user_id' => 0,
                    'msg_time' => $now,
                    'msg_image' => $input['msg_image'],
                    'msg_title' => $input['msg_title'],
                    'msg_abstract' => $input['msg_abstract'],
                    'msg_content' => $input['msg_content'],
                    'msg_link' => $input['msg_link'],
                );
                $msg_id = $this->sys_model_message->addMessage($data);
            } elseif ($input['user_type'] == 1) {
                // 自定义用户
                $mobiles = explode(PHP_EOL, $input['mobiles']);
                foreach($mobiles as &$mobile) {
                    $mobile = trim($mobile);
                }
                $condition = array(
                    'mobile' => array('in', $mobiles)
                );
                $users = $this->sys_model_user->getUserList($condition, 'user_id');
                $user_ids = array_column($users, 'user_id');
                if (is_array($user_ids) && !empty($user_ids)) {
                    $user_id = implode(',', $user_ids);
                    $data = array(
                        'user_type' =>$input['user_type'],
                        'user_id' => $user_id,
                        'msg_time' => $now,
                        'msg_image' => $input['msg_image'],
                        'msg_title' => $input['msg_title'],
                        'msg_abstract' => $input['msg_abstract'],
                        'msg_content' => $input['msg_content'],
                        'msg_link' => $input['msg_link'],
                    );
                    $msg_id = $this->sys_model_message->addMessage($data);
                }
            }else if($input['user_type'] == 2){
                //区域或城市
                 $data = array(
                    'user_type' =>$input['user_type'],
                    'region_id' =>$input['region_id'],
                    'city_id' =>$input['city_id'],
                    'user_id' => 0,
                    'msg_time' => $now,
                    'msg_image' => $input['msg_image'],
                    'msg_title' => $input['msg_title'],
                    'msg_abstract' => $input['msg_abstract'],
                    'msg_content' => $input['msg_content'],
                    'msg_link' => $input['msg_link'],
                );
                $msg_id = $this->sys_model_message->addMessage($data);
            }

            $this->session->data['success'] = '添加系统消息成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加系统消息：ID ' . $msg_id,
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 2,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);
            
            $filter = array();

            $this->load->controller('common/base/redirect', $this->url->link('user/message', $filter, true));
        }

        $this->assign('title', '增加系统消息');
        $this->getForm();
    }

    /**
     * 消息详情
     */
    public function info() {
        $msg_id = $this->request->get('msg_id');
        $condition = array(
            'msg_id' => $msg_id
        );
        $info = $this->sys_model_message->getMessageInfo($condition);
        $info['msg_image_url'] = HTTP_IMAGE . ($info['msg_image'] ? $info['msg_image'] : 'images/nopic.jpg');

        $info['user_name'] = '';
        if ($info['user_id'] == 0) {
            $info['user_name'] = '全部用户';
        } else {
            $user_names = array();
            $condition =array(
                'user_id' => array('in', $info['user_id'])
            );
            $users = $this->sys_model_user->getUserList($condition);
            if (is_array($users) && !empty($users)) {
                foreach ($users as $user) {
                    $user_names[] = $user['mobile'];
                }
            }
            $info['user_name'] = implode('<br/>', $user_names);
        }

        
        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('user/message'));

        $this->response->setOutput($this->load->view('user/message_info', $this->output));
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('msg_title', 'msg_image', 'user_type', 'mobiles', 'msg_abstract', 'msg_link', 'msg_content','region_id','city_id','region_id'));

        $info['msg_image_url'] = !empty($info['msg_image']) ? HTTP_IMAGE . $info['msg_image'] : getDefaultImage();



        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        $filter_citys = $this->sys_model_city->getCityList([], '', '', 'city_id,city_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }





        $this->assign('data', $info);
        $this->assign('filter_regions', $filter_regions);
        $this->assign('filter_citys', $filter_citys);
        $this->assign('action', $this->cur_url);
        $this->assign('return_action', $this->url->link('user/message'));
        $this->assign('upload_action', $this->url->link('common/upload'));
        $this->assign('error', $this->error);
        $this->assign('get_modal_ad_url', $this->url->link('user/message/getAdList'));
        $this->response->setOutput($this->load->view('user/message_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('msg_title',  'msg_abstract', 'msg_link', 'msg_content','msg_image'));
// 'msg_image',
        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        $user_type = $this->request->post('user_type');
        $mobiles = $this->request->post('mobiles');
        if ($user_type == 1 && empty($mobiles)) {
            $this->error['mobiles'] = '请输入用户手机号！';
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }
        return !$this->error;
    }

    public function getAdList() {
        $filter = array();
        $condition = array();

        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $order = 'adv_sort ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);


        if (isset($this->request->get['adv_region_id'])) {
            $adv_region_id = (int)$this->request->get['adv_region_id'];
            $condition['adv_region_id'] = $adv_region_id;
        } else {
            $adv_region_id = '';
        }

        $this->load->library('sys_model/region', true);
        $this->load->library('sys_model/advertisement', true);

        $region_result = $this->sys_model_region->getRegionList();
        $region_arr = array();
        foreach($region_result as $v){
            $region_arr[$v['region_id']] = $v['region_name'];
        }
        $region_arr['-99999'] = '未开通区域广告';

        $condition['adv_approved'] = 1;

        $result = $this->sys_model_advertisement->getAdvertisementList($condition, $order, $limit);
        $total = $this->sys_model_advertisement->getTotalAdvertisement($condition);

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['adv_region_id']     = $item['adv_region_id']  ? isset($region_arr[$item['adv_region_id']]) ? $region_arr[$item['adv_region_id']] :  '平台' : '平台' ;
                $item['adv_start_time']   = $item['adv_start_time'] ? date('Y-m-d', $item['adv_start_time']) : '';
                $item['adv_end_time']     = $item['adv_end_time'] ? date('Y-m-d', $item['adv_end_time']) : '';
                $item['adv_effect_time']  = $item['adv_effect_time'] ? date('Y-m-d', $item['adv_effect_time']) : '';
                $item['adv_expire_time']  = $item['adv_expire_time'] ? date('Y-m-d', $item['adv_expire_time']) : '';
                $item['adv_add_time']     = $item['adv_add_time'] ? date('Y-m-d H:i:s', $item['adv_add_time']) : '';
                $item['adv_approve_time'] = $item['adv_approve_time'] ? date('Y-m-d H:i:s', $item['adv_approve_time']) : '';
                $item['adv_approved']     = $item['adv_approved'] == 1 ? '通过' : '未通过' ;
                $item['adv_image'] = HTTP_IMAGE . $item['adv_image'];
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('region_list', $region_result);
        $this->assign('adv_region_id', $adv_region_id);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('region/region_advertisement/add'));


        $this->assign('send_msg', $this->url->link('region/region_advertisement/sendMsg'));
        $this->assign('check_url', $this->url->link('region/region_advertisement/reviewed'));
        $this->assign('get_advertisement_info', $this->url->link('region/region_advertisement/get_advertisement_info'));

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $output = array();

        //$output['data_columns'] = $data_columns;
        $output['totalPage'] = ceil($total / $this->config->get('config_limit_admin'));
        $output['total'] = $total;
        $output['pageSize'] = $rows;
        $output['page'] = $page;
        $output['items'] = $result;

        $this->response->showSuccessResult($output);
    }
}
