<?php
class ControllerArticleArticle extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载Article Model
        $this->load->library('sys_model/article', true);
    }

    /**
     * 文章列表
     */
    public function index() {
        $filter = $this->request->get(array('filter_type', 'article_title', 'category_id', 'article_sort'));

        $condition = array();
        if (!empty($filter['article_title'])) {
            $condition['article_title'] = array('like', "%{$filter['article_title']}%");
        }
        if (is_numeric($filter['category_id'])) {
            $condition['category_id'] = (int)$filter['category_id'];
        }
        if (is_numeric($filter['article_sort'])) {
            $condition['article_sort'] = (int)$filter['article_sort'];
        }

        $filter_types = array(
            'article_title' => '文章标题',
            'article_sort' => '文章排序'
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

        $order = 'article_sort ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_article->getArticleList($condition, $order, $limit);
        $total = $this->sys_model_article->getTotalArticles($condition);

        // 所有文章分类
        $categories = array();
        $categoryList = $this->sys_model_article->getArticleCategoryList();
        if (is_array($categoryList) && !empty($categoryList)) {
            foreach ($categoryList as $val) {
                $categories[$val['category_id']] = $val['category_name'];
            }
        }

        $model = array(
            'category_id' => $categories,
        );
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                foreach ($model as $k => $v) {
                    $item[$k] = isset($v[$item[$k]]) ? $v[$item[$k]] : '';
                }
                $item['edit_action'] = $this->url->link('article/article/edit', 'article_id='.$item['article_id']);
                $item['delete_action'] = $this->url->link('article/article/delete', 'article_id='.$item['article_id']);
                $item['info_action'] = $this->url->link('article/article/info', 'article_id='.$item['article_id']);
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
        $this->assign('add_action', $this->url->link('article/article/add'));

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

        $this->assign('export_action', $this->url->link('article/article/export'));
        $this->assign('import_action', $this->url->link('article/article/import'));

        $this->response->setOutput($this->load->view('article/article_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('文章标题');
        $this->setDataColumn('文章分类');
        $this->setDataColumn('文章排序');
        return $this->data_columns;
    }

    /**
     * 添加文章
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('article_title', 'category_id', 'article_code', 'article_content', 'article_sort'));
            $data = array(
                'article_title' => $input['article_title'],
                'category_id' => (int)$input['category_id'],
                'article_code' => $input['article_code'],
                'article_content' => $input['article_content'],
                'article_sort' => (int)$input['article_sort'],
            );
            $rec = $this->sys_model_article->addArticle($data);
            if ($rec) {
                // 生成静态网页文件
                $param = array(
                    'title' => $data['article_title'],
                    'content' => $data['article_content']
                );

                $file = sprintf('%sarticle/zh/%s.html', DIR_STATIC, $data['article_code']);
                $content = $this->load->view('article/article_template', $param);
                $fp = fopen($file, "w");
                flock($fp, LOCK_EX);
                fwrite($fp, $content);
                flock($fp, LOCK_UN);
                fclose($fp);
            }

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加文章：'.$data['article_title'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 8,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->session->data['success'] = '添加文章成功！';

            $filter = $this->request->get(array('article_title', 'category_id', 'article_sort'));

            $this->load->controller('common/base/redirect', $this->url->link('article/article', $filter, true));
        }

        $this->assign('title', '文章添加');
        $this->getForm();
    }

    /**
     * 编辑文章
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('article_title', 'category_id', 'article_code', 'article_content', 'article_sort'));
            $article_id = $this->request->get['article_id'];
            $data = array(
                'article_title' => $input['article_title'],
                'category_id' => (int)$input['category_id'],
                'article_code' => $input['article_code'],
                'article_content' => $input['article_content'],
                'article_sort' => (int)$input['article_sort'],
            );
            $condition = array(
                'article_id' => $article_id
            );
            $this->sys_model_article->updateArticle($condition, $data);

            // 生成静态网页文件
            $param = array(
                'title' => $data['article_title'],
                'content' => $data['article_content']
            );

            $file = sprintf('%sarticle/zh/%s.html', DIR_STATIC, $data['article_code']);
            $content = $this->load->view('article/article_template', $param);
            $fp = fopen($file, "w");
            flock($fp, LOCK_EX);
            fwrite($fp, $content);
            flock($fp, LOCK_UN);
            fclose($fp);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '编辑文章：'.$data['article_title'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 8,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->session->data['success'] = '编辑文章成功！';

            $filter = $this->request->get(array('article_title', 'category_id', 'article_sort'));

            $this->load->controller('common/base/redirect', $this->url->link('article/article', $filter, true));
        }

        $this->assign('title', '编辑文章');
        $this->getForm();
    }

    /**
     * 删除文章
     */
    public function delete() {
        if (isset($this->request->get['article_id']) && $this->validateDelete()) {
            $condition = array(
                'article_id' => $this->request->get['article_id']
            );

            $article = $this->sys_model_article->getArticleInfo($condition);
            $file = sprintf('%sarticle/zh/%s.html', DIR_STATIC, $article['article_code']);
            unlink($file);

            $this->sys_model_article->deleteArticle($condition);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '删除文章：' . $this->request->get['article_id'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 8,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->session->data['success'] = '删除文章成功！';
        }
        $filter = $this->request->get(array('article_title', 'category_id', 'article_sort'));
        $this->load->controller('common/base/redirect', $this->url->link('article/article', $filter, true));
    }

//    /**
//     * 导入单车
//     */
//    public function import() {
//        // 获取上传EXCEL文件数据
//        $excelData = $this->load->controller('common/base/importExcel');
//
//        if (is_array($excelData) && !empty($excelData)) {
//            $count = count($excelData);
//            // 从第3行开始
//            if ($count >= 3) {
//                for ($i = 3; $i <= $count; $i++) {
//                    $data = array(
//                        'article_title' => isset($excelData[$i][0]) ? $excelData[$i][0] : '',
//                        'category_id' => 1,
//                        'article_sort' => isset($excelData[$i][1]) ? $excelData[$i][1] : '',
//                        'article_content' => TIMESTAMP
//                    );
//                    $this->sys_model_bicycle->addBicycle($data);
//                }
//            }
//        }
//
//        $this->response->showSuccessResult('', '导入成功');
//    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('filter_type', 'article_title', 'category_id', 'article_sort'));

        $condition = array();
        if (!empty($filter['article_title'])) {
            $condition['article_title'] = array('like', "%{$filter['article_title']}%");
        }
        if (is_numeric($filter['category_id'])) {
            $condition['category_id'] = (int)$filter['category_id'];
        }
        if (is_numeric($filter['article_sort'])) {
            $condition['article_sort'] = (int)$filter['article_sort'];
        }
        $order = 'article_id DESC';
        $limit = '';

        $result = $this->sys_model_article->getArticleList($condition, $order, $limit);
        $list = array();
        if (is_array($result) && !empty($result)) {
            // 所有文章分类
            $categories = array();
            $categoryList = $this->sys_model_article->getArticleCategoryList();
            if (is_array($categoryList) && !empty($categoryList)) {
                foreach ($categoryList as $val) {
                    $categories[$val['category_id']] = $val['category_name'];
                }
            }
            foreach ($result as $v) {
                $list[] = array(
                    'article_title' => $v['article_title'],
                    'category_id' => $categories[$v['category_id']],
                    'article_sort' => $v['article_sort'],
                    'article_content' => $v['article_content'],
                );
            }
        }

        $data = array(
            'title' => '文章列表',
            'header' => array(
                'article_title' => '文章标题',
                'category_id' => '文章分类',
                'article_sort' => '文章排序',
                'article_content' => '文章内容',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('article_title', 'category_id', 'article_code', 'article_content', 'article_sort'));
        $article_id = $this->request->get('article_id');
        if (isset($this->request->get['article_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'article_id' => $this->request->get['article_id']
            );
            $info = $this->sys_model_article->getArticleInfo($condition);
        }

        // 所有文章分类
        $categories = array();
        $categoryList = $this->sys_model_article->getArticleCategoryList();
        if (is_array($categoryList) && !empty($categoryList)) {
            foreach ($categoryList as $val) {
                $categories[$val['category_id']] = $val['category_name'];
            }
        }

        $this->assign('data', $info);
        $this->assign('categories', $categories);
        $this->assign('action', $this->cur_url . '&article_id=' . $article_id);
        $this->assign('return_action', $this->url->link('article/article'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('article/article_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('article_title', 'category_id', 'article_code', 'article_content', 'article_sort'));

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