<?php

class ControllerOperationOperationArea extends Controller
{
    private $cur_url = null;
    private $error = null;
    private $cooperator_id;

    /**
     * model admin
     * @var \Sys_Model\Admin
     */
    private $sys_model_admin;

    /**
     * model operation_To_Area
     * @var \Sys_Model\Operation_To_Area
     */
    private $sys_model_area;

    /**
     * ControllerOperationOperationArea constructor.
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');
        $this->sys_model_admin = new Sys_Model\Admin($registry);
        $this->sys_model_area = new Sys_Model\Operation_To_Area($registry);
    }


    /**
     * 运维人员列表带区域
     * @param string $filter ['area_name']  区域名字
     * @param int $page
     */
    public function index()
    {
        $filter = $this->request->get(array('admin_name'));
        $condition = array();
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

        if (!empty($filter['admin_name'])) {
            $condition['admin.admin_name'] = array('like', "%{$filter['admin_name']}%");
        }

        $condition['admin.cooperator_id'] = $this->cooperator_id;

//      运维人员的type是3
        $condition['admin.type'] = 3;
        $order = 'admin.admin_id DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $field = 'admin.admin_name,admin.admin_id,admin.nickname,cooperator.cooperator_name,operations_to_region.region_name';
//      连表查合伙人的名字和运维人员所在的区域
        $join = [
            'cooperator' => 'cooperator.cooperator_id = admin.cooperator_id',
            'operations_to_region' => 'operations_to_region.admin_id = admin.admin_id',
        ];
        $result = $this->sys_model_admin->getAdminList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_admin->getTotalAdmins($condition, $join);
//      合伙人列表
        $this->load->library('sys_model/cooperator', true);
        $cooperatorList = $this->sys_model_cooperator->getCooperatorList();

        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('cooperatorList', $cooperatorList);
        $this->assign('action', $this->cur_url);
        $this->assign('location_url', $this->url->link('operation/operationLocation'));
        $this->assign('repair_url', $this->url->link('operation/operationRecords'));
        $this->assign('area_url', $this->url->link('operation/operationArea'));
        $this->assign('info_url', $this->url->link('operation/operationArea/edit'));
        $this->assign('repair_detail_url', $this->url->link('operation/operationRecords/detail'));
        $page_info = $this->page($total, $page, $rows, $filter, $offset);
        $this->assign('pagination', $page_info['pagination']);
        $this->assign('results', $page_info['results']);

        $this->response->setOutput($this->load->view('operation/operation_area', $this->output));
    }

    /**
     * 编辑运维人员的负责片区
     * @param int $input ['admin_id'] 运维人员id
     * @param string $input ['region_name'] 片区名称
     */
    public function edit()
    {
        //判断是否是post还有进行数据验证
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('admin_id', 'region_name'));

            $condition = array(
                'admin_id' => $input['admin_id']
            );
//            判断有没数据存在，没有插入，有更新
            if ($this->sys_model_area->getInfo($condition)) {
                $data = array(
                    'region_name' => $input['region_name']
                );
                $this->sys_model_area->update($condition, $data);
            } else {
                $data = array(
                    'region_name' => $input['region_name'],
                    'admin_id' => $input['admin_id']
                );
                $this->sys_model_area->add($data);
            }
            $this->session->data['success'] = '编辑区域成功！';
            $this->load->controller('common/base/redirect', $this->url->link('operation/operationArea', '', true));
            exit();
        }

        $condition['admin.admin_id'] = $this->request->get('admin_id');
        $field = 'admin.admin_id,operations_to_region.region_name,admin.admin_name';
//        连表查运维人员负责片区
        $join = [
            'operations_to_region' => 'operations_to_region.admin_id = admin.admin_id'
        ];
        $result = $this->sys_model_admin->getAdminList($condition, '', '0,1', $field, $join);
        $this->assign('data', $result[0]);
        $this->assign('error', $this->error);
        $this->assign('action', $this->cur_url . '&admin_id=' . $this->request->get('admin_id'));
        $this->response->setOutput($this->load->view('operation/operation_area_edit', $this->output));
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

    /**
     * 验证数据
     * @return bool
     */
    private function validateForm()
    {

        $input = $this->request->post(array('admin_id', 'region_name'));
        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        return !$this->error;
    }
}
