<?php
class ControllerOperationViolation extends Controller {
    private $cooperator_id = null;
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');

        // 加载fault Model
        $this->load->library('sys_model/fault', true);
    }

    /**
     * 故障记录列表
     */
    public function index() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //AJAX请求
            if ($this->request->get('method') == 'json') {
                $this->apiIndex();
                return;
            }
        }

        $filter = $this->request->get(array('filter_type', 'bicycle_sn', 'user_name', 'content', 'type', 'add_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['user_name'])) {
            $condition['user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (!empty($filter['content'])) {
            $condition['content'] = array('like', "%{$filter['content']}%");
        }
        if (!empty($filter['type'])) {
            $condition['type'] = array('like', "%{$filter['type']}%");
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('gt', strtotime($add_time[0])),
                array('lt', bcadd(86399, strtotime($add_time[1])))
            );
        }

        $filter_types = array(
            'bicycle_sn' => '单车编号',
            'user_name' => '上报用户',
            'content' => '上报描述'
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

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_fault->getIllegalParkingList($condition, $order, $limit);
        $total = $this->sys_model_fault->getTotalIllegalParking($condition);

        $model = array(
            'type' => get_parking_type()
        );

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                foreach ($model as $k => $v) {
                    $item[$k] = isset($v[$item[$k]]) ? $v[$item[$k]] : '';
                }

                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['edit_action'] = $this->url->link('operation/violation/edit', 'parking_id='.$item['parking_id']);
                $item['delete_action'] = $this->url->link('operation/violation/delete', 'parking_id='.$item['parking_id']);
                $item['info_action'] = $this->url->link('operation/violation/info', 'parking_id='.$item['parking_id']);
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
        $this->assign('add_action', $this->url->link('operation/violation/add'));

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

        $this->assign('export_action', $this->url->link('operation/violation/export'));

        $this->response->setOutput($this->load->view('operation/violation_list', $this->output));
    }

    /**
     * index AJAX请求
     */
    protected function apiIndex() {
        if (isset($this->request->post['page'])) {
            $page = (int)$this->request->post['page'];
        } else {
            $page = 1;
        }

        $condition = array(
            'cooperator_id' => $this->cooperator_id,
            'processed' => 0
			
        );
        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_fault->getIllegalParkingList($condition, $order, $limit);
        $total = $this->sys_model_fault->getTotalIllegalParking($condition);
        $this->load->library('sys_model/bicycle', true);
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'bicycle_sn' => $v['bicycle_sn'],
                    'add_time' => date('Y-m-d H:i:s', $v['add_time']),
                    'uri' => $this->url->link('admin/index') . '#' . $v['bicycle_sn'],
                    'bicycle_id'=> $this->sys_model_bicycle->getBicycleInfo(array('bicycle_sn'=> $v['bicycle_sn']))['bicycle_id'],
                );
            }
        }

        $statisticsMessages = $this->load->controller('common/base/statisticsMessages', $condition);

        $data = array(
            'title' => array(
                'bicycle_sn' => '单车编号',
                'add_time' => '上报时间',
            ),
            'list' => $list,
            'page' => $page,
            'total' => ceil($total / $rows),
            'statistics' => $statisticsMessages
        );

        $this->response->showSuccessResult($data, '获取成功');
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('单车编号');
        $this->setDataColumn('上报用户');
        $this->setDataColumn('上报描述');
        $this->setDataColumn('违规类型');
        $this->setDataColumn('上报时间');
        return $this->data_columns;
    }

    /**
     * 删除故障记录
     */
    public function delete() {
        if (isset($this->request->get['violation_id']) && $this->validateDelete()) {
            $condition = array(
                'violation_id' => $this->request->get['violation_id'],
                'cooperator_id' => $this->cooperator_id
            );
            $this->sys_model_fault->deleteFault($condition);

            $this->session->data['success'] = '删除故障记录成功！';
        }
        $filter = $this->request->get(array('filter_type', 'violation_sn', 'type', 'lock_sn', 'is_using'));
        $this->load->controller('common/base/redirect', $this->url->link('operation/violation', $filter, true));
    }

    /**
     * 故障记录详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $parking_id = $this->request->get('parking_id');
        $condition = array(
            'parking_id' => $parking_id,
            'cooperator_id' => $this->cooperator_id
        );
        $info = $this->sys_model_fault->getIllegalParkingInfo($condition);
        if (!empty($info)) {

            $model = array(
                'type' => get_parking_type()
            );
            foreach ($model as $k => $v) {
                $info[$k] = isset($v[$info[$k]]) ? $v[$info[$k]] : '';
            }

            $info['add_time'] = !empty($info['add_time']) ? date('Y-m-d H:i:s', $info['add_time']) : '';
        }

        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('operation/violation'));

        $this->response->setOutput($this->load->view('operation/violation_info', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('filter_type', 'bicycle_sn', 'user_name', 'content', 'type', 'add_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['user_name'])) {
            $condition['user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (!empty($filter['content'])) {
            $condition['content'] = array('like', "%{$filter['content']}%");
        }
        if (!empty($filter['type'])) {
            $condition['type'] = array('like', "%{$filter['type']}%");
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('gt', strtotime($add_time[0])),
                array('lt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        $order = 'add_time DESC';
        $limit = '';

        $result = $this->sys_model_fault->getIllegalParkingList($condition, $order, $limit);
        $list = array();
        if (is_array($result) && !empty($result)) {
            $get_parking_type = get_parking_type();
            foreach ($result as $v) {
                $list[] = array(
                    'bicycle_sn' => $v['bicycle_sn'],
                    'user_name' => $v['user_name'],
                    'content' => $v['content'],
                    'type' => $get_parking_type[$v['type']],
                    'add_time' => date("Y-m-d H:i:s",$v['add_time']),
                );
            }
        }

        $data = array(
            'title' => '违规停放列表',
            'header' => array(
                'bicycle_sn' => '单车编号',
                'user_name' => '上报用户',
                'content' => '上报描述',
                'type' => '违规类型',
                'add_time' => '上报时间',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 验证删除条件
     */
    private function validateDelete() {
        return !$this->error;
    }
}