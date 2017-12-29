<?php
class ControllerOperationGift extends Controller {
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
     * 礼品列表
     */
    public function index() {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'sort_order ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $is_shows = get_common_boolean();

        $result = $this->sys_model_gift->getGiftList($condition, $order, $limit);
        $total = $this->sys_model_gift->getTotalGifts($condition);
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['is_show'] = isset($is_shows[$item['is_show']]) ? $is_shows[$item['is_show']] : '';
                $item['limit_num'] = ($item['is_limit_num'] == 1) ? $item['limit_num'] : '-';

                $item['edit_action']   = $this->url->link('operation/gift/edit', 'gift_id='.$item['gift_id']);
                $item['delete_action'] = $this->url->link('operation/gift/delete', 'gift_id='.$item['gift_id']);
                $item['info_action']   = $this->url->link('operation/gift/info', 'gift_id='.$item['gift_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);
        $this->assign('add_action', $this->url->link('operation/gift/add'));
        $this->assign('return_action', $this->url->link('operation/gift'));


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

        $this->response->setOutput($this->load->view('operation/gift_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('排序');
        $this->setDataColumn('礼品');
        $this->setDataColumn('总数量');
        $this->setDataColumn('售出数量');
        $this->setDataColumn('限制兑换数量（件）');
        $this->setDataColumn('是否上架');
        return $this->data_columns;
    }

    /**
     * 添加礼品
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('gift_name', 'storage', 'is_show', 'is_limit_num', 'limit_num', 'sort_order'));
            $now = time();
            $data = array(
                'gift_name' => $input['gift_name'],
                'storage' => (int)$input['storage'],
                'is_show' => $input['is_show'],
                'is_limit_num' => $input['is_limit_num'],
                'limit_num' => $input['limit_num'],
                'sort_order' => $input['sort_order'],
                'add_time' => $now
            );
            $this->sys_model_gift->addGift($data);

            $this->session->data['success'] = '添加礼品成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '添加礼品：' . $data['gift_name']);
            
            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('operation/gift', $filter, true));
        }

        $this->assign('title', '礼品添加');
        $this->getForm();
    }

    /**
     * 编辑礼品
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('gift_name', 'storage', 'is_show', 'is_limit_num', 'limit_num', 'sort_order'));
            $gift_id = $this->request->get['gift_id'];
            $data = array(
                'gift_name' => $input['gift_name'],
                'storage' => (int)$input['storage'],
                'is_show' => $input['is_show'],
                'is_limit_num' => $input['is_limit_num'],
                'limit_num' => $input['limit_num'],
                'sort_order' => $input['sort_order']
            );
            $condition = array(
                'gift_id' => $gift_id
            );
            $this->sys_model_gift->updateGift($condition, $data);

            $this->session->data['success'] = '编辑礼品成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '编辑礼品：' . $data['gift_name']);

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('operation/gift', $filter, true));
        }

        $this->assign('title', '编辑礼品');
        $this->getForm();
    }

    /**
     * 删除礼品
     */
    public function delete() {
        if (isset($this->request->get['gift_id']) && $this->validateDelete()) {
            $condition = array(
                'gift_id' => $this->request->get['gift_id']
            );
            $this->sys_model_gift->deleteGift($condition);

            $this->session->data['success'] = '删除礼品成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '删除礼品：' . $this->request->get['gift_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('operation/gift', $filter, true));
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('gift_name', 'storage', 'is_show', 'is_limit_num', 'limit_num', 'sort_order'));
        $gift_id = $this->request->get('gift_id');
        if (isset($this->request->get['gift_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'gift_id' => $gift_id
            );
            $info = $this->sys_model_gift->getGiftInfo($condition);
        }

        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&gift_id=' . $gift_id);
        $this->assign('return_action', $this->url->link('operation/gift'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('operation/gift_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('gift_name', 'storage', 'sort_order'));

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