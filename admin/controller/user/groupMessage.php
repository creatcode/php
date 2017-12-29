<?php

class ControllerUserGroupMessage extends Controller
{
    private $cur_url = null;
    private $error = null;

    private $sys_model_group_send;
    private $sys_model_user;
    private $sys_model_sms_templates;

    public function __construct($registry)
    {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->sys_model_group_send = new sys_model\Group_Send($registry);
        $this->sys_model_user = new sys_model\User($registry);
        $this->sys_model_sms_templates = new sys_model\Sms_Templates($registry);
    }


    /**
     * 模板显示列表
     */
    public function index()
    {
        $result = $this->sys_model_sms_templates->getList([], 'template_id desc');
        foreach ($result as &$item) {
            $item['sms_platform'] = $this->get_sms_platform($item['sms_platform']);
        }
        $this->assign('data_rows', $result);
        $this->assign('info_action', $this->url->link('user/groupMessage/add'));
        $this->assign('send_list_url', $this->url->link('user/groupMessage/send_list'));
        $this->response->setOutput($this->load->view('user/group_message', $this->output));
    }

    /**
     * 添加自定义短信
     */
    public function add()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('template_id', 'message_blocks', 'template_text', 'mobiles'));
            $data = array(
                'template_id' => $input['template_id'],
                'template_text' => $input['template_text'],
                'message_block' => $input['message_blocks'],
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'add_time' => time()
            );
            $data['mobiles'] = $this->handleMobiles($input['mobiles']);
            $this->sys_model_group_send->add($data);
            $this->session->data['success'] = '添加群发成功！';
            $this->load->controller('common/base/redirect', $this->url->link('user/groupMessage/send_list', '', true));
        }
        $this->assign('title', '添加群发短信');
        $this->getForm();
    }

    public function edit()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('template_id', 'message_blocks', 'template_text', 'mobiles'));
            //更新id
            $get['id'] = $this->request->get('send_id');
            //需要更新数据
            $data = array(
                'template_id' => $input['template_id'],
                'template_text' => $input['template_text'],
                'message_block' => $input['message_blocks'],
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
            );
            //手机号码处理
            $data['mobiles'] = $this->handleMobiles($input['mobiles']);
            $this->sys_model_group_send->update($get, $data);
            $this->session->data['success'] = '添加群发成功！';
            $this->load->controller('common/base/redirect', $this->url->link('user/groupMessage/send_list', '', true));
        }
        $this->assign('title', '编辑群发短信');
        $this->getForm();
    }

    public function delete()
    {

    }

    /**
     * form展示
     * @return array $data 模板信息
     * @return array $info 发送短信信息
     */
    private function getForm()
    {
        // 编辑时获取已有的数据
        $template_id = $this->request->get['template_id'];
        $info = [];
        $info['sms_platform'] = '';
        if (!isset($template_id) || !is_numeric($template_id)) {
            exit('模板不存在');
        }
        //编辑时，获取数据
        if (isset($this->request->get['send_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'id' => $this->request->get['send_id']
            );
            $info = $this->sys_model_group_send->getGroupSendInfo($condition);
            $info['message_blocks'] = $info['message_block'];
            $info['message_block'] = explode('|', $info['message_block']);
            $mobile_f = explode(',', $info['mobiles']);
            $info['mobiles'] = implode(PHP_EOL, $mobile_f);
        }
        //获取模板数据
        $condition = [
            'template_id' => $template_id
        ];
        $data = $this->sys_model_sms_templates->getInfo($condition);
        //输出处理
        $url_extend = isset($info['id']) ? '&send_id=' . $info['id'] : '';
        $this->assign('data', $data);
        $this->assign('info', $info);
        $this->assign('action', $this->cur_url . '&template_id=' . $data['template_id'] . $url_extend);
        $this->assign('message_block', []);
        $this->assign('error', $this->error);
        $this->response->setOutput($this->load->view('user/group_message_form', $this->output));
    }

    public function info()
    {

    }

    public function send_list()
    {
        $filter = $this->request->get(array('filter_text'));
        $condition = [];
        if (!empty($filter['filter_text'])) {
            $condition['template_text'] = array('like', "%{$filter['filter_text']}%");
        }
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $order = 'group_send.add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $join = [
            'sms_templates' => 'sms_templates.template_id = group_send.template_id'
        ];
        $result = $this->sys_model_group_send->getGroupSendList($condition, $order, $limit, 'group_send.*,sms_templates.sms_platform', $join);
        $total = $this->sys_model_group_send->getGroupSendCount($condition);
        foreach ($result as &$item) {
            $item['mobiles_num'] = count(explode(',', $item['mobiles']));
            $item['sms_platform'] = $this->get_sms_platform($item['sms_platform']);
        }
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('filter', $filter);
        $this->assign('info_action', $this->url->link('user/groupMessage/edit'));
        $this->assign('templates_url', $this->url->link('user/groupMessage'));
        $this->assign('send_url', $this->url->link('user/groupMessage/send_sms'));
        $page_info = $this->page($total, $page, $rows, $filter, $offset);
        $this->assign('pagination', $page_info['pagination']);
        $this->assign('results', $page_info['results']);
        $this->response->setOutput($this->load->view('user/group_message_send_list', $this->output));
    }

    public function send_sms()
    {
        set_time_limit(0);
        //获取id得到数据
        $condition['group_send.id'] = $this->request->get('send_id');
        $join = [
            'sms_templates' => 'sms_templates.template_id = group_send.template_id'
        ];
        $filed = 'group_send.*,sms_templates.sms_platform,sms_templates.platform_template_id';
        $info = $this->sys_model_group_send->getGroupSendInfo($condition, $filed, $join);
        $data = [
            'send_time' => time()
        ];
        //更新发送时间
        $this->sys_model_group_send->update($condition, $data);
        //发送短信
        $sms = $this->getSmsObject($info['sms_platform']);
        if (!$sms) {
            exit('该短信类不存在');
        }
        $mobile_array = explode(',', $info['mobiles']);
        $mobile_array_chunk = array_chunk($mobile_array, 500);
        foreach ($mobile_array_chunk as $mobiles) {
            $sms->send_sms(implode(',', $mobiles), '', $info['platform_template_id'], $info['template_text']);
            sleep(3);
        }
        $this->session->data['success'] = '短信全部成功！';
        $this->load->controller('common/base/redirect', $this->url->link('user/groupMessage/send_list', '', true));
    }

    /**
     * 处理手机号码
     * @param $data
     * @return string
     */
    private function handleMobiles($data)
    {
        $mobile_array = [];
        $mobiles = explode(PHP_EOL, $data);
        //判断是不是单车用户
        if (is_array($mobiles) && !empty($mobiles)) {
            foreach ($mobiles as $mobile) {
                $condition = array(
                    'mobile' => trim($mobile)
                );
                $user = $this->sys_model_user->getUserInfo($condition, 'user_id');
                if ($user) {
                    $mobile_array[] = $mobile;
                }
            }
        }
        $mobiles_string = implode(',', $mobile_array);
        return $mobiles_string;
    }

    /**
     * 返回平台名称
     * @param $data
     * @return string
     */
    private function get_sms_platform($data)
    {
        switch ($data) {
            case 1:
                return '云片';
                break;
            case 2:
                return '容联';
                break;
            default:
                return '不明平台';
                break;
        }
    }

    /**
     * 分页
     * @param int $total 数据总数
     * @param int $page 当前页数
     * @param int $rows 一页多少条记录
     * @param array $filter 链接的参数
     * @param int $offset
     * @return array
     */
    protected function page($total, $page, $rows, $filter, $offset)
    {
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));
        return array('pagination' => $pagination, 'results' => $results);
    }

    private function validateForm()
    {
        $input = $this->request->post(array('template_text', 'mobiles'));

        if (!is_numeric($this->request->post('template_id'))) {
            $this->error['template_id'] = '请输入完整！';
        }

        if ($this->sms_templates[$this->request->post('template_id')]['block'] > 0) {
            $this->request->post('message_blocks');
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
     * 初始化短信类
     * @param $data
     * @return object|\Tool\Rest|\Tool\Yunpian
     */
    private function getSmsObject($data)
    {
        library('tool/phone_code');
        switch ($data) {
            case 1:
                return new \Tool\Yunpian();
                break;
            case 2:
                return new \Tool\Rest();
                break;
            default:
                return (object)null;
                break;
        }
    }
}
