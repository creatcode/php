<?php
class ControllerArticleCategory extends Controller {
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载article Model
        $this->load->library('sys_model/article', true);
    }

    /**
     * 文章分类列表
     */
    public function index() {
        $filter = $this->request->get(array('category_name', 'category_sort'));

        $condition = array();

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'category_sort ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_article->getArticleCategoryList($condition, $order, $limit);
        $total = $this->sys_model_article->getTotalArticleCategories($condition);

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {

                $item['edit_action'] = $this->url->link('article/category/edit', 'category_id='.$item['category_id']);
                $item['delete_action'] = $this->url->link('article/category/delete', 'category_id='.$item['category_id']);
                $item['info_action'] = $this->url->link('article/category/info', 'category_id='.$item['category_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('article/category/add'));

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

        $this->response->setOutput($this->load->view('article/category_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('分类名称');
        $this->setDataColumn('排序');
        return $this->data_columns;
    }

    /**
     * 添加文章分类
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('category_name', 'category_sort'));
            $data = array(
                'category_name' => $input['category_name'],
                'category_sort' => (int)$input['category_sort']
            );
            $this->sys_model_article->addArticleCategory($data);

            $this->session->data['success'] = '添加文章分类成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加文章分类：' . $data['category_name'],
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $filter = array();

            $this->load->controller('common/base/redirect', $this->url->link('article/category', $filter, true));
        }

        $this->assign('title', '文章分类添加');
        $this->getForm();
    }

    /**
     * 编辑文章分类
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('category_name', 'category_sort'));
            $category_id = $this->request->get['category_id'];
            $data = array(
                'category_name' => $input['category_name'],
                'category_sort' => (int)$input['category_sort']
            );
            $condition = array(
                'category_id' => $category_id
            );
            $this->sys_model_article->updateArticleCategory($condition, $data);

            $this->session->data['success'] = '编辑文章分类成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '编辑文章分类：' . $data['category_name'],
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $filter = array();

            $this->load->controller('common/base/redirect', $this->url->link('article/category', $filter, true));
        }

        $this->assign('title', '编辑文章分类');
        $this->getForm();
    }

    /**
     * 删除文章分类
     */
    public function delete() {
        if (isset($this->request->get['category_id']) && $this->validateDelete()) {
            $condition = array(
                'category_id' => $this->request->get['category_id']
            );
            $this->sys_model_article->deleteArticleCategory($condition);

            $this->session->data['success'] = '删除文章分类成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '删除文章分类：' . $this->request->get['category_id'],
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('article/category', $filter, true));
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('category_name', 'category_sort'));
        $category_id = $this->request->get('category_id');
        if (isset($this->request->get['category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'category_id' => $this->request->get['category_id']
            );
            $info = $this->sys_model_article->getArticleCategoryInfo($condition);
        }

        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&category_id=' . $category_id);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('article/category_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('category_name', 'category_sort'));

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