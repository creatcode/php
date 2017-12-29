<?php

class ControllerRegionRegionAdvertisement extends Controller
{
    private $cur_url = null;
    private $error = null;

    public function __construct($registry)
    {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载region Model
        $this->load->library('sys_model/region', true);
        $this->load->library('sys_model/advertisement', true);
    }

    /**
     * 区域广告列表
     */
    public function index()
    {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['adv_region_id'])) {
            $adv_region_id = (int)$this->request->get['adv_region_id'];
            $condition['adv_region_id'] = $adv_region_id;
        } else {
            $adv_region_id = '';
        }

        $order = 'adv_sort ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $region_result = $this->sys_model_region->getRegionList();
        $region_arr = array();
        foreach ($region_result as $v) {
            $region_arr[$v['region_id']] = $v['region_name'];
        }
        $region_arr['-99999'] = '未开通区域广告';
        $result = $this->sys_model_advertisement->getAdvertisementList($condition, $order, $limit);
        $total = $this->sys_model_advertisement->getTotalAdvertisement($condition);

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {

                $item['edit_action'] = $this->url->link('region/region_advertisement/edit', 'adv_id=' . $item['adv_id']);
                $item['delete_action'] = $this->url->link('region/region_advertisement/delete', 'adv_id=' . $item['adv_id']);
                $item['info_action'] = $this->url->link('region/region_advertisement/info', 'adv_id=' . $item['adv_id']);
                $item['adv_region_id'] = $item['adv_region_id'] ? isset($region_arr[$item['adv_region_id']]) ? $region_arr[$item['adv_region_id']] : '平台' : '平台';
                $item['adv_start_time'] = $item['adv_start_time'] ? date('Y-m-d', $item['adv_start_time']) : '';
                $item['adv_end_time'] = $item['adv_end_time'] ? date('Y-m-d', $item['adv_end_time']) : '';
                $item['adv_effect_time'] = $item['adv_effect_time'] ? date('Y-m-d', $item['adv_effect_time']) : '';
                $item['adv_expire_time'] = $item['adv_expire_time'] ? date('Y-m-d', $item['adv_expire_time']) : '';
                $item['adv_add_time'] = $item['adv_add_time'] ? date('Y-m-d H:i:s', $item['adv_add_time']) : '';
                $item['adv_approve_time'] = $item['adv_approve_time'] ? date('Y-m-d H:i:s', $item['adv_approve_time']) : '';
                $item['adv_approved'] = $item['adv_approved'] == 1 ? '通过' : '未通过';

            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('region_list', $region_result);
        $this->assign('adv_region_id', $adv_region_id);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);
        $this->assign('add_action', $this->url->link('region/region_advertisement/add'));
        $this->assign('return_action', $this->url->link('region/region_advertisement'));
        $this->assign('send_msg', $this->url->link('region/region_advertisement/sendMsg'));
        $this->assign('check_url', $this->url->link('region/region_advertisement/reviewed'));
        $this->assign('get_advertisement_info', $this->url->link('region/region_advertisement/get_advertisement_info'));

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
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('region/advertisement_list', $this->output));
    }

    public function reviewed()
    {
        $post = $this->request->post(array('adv_id', 'adv_approve_memo', 'adv_approved'));
        foreach ($post as $k => $v) {
            if (!$v) {
                $this->response->showErrorResult('参数错误');
            }
        }
        $data['adv_approve_memo'] = $post['adv_approve_memo'];
        $data['adv_approved'] = $post['adv_approved'];

        $result = $this->sys_model_advertisement->updateAdvertisement(array('adv_id' => $post['adv_id']), $data);
        if (!$result) {
            $this->response->showErrorResult('审批失败');
        } else {
            $this->response->showSuccessResult(array('code' => 1), '审批成功');
        }

        $this->response->setOutput($this->load->view('region/advertisement_list', $this->output));
    }


    public function get_advertisement_info()
    {

        $post = $this->request->post(array('adv_id'));
        if (empty($post)) {
            $this->response->showErrorResult('参数错误');
        }
        $data = $this->sys_model_advertisement->getAdvertisementInfo(array('adv_id' => $post['adv_id']));
        if (empty($data)) {
            $this->response->showErrorResult('无此数据');
        }
        $this->assign('static', HTTP_IMAGE);
        $this->response->showSuccessResult($data, '信息已发送');
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns()
    {
        $this->setDataColumn('排序');
        $this->setDataColumn('区域');
        $this->setDataColumn('开始时间');
        $this->setDataColumn('结束时间');
//        $this->setDataColumn('生效时间');
//        $this->setDataColumn('失效时间');
        $this->setDataColumn('图片');
//        $this->setDataColumn('小图-iOS');
//        $this->setDataColumn('中图-iOS');
//        $this->setDataColumn('大图-iOS');
//        $this->setDataColumn('广告链接');
//        $this->setDataColumn('添加者名称');
        $this->setDataColumn('添加备注');
        $this->setDataColumn('添加时间');
//        $this->setDataColumn('审批者名称');
//        $this->setDataColumn('审批备注');
//        $this->setDataColumn('审批时间');
        $this->setDataColumn('是否审批');
//        $this->setDataColumn('广告排序');
        return $this->data_columns;
    }

    /**
     * 添加区域广告
     */
    public function add()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('adv_region_id', 'adv_type', 'adv_start_time', 'adv_end_time', 'adv_effect_time', 'adv_expire_time', 'adv_image', 'adv_image_1x', 'adv_image_2x', 'adv_image_3x', 'adv_image_4x', 'adv_image_5x', 'adv_link', 'ios_link', 'adv_add_memo', 'adv_sort', 'adv_id', 'adv_max_version_android', 'adv_max_version_ios'));
            $effect_time_arr = explode('至', $input['adv_effect_time']);
            $start_time_arr = explode('至', $input['adv_start_time']);
            $data = array(
                'adv_region_id' => $input['adv_region_id'],
                'adv_type' => (int)$input['adv_type'],
                'adv_start_time' => $start_time_arr[0] ? strtotime($start_time_arr[0]) : '',
                'adv_end_time' => $start_time_arr[1] ? strtotime($start_time_arr[1]) + 86399 : '',
                'adv_effect_time' => $effect_time_arr[0] ? strtotime($effect_time_arr[0]) : '',
                'adv_expire_time' => $effect_time_arr[1] ? strtotime($effect_time_arr[1]) + 86399 : '',
                'adv_image' => ((int)$input['adv_type'] == 0) ? $input['adv_image_3x'] : $input['adv_image_5x'],
                'adv_image_1x' => $input['adv_image_1x'],
                'adv_image_2x' => $input['adv_image_2x'],
                'adv_image_3x' => $input['adv_image_3x'],
                'adv_image_4x' => $input['adv_image_4x'],
                'adv_image_5x' => $input['adv_image_5x'],
                'adv_link' => empty($input['adv_link']) ? '' : $input['adv_link'],
                'ios_link' => empty($input['ios_link']) ? '' : $input['ios_link'],
                'adv_add_memo' => $input['adv_add_memo'],
                'adv_sort' => $input['adv_sort'],
                'adv_max_version_android' => $input['adv_max_version_android'],
                'adv_max_version_ios' => $input['adv_max_version_ios'],
                'adv_add_by_id' => $this->logic_admin->getId(),
                'adv_add_by' => $this->logic_admin->getadmin_name(),
                'adv_add_time' => time()
            );
            //添加消息发送
            $message_input = $this->request->post(['msg_title', 'msg_image', 'msg_abstract']);
            $message_data = [
                'msg_title' => $message_input['msg_title'],
                'msg_image' => $message_input['msg_image'],
                'msg_abstract' => $message_input['msg_abstract']
            ];

            $data = array_merge($data, $message_data);

            $this->sys_model_advertisement->addAdvertisement($data);

            $this->session->data['success'] = '添加区域广告成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '添加区域广告：' . $data['adv_add_memo']);

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('region/region_advertisement', $filter, true));
        }

        $this->assign('title', '区域广告添加');
        $this->getForm();
    }

    public function sendMsg()
    {
        if (!isset($this->request->post['adv_id']) || empty($this->request->post['adv_id'])) {
            $this->response->showErrorResult('参数错误');
        }
        $this->load->library('sys_model/user');
        $this->load->library('sys_model/advertisement');
        $this->load->library('sys_model/message');
        $advertisement_info = $this->sys_model_advertisement->getAdvertisementInfo(array('adv_id' => $this->request->post['adv_id']));
        if (empty($advertisement_info)) {
            $this->response->showErrorResult('参数错误');
        }

        $user_list = $this->sys_model_user->getUserList(array('region_id' => $advertisement_info['adv_region_id']), 'user_id');
        if (empty($user_list)) {
            $this->response->showErrorResult('该区域无任何用户');
        }

        foreach ($user_list as $v) {
            $data = array(
                'user_id' => $v['user_id'],
                'msg_time' => time(),
                'msg_image' => $advertisement_info['msg_image'],
                'msg_title' => $advertisement_info['msg_title'],
                'msg_abstract' => $advertisement_info['msg_abstract']
            );
            $this->sys_model_message->addMessage($data);
        }

        $this->response->showSuccessResult('信息已发送');
    }

    /**
     * 编辑区域
     */
    public function edit()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('adv_region_id', 'adv_type', 'adv_start_time', 'adv_end_time', 'adv_effect_time', 'adv_expire_time', 'adv_image', 'adv_image_1x', 'adv_image_2x', 'adv_image_3x', 'adv_image_4x', 'adv_image_5x', 'adv_link', 'ios_link', 'adv_add_memo', 'adv_sort', 'adv_id', 'adv_max_version_android', 'adv_max_version_ios'));

            $effect_time_arr = explode('至', $input['adv_effect_time']);
            $start_time_arr = explode('至', $input['adv_start_time']);
            $data = array(
                'adv_region_id' => $input['adv_region_id'],
                'adv_start_time' => $start_time_arr[0] ? strtotime($start_time_arr[0]) : '',
                'adv_end_time' => $start_time_arr[1] ? strtotime($start_time_arr[1]) : '',
                'adv_effect_time' => $effect_time_arr[0] ? strtotime($effect_time_arr[0]) : '',
                'adv_expire_time' => $effect_time_arr[1] ? strtotime($effect_time_arr[1]) : '',
                'adv_image' => ((int)$input['adv_type'] == 0) ? $input['adv_image_3x'] : $input['adv_image_5x'],
                'adv_image_1x' => $input['adv_image_1x'],
                'adv_image_2x' => $input['adv_image_2x'],
                'adv_image_3x' => $input['adv_image_3x'],
                'adv_image_4x' => $input['adv_image_4x'],
                'adv_image_5x' => $input['adv_image_5x'],
                'adv_link' => empty($input['adv_link']) ? '' : $input['adv_link'],
                'ios_link' => empty($input['ios_link']) ? '' : $input['ios_link'],
                'adv_add_memo' => $input['adv_add_memo'],
                'adv_sort' => $input['adv_sort'],
                'adv_max_version_android' => $input['adv_max_version_android'],
                'adv_max_version_ios' => $input['adv_max_version_ios'],
            );
            //消息
            $message_input = $this->request->post(['msg_title', 'msg_image', 'msg_abstract']);
            $message_data = [
                'msg_title' => $message_input['msg_title'],
                'msg_image' => $message_input['msg_image'],
                'msg_abstract' => $message_input['msg_abstract']
            ];

            $data = array_merge($data, $message_data);
            $condition = array(
                'adv_id' => $input['adv_id']
            );
            $this->sys_model_advertisement->updateAdvertisement($condition, $data);

            $this->session->data['success'] = '编辑区域广告成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '编辑区域广告：' . $data['adv_add_memo']);

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('region/region_advertisement', $filter, true));
        }

        $this->assign('title', '编辑区域广告');
        $this->getForm();
    }

    /**
     * 删除区域
     */
    public function delete()
    {
        if (isset($this->request->get['adv_id']) && $this->validateDelete()) {
            $condition = array(
                'adv_id' => $this->request->get['adv_id']
            );
            $this->sys_model_advertisement->deleteAdvertisement($condition);

            $this->session->data['success'] = '删除区域广告成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '删除区域广告：' . $this->request->get['adv_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('region/region_advertisement', $filter, true));
    }

    private function getForm()
    {
        // 编辑时获取已有的数据
        $info = $this->request->get(array(
            'adv_region_id',
            'adv_type',
            'adv_start_time',
            'adv_end_time',
            'adv_effect_time',
            'adv_expire_time',
            'adv_image',
            'adv_image_1x',
            'adv_image_2x',
            'adv_image_3x',
            'adv_image_4x',
            'adv_image_5x',
            'adv_link',
            'ios_link',
            'adv_add_memo',
            'adv_sort',
            'adv_id',
            'all_adv_image_1x',
            'all_adv_image_2x',
            'all_adv_image_3x',
            'all_adv_image_4x',
            'all_adv_image_5x',
            'adv_max_version_android',
            'adv_max_version_ios',
            'msg_image',
            'msg_title',
            'msg_abstract'));

        if ($info['adv_id']) {
            $info = $this->sys_model_advertisement->getAdvertisementInfo(array('adv_id' => $info['adv_id']));
            if (!empty($info)) {
                $info['adv_start_time'] = date('Y-m-d', $info['adv_start_time']) . ' 至 ' . date('Y-m-d', $info['adv_end_time']);
                $info['adv_effect_time'] = date('Y-m-d', $info['adv_effect_time']) . ' 至 ' . date('Y-m-d', $info['adv_expire_time']);
                $info['all_adv_image_1x'] = HTTP_IMAGE . $info['adv_image_1x'];
                $info['all_adv_image_2x'] = HTTP_IMAGE . $info['adv_image_2x'];
                $info['all_adv_image_3x'] = HTTP_IMAGE . $info['adv_image_3x'];
                $info['all_adv_image_4x'] = HTTP_IMAGE . $info['adv_image_4x'];
                $info['all_adv_image_5x'] = HTTP_IMAGE . $info['adv_image_5x'];
                $info['msg_image_url'] = HTTP_IMAGE . $info['msg_image'];
            }
        } else {
            $info['all_adv_image_1x'] = HTTP_IMAGE . 'images/default.jpg';
            $info['all_adv_image_2x'] = HTTP_IMAGE . 'images/default.jpg';
            $info['all_adv_image_3x'] = HTTP_IMAGE . 'images/default.jpg';
            $info['all_adv_image_4x'] = HTTP_IMAGE . 'images/default.jpg';
            $info['all_adv_image_5x'] = HTTP_IMAGE . 'images/default.jpg';
            $info['msg_image_url'] = getDefaultImage();
        }
        $adv_types = get_adv_type();
        $region_list = $this->sys_model_region->getRegionList();
        $this->assign('region_list', $region_list);
        $this->assign('adv_types', $adv_types);
        $this->assign('data', $info);
        $this->assign('action', $this->cur_url);
        $this->assign('return_action', $this->url->link('region/region_advertisement'));
        $this->assign('error', $this->error);
        $this->assign('upload_action', $this->url->link('common/upload'));
        $this->assign('static', HTTP_IMAGE);
        $this->response->setOutput($this->load->view('region/advertisement_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm()
    {
        $adv_type = $this->request->post('adv_type');
        if ($adv_type == 1) {           // 启动页广告
            $input = $this->request->post(array('adv_start_time', 'adv_effect_time', 'adv_image_1x', 'adv_image_2x', 'adv_image_3x', 'adv_image_4x', 'adv_image_5x', 'adv_add_memo'));
        } else {
            $input = $this->request->post(array('adv_start_time', 'adv_effect_time', 'adv_image_1x', 'adv_image_2x', 'adv_image_3x', 'adv_add_memo'));
        }
        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }
        return !$this->error;
    }

    /**
     * 验证删除条件
     */
    private function validateDelete()
    {
        return !$this->error;
    }
}
