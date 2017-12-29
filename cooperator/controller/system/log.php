<?php
class ControllerSystemLog extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载log Model
        $this->load->library('sys_model/admin_log', true);
    }

    /**
     * 日志列表
     */
    public function index() {
        $filter = array();
        $condition = array();

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'log_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_admin_log->getAdminLogList($condition, $order, $limit);
        $total = $this->sys_model_admin_log->getTotalAdminLogs($condition);

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('log/log/add'));

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

        $this->assign('export_action', $this->url->link('system/log/export'));

        $this->response->setOutput($this->load->view('system/log_list', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        $condition = array();

        $order = 'log_id DESC';
        $limit = '';

        $result = $this->sys_model_admin_log->getAdminLogList($condition, $order, $limit);
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'admin_name' => $v['admin_name'],
                    'log_description' => $v['log_description'],
                    'log_ip' => $v['log_ip'],
                    'log_time' => $v['log_time'],
                );
            }
        }

        $data = array(
            'title' => '操作日志列表',
            'header' => array(
                'admin_name' => '管理员',
                'log_description' => '操作内容',
                'log_ip' => '操作ip',
                'log_time' => '操作时间',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('管理员');
        $this->setDataColumn('操作内容');
        $this->setDataColumn('操作ip');
        $this->setDataColumn('操作时间');
        return $this->data_columns;
    }
}