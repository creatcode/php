<?php
class ControllerMeInformation extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载admin Model
        $this->load->library('sys_model/admin', true);
    }

    /**
     * 个人中心
     */
    public function index() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('password'));
            $admin_id = $this->logic_admin->getId();
            // 是否需要更改密码
            if (!empty($input['password'])) {
                $data['password'] = $input['password'];
                // 更新用户信息条件
                $condition = array(
                    'admin_id' => $admin_id
                );
                $this->logic_admin->update($condition, $data);

                $this->session->data['success'] = '修改成功！';
            }
            else {
                $this->session->data['success'] = '没有做任何修改';
            }

            $this->load->controller('common/base/redirect', $this->url->link('me/information', '', true));
        }

        $this->assign('title', '账号信息');
        $this->getForm();
    }

    private function getForm() {
        $info = $this->logic_admin->getData();

        $info['add_time'] = isset($info['add_time']) && !empty($info['add_time']) ? date('Y-m-d H:i:s', $info['add_time']) : '';

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $this->assign('data', $info);
        $this->assign('action', $this->cur_url);
        $this->assign('error', $this->error);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('me/information_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
//        $input = $this->request->post(array('state', 'type', 'lock_sn'));
//
//        foreach ($input as $k => $v) {
//            if (empty($v)) {
//                $this->error[$k] = '请输入完整！';
//            }
//        }

        $password = $this->request->post('password');
        $confirm = $this->request->post('confirm');
        if (!empty($password) && !$this->logic_admin->checkPasswordFormat($password)) {
            $this->error['password'] = '请输入6-16位字母数字的密码！';
        } else {
            if ($password !== $confirm) {
                $this->error['confirm'] = '两次输入密码不正确！';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }
        return !$this->error;
    }

}
