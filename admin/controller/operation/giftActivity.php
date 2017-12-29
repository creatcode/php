<?php
class ControllerOperationGiftActivity extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载gift Model
        $this->load->library('sys_model/gift', true);
    }

    /**
     * 礼品活动列表
     */
    public function index() {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'activity_id DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $activity_states = get_setting_boolean();

        $result = $this->sys_model_gift->getGiftActivityList($condition, $order, $limit);
        $total = $this->sys_model_gift->getTotalGiftActivities($condition);
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['activity_time'] = sprintf('%s 至 %s', date('Y-m-d', $item['activity_start_time']), date('Y-m-d', $item['activity_end_time']));
                $item['activity_state'] = isset($activity_states[$item['activity_state']]) ? $activity_states[$item['activity_state']] : '';

                $item['edit_action']   = $this->url->link('operation/giftActivity/edit', 'activity_id='.$item['activity_id']);
                $item['delete_action'] = $this->url->link('operation/giftActivity/delete', 'activity_id='.$item['activity_id']);
                $item['info_action']   = $this->url->link('operation/giftActivity/info', 'activity_id='.$item['activity_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);
        $this->assign('add_action', $this->url->link('operation/giftActivity/add'));
        $this->assign('return_action', $this->url->link('operation/giftActivity'));

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

        $this->response->setOutput($this->load->view('operation/gift_activity_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('活动标题');
        $this->setDataColumn('活动时间');
        $this->setDataColumn('状态');
        return $this->data_columns;
    }

    /**
     * 添加礼品活动
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('activity_title', 'activity_time', 'activity_image', 'activity_description', 'activity_state', 'activityGifts'));
            $activity_time = explode(' 至 ', $input['activity_time']);
            $activity_start_time = isset($activity_time[0]) ? strtotime($activity_time[0]) : 0;
            $activity_end_time = isset($activity_time[1]) ? bcadd(86399, strtotime($activity_time[1])) : 0;
            $now = time();
            $data = array(
                'activity_title' => $input['activity_title'],
                'activity_start_time' => $activity_start_time,
                'activity_end_time' => $activity_end_time,
                'activity_image' => $input['activity_image'],
                'activity_description' => $input['activity_description'],
                'activity_state' => (int)$input['activity_state'],
                'activity_add_time' => $now
            );
            $activity_id = $this->sys_model_gift->addGiftActivity($data);

            // 关联活动礼品
            foreach ($input['activityGifts'] as $activityGift) {
                $data = array(
                    'activity_id' => $activity_id,
                    'gift_id' => $activityGift,
                );
                $this->sys_model_gift->addGiftActivityToGift($data);
            }

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '添加礼品活动：' . $input['activity_title']);

            $this->session->data['success'] = '添加礼品活动成功！';
            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('operation/giftActivity', $filter, true));
        }

        $this->assign('title', '礼品活动添加');
        $this->getForm();
    }

    /**
     * 编辑礼品活动
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('activity_title', 'activity_time', 'activity_image', 'activity_description', 'activity_state', 'activityGifts'));
            $activity_time = explode(' 至 ', $input['activity_time']);
            $activity_start_time = isset($activity_time[0]) ? strtotime($activity_time[0]) : 0;
            $activity_end_time = isset($activity_time[1]) ? bcadd(86399, strtotime($activity_time[1])) : 0;
            $activity_id = $this->request->get['activity_id'];
            $data = array(
                'activity_title' => $input['activity_title'],
                'activity_start_time' => $activity_start_time,
                'activity_end_time' => $activity_end_time,
                'activity_image' => $input['activity_image'],
                'activity_description' => $input['activity_description'],
                'activity_state' => (int)$input['activity_state'],
            );
            $condition = array(
                'activity_id' => $activity_id
            );
            $this->sys_model_gift->updateGiftActivity($condition, $data);

            // 删除已关联的礼品
            $condition = array(
                'activity_id' => $activity_id
            );
            $this->sys_model_gift->deleteGiftActivityToGift($condition);
            // 关联活动礼品
            foreach ($input['activityGifts'] as $activityGift) {
                $data = array(
                    'activity_id' => $activity_id,
                    'gift_id' => $activityGift,
                );
                $this->sys_model_gift->addGiftActivityToGift($data);
            }

            $this->session->data['success'] = '编辑礼品活动成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '编辑礼品活动：' . $input['activity_title']);

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('operation/giftActivity', $filter, true));
        }

        $this->assign('title', '编辑礼品活动');
        $this->getForm();
    }

    /**
     * 删除礼品活动
     */
    public function delete() {
        if (isset($this->request->get['activity_id']) && $this->validateDelete()) {
            $condition = array(
                'activity_id' => $this->request->get['activity_id']
            );
            $this->sys_model_gift->deleteGiftActivity($condition);

            $this->session->data['success'] = '删除礼品成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '删除礼品：' . $this->request->get['activity_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('operation/giftActivity', $filter, true));
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('activity_title', 'activity_time', 'activity_image', 'activity_description', 'activity_state', 'activityGifts'));
        $activity_id = $this->request->get('activity_id');
        $activityGifts = !empty($info['activityGifts']) && is_array($info['activityGifts']) ? $info['activityGifts'] :array();
        if (isset($this->request->get['activity_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'activity_id' => $activity_id
            );
            $info = $this->sys_model_gift->getGiftActivityInfo($condition);
            if (!empty($info['activity_start_time']) && !empty($info['activity_end_time'])) {
                $info['activity_time'] = sprintf('%s 至 %s', date('Y-m-d', $info['activity_start_time']), date('Y-m-d', $info['activity_end_time']));
            } else {
                $info['activity_time'] = '';
            }
            $field = 'gift_id';
            $activityGiftList = $this->sys_model_gift->getGiftActivityToGiftList($condition, $field);
            foreach ($activityGiftList as $item) {
                $activityGifts[] = $item['gift_id'];
            }
        }

        $info['activity_image_url'] = !empty($info['activity_image']) ? (HTTP_IMAGE . $info['activity_image']): HTTP_IMAGE . 'images/default.jpg';

        // 所有礼品
        $gifts = $this->sys_model_gift->getGiftList();

        $this->assign('data', $info);
        $this->assign('gifts', $gifts);
        $this->assign('activityGifts', $activityGifts);
        $this->assign('action', $this->cur_url . '&activity_id=' . $activity_id);
        $this->assign('upload_action', $this->url->link('common/upload'));
        $this->assign('return_action', $this->url->link('operation/giftActivity'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('operation/gift_activity_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('activity_title', 'activity_time', 'activity_image', 'activity_description', 'activityGifts'));

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