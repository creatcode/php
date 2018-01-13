<?php
class ControllerUserPoints extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载points Model
        $this->load->library('sys_model/points', true);
        $this->assign('lang',$this->language->all());
    }

    /**
     * 用户信用记录列表
     */
    public function index() {
        $filter = $this->request->get(array('mobile', 'points', 'point_desc', 'admin_name', 'add_time'));

        $condition = array();
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (is_numeric($filter['points'])) {
            $condition['points'] = (int)$filter['points'];
        }
        if (!empty($filter['point_desc'])) {
            $condition['point_desc'] = array('like', "%{$filter['point_desc']}%");
        }
        if (!empty($filter['admin_name'])) {
            $condition['admin_name'] = array('like', "%{$filter['admin_name']}%");
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['pl.add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
        }

        $filter_types = array(
            'mobile' => $this->language->get('t8'),
            'points' => $this->language->get('t9'),
            'point_desc' => $this->language->get('t10'),
            'admin_name' => $this->language->get('t11'),
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'pl.add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $field = 'pl.*, user.mobile';
        $join = array(
            'user' => 'user.user_id=pl.user_id'
        );
        $result = $this->sys_model_points->getPointsList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_points->getTotalPoints($condition, $join);

        $model = array(

        );
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                foreach ($model as $k => $v) {
                    $item[$k] = isset($v[$item[$k]]) ? $v[$item[$k]] : '';
                }

                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['edit_action'] = $this->url->link('points/points/edit', 'point_id='.$item['point_id']);
                $item['delete_action'] = $this->url->link('points/points/delete', 'point_id='.$item['point_id']);
                $item['info_action'] = $this->url->link('points/points/info', 'point_id='.$item['point_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('model', $model);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('points/points/add'));

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
        $this->assign('time_type',get_time_type());
        $this->assign('export_action', $this->url->link('user/points/export'));

        $this->response->setOutput($this->load->view('user/points_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn($this->language->get('t8'));
        $this->setDataColumn($this->language->get('t9'));
        $this->setDataColumn($this->language->get('t10'));
        $this->setDataColumn($this->language->get('t11'));
        $this->setDataColumn($this->language->get('t12'));
        return $this->data_columns;
    }

    /**
     * 添加用户信用记录
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('points_sn', 'type', 'lock_sn'));
            $now = time();
            $data = array(
                'points_sn' => $input['points_sn'],
                'type' => (int)$input['type'],
                'lock_sn' => $input['lock_sn'],
                'add_time' => $now
            );
            $this->sys_model_points->addpoints($data);

            $this->session->data['success'] = '添加用户信用记录成功！';
            
            $filter = $this->request->get(array('points_sn', 'type', 'lock_sn', 'is_using'));

            $this->load->controller('common/base/redirect', $this->url->link('points/points', $filter, true));
        }

        $this->assign('title', '用户信用记录添加');
        $this->getForm();
    }

    /**
     * 编辑用户信用记录
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('points_sn', 'type', 'lock_sn'));
            $point_id = $this->request->get['point_id'];
            $data = array(
                'points_sn' => $input['points_sn'],
                'type' => (int)$input['type'],
                'lock_sn' => $input['lock_sn']
            );
            $condition = array(
                'point_id' => $point_id
            );
            $this->sys_model_points->updatepoints($condition, $data);

            $this->session->data['success'] = '编辑用户信用记录成功！';

            $filter = $this->request->get(array('points_sn', 'type', 'lock_sn', 'is_using'));

            $this->load->controller('common/base/redirect', $this->url->link('points/points', $filter, true));
        }

        $this->assign('title', '编辑用户信用记录');
        $this->getForm();
    }

    /**
     * 删除用户信用记录
     */
    public function delete() {
        if (isset($this->request->get['point_id']) && $this->validateDelete()) {
            $condition = array(
                'point_id' => $this->request->get['point_id']
            );
            $this->sys_model_points->deletepoints($condition);

            $this->session->data['success'] = '删除用户信用记录成功！';
        }
        $filter = $this->request->get(array('points_sn', 'type', 'lock_sn', 'is_using'));
        $this->load->controller('common/base/redirect', $this->url->link('points/points', $filter, true));
    }

    /**
     * 用户信用记录详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $point_id = $this->request->get('point_id');
        $condition = array(
            'point_id' => $point_id
        );
        $info = $this->sys_model_points->getpointsInfo($condition);
        if (!empty($info)) {
            $model = array(
                'type' => get_points_type(),
                'is_using' => get_common_boolean()
            );
            foreach ($model as $k => $v) {
                $info[$k] = isset($v[$info[$k]]) ? $v[$info[$k]] : '';
            }
        }

        $this->assign('data', $info);

        $this->response->setOutput($this->load->view('points/points_info', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('mobile', 'points', 'point_desc', 'admin_name', 'add_time'));

        $condition = array();
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (is_numeric($filter['points'])) {
            $condition['points'] = (int)$filter['points'];
        }
        if (!empty($filter['point_desc'])) {
            $condition['point_desc'] = array('like', "%{$filter['point_desc']}%");
        }
        if (!empty($filter['admin_name'])) {
            $condition['admin_name'] = array('like', "%{$filter['admin_name']}%");
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['pl.add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        $order = 'pl.add_time DESC';
        $limit = '';

        $field = 'pl.*, user.mobile';
        $join = array(
            'user' => 'user.user_id=pl.user_id'
        );
        $result = $this->sys_model_points->getPointsList($condition, $order, $limit, $field, $join);
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'mobile' => $v['mobile'],
                    'points' => $v['points'],
                    'point_desc' => $v['point_desc'],
                    'admin_name' => $v['admin_name'],
                    'add_time' => date("Y-m-d H:i:s",$v['add_time']),
                );
            }
        }

        $data = array(
            'title' => '用户信用列表',
            'header' => array(
                'mobile' => '手机号',
                'points' => '积分值',
                'point_desc' => '积分描述',
                'admin_name' => '管理员名称',
                'add_time' => '添加时间',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('points_sn', 'type', 'lock_sn'));
        $point_id = $this->request->get('point_id');
        if (isset($this->request->get['point_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'point_id' => $this->request->get['point_id']
            );
            $info = $this->sys_model_points->getpointsInfo($condition);
        }

        $this->assign('data', $info);
        $this->assign('types', get_points_type());
        $this->assign('action', $this->cur_url . '&point_id=' . $point_id);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('points/points_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('points_sn', 'type', 'lock_sn'));

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
    private function validateDelete() {
        return !$this->error;
    }
}