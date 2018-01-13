<?php
error_reporting(E_ALL); //E_ALL
function cache_shutdown_error() {
    $_error = error_get_last();
    if ($_error && in_array($_error['type'], array(1, 4, 16, 64, 256, 4096, E_ALL))) {
        echo '<font color=red>你的代码出错了：</font></br>';
        echo '致命错误:' . $_error['message'] . '</br>';
        echo '文件:' . $_error['file'] . '</br>';
        echo '在第' . $_error['line'] . '行</br>';
    }
}
register_shutdown_function("cache_shutdown_error");
class ControllerSystemVersion extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载 Model
        $this->load->library('sys_model/version', true);
        $this->load->library('logic/setting', true);
    }

    /**
     * 版本列表站点
     */
    public function index() {
        $filter = $this->request->get(array('version_type', 'version_name'));
        $condition = array();
        if (!empty($filter['version_name'])) {
            $condition['version_name'] = array('like', "%{$filter['version_name']}%");
        }
        if (is_numeric($filter['version_type'])) {
            $condition['type'] = (int)$filter['version_type'];
        }
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $state = get_setting_boolean();
        $version_type = array(
            '1' => '站点',
            '2' => '用户',
            '3' => '运维',
            '4' => '锁桩',
            '5' => '控制盒',
        );
        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_version->getVersionList($condition, $order, $limit);
        $total = $this->sys_model_version->getTotalVersions($condition);

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['download_url'] = $item['filepath'] ? get_static_url($item['filepath']) : '-';
                $item['state'] = isset($state[$item['state']]) ? $state[$item['state']] : '';
                
                $item['version_name'] = $item['version_name'] ? $item['version_name'] : '-';

                $item['edit_action'] = $this->url->link('system/version/edit', 'version_id='.$item['version_id']);
                $item['delete_action'] = $this->url->link('system/version/delete', 'version_id='.$item['version_id']);
                $item['info_action'] = $this->url->link('system/version/info', 'version_id='.$item['version_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('version_type', $version_type);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('system/version/add'));
        // $this->assign('version_android_action', $this->url->link('system/version/android'));
        $this->assign('version_bike_action', $this->url->link('system/version/bike'));
        $this->assign('version_user_action', $this->url->link('system/version/user'));
        $this->assign('version_peration_action', $this->url->link('system/version/peration'));
        $this->assign('version_lock_action', $this->url->link('system/version/locker'));

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}';
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        //参数  lock_ids，version，url
            
        // $curl = curl_init();
        // curl_setopt($curl, CURLOPT_URL, 'http://120.76.72.228:808?route=ebike/firmware/up');
        // curl_setopt($curl, CURLOPT_HEADER, 0);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curl, CURLOPT_POST, 1);
        // $post_data = array(
        //      'lock_ids' => "80000c51",
        //      'version' => "2017-12-12 10:00",
        //      'url'  => "http://eazymov.s-bike.cn/static/bin/1212V1.0.5.bin"
        // );
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        // $data = curl_exec($curl);
        // curl_close($curl);
        // $data1 = json_decode($data,true);
        // print_r($data1);



        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('type', 1);

        $this->response->setOutput($this->load->view('system/version_site_list', $this->output));
    }


    /**
     * 版本列表用户
     */
    public function user(){
         $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('system/version/add'));
        // $this->assign('version_android_action', $this->url->link('system/version/android'));
        $this->assign('version_site_action', $this->url->link('system/version'));
        $this->assign('version_lock_action', $this->url->link('system/version/locker'));
        $this->assign('version_peration_action', $this->url->link('system/version/peration'));
        $this->assign('version_bike_action', $this->url->link('system/version/bike'));

       

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('type', 2);

        $this->response->setOutput($this->load->view('system/version_user_list', $this->output));
    }


    /**
     * 版本列表运维
     */
    public function peration(){
        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('system/version/add'));
        // $this->assign('version_android_action', $this->url->link('system/version/android'));
        $this->assign('version_site_action', $this->url->link('system/version'));
        $this->assign('version_user_action', $this->url->link('system/version/user'));
        $this->assign('version_lock_action', $this->url->link('system/version/locker'));
        $this->assign('version_bike_action', $this->url->link('system/version/bike'));

       

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('type', 3);

        $this->response->setOutput($this->load->view('system/version_peration_list', $this->output));
    }

    /**
     * 版本列表锁桩
     */
    public function locker(){
         $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('system/version/add'));
        // $this->assign('version_android_action', $this->url->link('system/version/android'));
        $this->assign('version_site_action', $this->url->link('system/version'));
        $this->assign('version_user_action', $this->url->link('system/version/user'));
        $this->assign('version_peration_action', $this->url->link('system/version/peration'));
        $this->assign('version_bike_action', $this->url->link('system/version/bike'));

       

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('type', 4);

        $this->response->setOutput($this->load->view('system/version_lock_list', $this->output));
    }

    /**
     * 版本列表车辆
     */
    public function bike(){
        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('system/version/add'));
        // $this->assign('version_android_action', $this->url->link('system/version/android'));
        $this->assign('version_lock_action', $this->url->link('system/version/locker'));
        $this->assign('version_user_action', $this->url->link('system/version/user'));
        $this->assign('version_peration_action', $this->url->link('system/version/peration'));
        $this->assign('version_site_action', $this->url->link('system/version'));

       

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('type', 5);

        $this->response->setOutput($this->load->view('system/version_bike_list', $this->output));
    }
    /**
     * 版本列表安卓
     */
    public function android() {
        $type = isset($this->request->get['type']) ? intval($this->request->get['type']) ? intval($this->request->get['type']) : 2 : 2;
        $condition = array('type' => $type);

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $state = get_setting_boolean();

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_version->getVersionList($condition, $order, $limit);
        $total = $this->sys_model_version->getTotalVersions($condition);

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['download_url'] = $item['filepath'] ? get_static_url($item['filepath']) : '-';
                $item['state'] = isset($state[$item['state']]) ? $state[$item['state']] : '';
                $item['version_name'] = $item['version_name'] ? $item['version_name'] : '-';

                $item['edit_action'] = $this->url->link('system/version/edit', 'version_id='.$item['version_id']);
                $item['delete_action'] = $this->url->link('system/version/delete', 'version_id='.$item['version_id']);
                $item['info_action'] = $this->url->link('system/version/info', 'version_id='.$item['version_id']);
            }
        }

        $data_columns = $this->getDataColumnsAndroid();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('system/version/add'));
        $this->assign('version_site_action', $this->url->link('system/version'));
        $this->assign('version_lock_action', $this->url->link('system/version/locker'));
        $this->assign('version_bike_action', $this->url->link('system/version/bike'));

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}';
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->assign('type', $type);

        $this->response->setOutput($this->load->view('system/version_android_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        // $this->setDataColumn('版本');
        $this->setDataColumn('类别');
        $this->setDataColumn('版本号');
        $this->setDataColumn('更新内容');
        $this->setDataColumn('下载地址');
        $this->setDataColumn('更新时间');
        $this->setDataColumn('状态');
        $this->setDataColumn('设备数量');
        return $this->data_columns;
    }

 

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumnsAndroid() {
        $this->setDataColumn('版本');
        $this->setDataColumn('版本号');
        $this->setDataColumn('更新内容');
        $this->setDataColumn('下载地址');
        $this->setDataColumn('更新时间');
        $this->setDataColumn('状态');
        return $this->data_columns;
    }
    /**
     * 添加版本
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('filepath', 'version_code', 'version_name', 'description', 'state', 'type', 'forced_update','version_type','version_num'));
            $now = time();
            $data = array(
                'filepath' => $input['filepath'],
                // 'version_num' => $input['version_num'],
                'type' => $input['version_type'],
                'version_code' => $input['version_code'],
                'version_name' => $input['version_name'],
                'description' => $input['description'],
                'state' => $input['state']=='1' ? 1 : 0,
                'type' => $input['type'],
                'forced_update' => $input['forced_update'] == "1" ? 1 : 0,
                'add_time' => $now
            );

            $version_id = $this->sys_model_version->addVersion($data);
            if(!empty($input['forced_update']) && $input['type'] == 1) {
                $this->logic_setting->editSetting(array('config_ios_fail_version'=>$input['version_code']));
            }else if(!empty($input['forced_update']) && $input['type'] == 2){
                $this->logic_setting->editSetting(array('config_android_fail_version'=>$input['version_code']));
            }

            $this->session->data['success'] = '添加版本成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加版本：' . $input['version_name'],
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->load->controller('common/base/redirect', $this->url->link('system/version', '', true));
        }

        $this->assign('title', '添加新版本');
        $this->getForm();



        
    }

    /**
     * 编辑版本
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('filepath', 'version_code', 'version_name', 'description', 'state', 'type', 'forced_update'));
            $version_id = $this->request->get['version_id'];
            $data = array(
                'filepath' => $input['filepath'],
                'version_code' => $input['version_code'],
                'version_name' => $input['version_name'],
                'description' => $input['description'],
                'state' => $input['state'],
                'type' => $input['type'],
                'forced_update' => $input['forced_update'] == "1" ? 1 : 0,
            );
            $condition = array(
                'version_id' => $version_id
            );
            $this->sys_model_version->updateVersion($condition, $data);

            $this->session->data['success'] = '编辑版本成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '编辑版本：' . $input['version_name'],
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->load->controller('common/base/redirect', $this->url->link('system/version', '', true));
        }

        $this->assign('title', '编辑版本');
        $this->getForm();
    }


    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('filepath', 'version_code', 'version_name', 'description', 'state', 'type', 'forced_update'));
        $version_id = $this->request->get('version_id');
        if (isset($this->request->get['version_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'version_id' => $this->request->get['version_id']
            );
            $info = $this->sys_model_version->getVersionInfo($condition);
        }

        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&version_id=' . $version_id);
        $this->assign('upload_url', $this->url->link('common/upload'));
        $this->assign('return_action', $this->url->link('system/version'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('system/version_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $info = $this->request->post(array('filepath', 'version_code', 'version_name', 'description', 'state', 'type', 'forced_update'));

        if($info['type'] == 2){
            if (empty($info['filepath']) || empty($info['version_code']) || empty($info['version_name'])) {
                $this->error['warning'] = '请上传文件！';
            }
        }

        if (empty($info['description'])) {
            $this->error['warning'] = '请完善更新内容！';
        }

        if(empty($info['state']) && !empty($info['forced_update'])){
            $this->error['warning'] = '停用状态下不能开启强制更新！';
        }

//        if ($this->error) {
//            $this->error['warning'] = '警告: 存在错误，请检查！';
//        }
        return !$this->error;
    }

}