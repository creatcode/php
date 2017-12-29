<?php
class ControllerRegionRegion extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载region Model
        $this->load->library('sys_model/region', true);
    }

    /**
     * 区域列表
     */
    public function index() {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'region_sort ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_region->getRegionList($condition, $order, $limit);
        $total = $this->sys_model_region->getTotalRegions($condition);

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {

                $item['edit_action'] = $this->url->link('region/region/edit', 'region_id='.$item['region_id']);
                $item['delete_action'] = $this->url->link('region/region/delete', 'region_id='.$item['region_id']);
                $item['info_action'] = $this->url->link('region/region/info', 'region_id='.$item['region_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);
        $this->assign('add_action', $this->url->link('region/region/add'));
        $this->assign('return_action', $this->url->link('region/region'));


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

        $this->response->setOutput($this->load->view('region/region_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('排序');
        $this->setDataColumn('区域');
        return $this->data_columns;
    }

    /**
     * 添加区域
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('region_name', 'region_sort', 'region_city_code', 'region_bounds', 'region_bounds_northeast_lng', 'region_bounds_northeast_lat', 'region_bounds_southwest_lng', 'region_bounds_southwest_lat', 'region_charge_time', 'region_charge_fee'));
            $now = time();
            $condition = array(
                'region_city_code' => (int)$input['region_city_code']
            );
            $field = 'region_city_ranking';
            $region_city_ranking = ((int)$this->sys_model_region->getMaxRegions($condition, $field) + 1);
            $data = array(
                'region_name' => $input['region_name'],
                'region_sort' => (int)$input['region_sort'],
                'region_city_code' => $input['region_city_code'],
                'region_city_ranking' => (int)$region_city_ranking,
                'region_bounds' => $input['region_bounds'],
                'region_bounds_northeast_lng' => $input['region_bounds_northeast_lng'],
                'region_bounds_northeast_lat' => $input['region_bounds_northeast_lat'],
                'region_bounds_southwest_lng' => $input['region_bounds_southwest_lng'],
                'region_bounds_southwest_lat' => $input['region_bounds_southwest_lat'],
                'region_charge_time' => (int)$input['region_charge_time'],
                'region_charge_fee' => $input['region_charge_fee'],
                'add_time' => $now
            );
            $this->sys_model_region->addRegion($data);

            $this->session->data['success'] = '添加区域成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '添加区域：' . $data['region_name']);
            
            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('region/region', $filter, true));
        }

        $this->assign('title', '区域添加');
        $this->getForm();
    }

    /**
     * 编辑区域
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('region_name', 'region_sort', 'region_city_code', 'region_bounds', 'region_bounds_northeast_lng', 'region_bounds_northeast_lat', 'region_bounds_southwest_lng', 'region_bounds_southwest_lat', 'region_charge_time', 'region_charge_fee'));
            $region_id = $this->request->get['region_id'];
            $data = array(
                'region_name' => $input['region_name'],
                'region_sort' => (int)$input['region_sort'],
                'region_city_code' => $input['region_city_code'],
                'region_bounds' => $input['region_bounds'],
                'region_bounds_northeast_lng' => $input['region_bounds_northeast_lng'],
                'region_bounds_northeast_lat' => $input['region_bounds_northeast_lat'],
                'region_bounds_southwest_lng' => $input['region_bounds_southwest_lng'],
                'region_bounds_southwest_lat' => $input['region_bounds_southwest_lat'],
                'region_charge_time' => (int)$input['region_charge_time'],
                'region_charge_fee' => $input['region_charge_fee'],
            );
            $condition = array(
                'region_id' => $region_id
            );
            $this->sys_model_region->updateRegion($condition, $data);

            $this->session->data['success'] = '编辑区域成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '编辑区域：' . $data['region_name']);

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('region/region', $filter, true));
        }

        $this->assign('title', '编辑区域');
        $this->getForm();
    }

    /**
     * 删除区域
     */
    public function delete() {
        if (isset($this->request->get['region_id']) && $this->validateDelete()) {
            $condition = array(
                'region_id' => $this->request->get['region_id']
            );
            $this->sys_model_region->deleteRegion($condition);

            $this->session->data['success'] = '删除区域成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '删除区域：' . $this->request->get['region_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('region/region', $filter, true));
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('region_name', 'region_sort', 'region_city_code', 'region_bounds', 'region_bounds_northeast_lng', 'region_bounds_northeast_lat', 'region_bounds_southwest_lng', 'region_bounds_southwest_lat'));
        $region_id = $this->request->get('region_id');
        if (isset($this->request->get['region_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'region_id' => $this->request->get['region_id']
            );
            $info = $this->sys_model_region->getRegionInfo($condition);
        }
        $info['region_bounds'] = !empty($info['region_bounds']) ? $info['region_bounds'] : '[]';

        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&region_id=' . $region_id);
        $this->assign('return_action', $this->url->link('region/region'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('region/region_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('region_name', 'region_sort', 'region_city_code', 'region_bounds_northeast_lng', 'region_bounds_northeast_lat', 'region_bounds_southwest_lng', 'region_bounds_southwest_lat'));

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