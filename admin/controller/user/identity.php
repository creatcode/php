<?php
class ControllerUserIdentity extends Controller {
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载user Model
        $this->load->library('sys_model/identity', true);
    }

    public function index() {
        $filter = $this->request->get(array('filter_type', 'il_id', 'il_user_id', 'il_user_mobile', 'il_real_name', 'il_identification', 'il_cert_time', 'il_has_photo', 'il_verify_state', 'il_verify_error_code', 'il_verify_error_desc', 'il_charged', 'il_api_reply'));

        $condition = array();
        if (is_numeric($filter['il_id'])) {
            $condition['il_id'] = (int)$filter['il_id'];
        }
        if (is_numeric($filter['il_user_id'])) {
            $condition['il_user_id'] = (int)$filter['il_user_id'];
        }
        if (!empty($filter['il_user_mobile'])) {
            $condition['il_user_mobile'] = array('like', "%{$filter['il_user_mobile']}%");
        }
        if (!empty($filter['il_real_name'])) {
            $condition['il_real_name'] = array('like', "%{$filter['il_real_name']}%");
        }
        if (!empty($filter['il_identification'])) {
            $condition['il_identification'] = array('like', "%{$filter['il_identification']}%");
        }
        if (!empty($filter['il_cert_time'])) {
            $il_cert_time = explode(' 至 ', $filter['il_cert_time']);
            $condition['il_cert_time'] = array(
                array('egt', strtotime($il_cert_time[0])),
                array('elt', bcadd(86399, strtotime($il_cert_time[1])))
            );
        }
        if (is_numeric($filter['il_has_photo'])) {
            $condition['il_has_photo'] = (int)$filter['il_has_photo'];
        }
        if (is_numeric($filter['il_verify_state'])) {
            $condition['il_verify_state'] = (int)$filter['il_verify_state'];
        }
        if (is_numeric($filter['il_verify_error_code'])) {
            $condition['il_verify_error_code'] = (int)$filter['il_verify_error_code'];
        }
        if (!empty($filter['il_verify_error_desc'])) {
            $condition['il_verify_error_desc'] = array('like', "%{$filter['il_verify_error_desc']}%");
        }
        if (is_numeric($filter['il_charged'])) {
            $condition['il_charged'] = (int)$filter['il_charged'];
        }

        if (!empty($filter['il_api_reply'])) {
            $condition['il_api_reply'] = array('like', "%{$filter['il_api_reply']}%");
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'il_cert_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_identity->getIdentityList($condition, '*', $order, $limit);
        $total = $this->sys_model_identity->getIdentityCount($condition);

        $available_states = get_common_boolean();
        $result_states = get_common_result();
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['il_has_photo'] = isset($available_states[$item['il_has_photo']]) ? $available_states[$item['il_has_photo']] : '';
                $item['il_charged'] = isset($available_states[$item['il_charged']]) ? $available_states[$item['il_charged']] : '';
                $item['il_verify_state'] = isset($result_states[$item['il_verify_state']]) ? $result_states[$item['il_verify_state']] : '';
                $item['il_cert_time'] = date("Y-m-d H:i:s",$item['il_cert_time']);
            }
        }

        $filter_types = array(
            'il_user_mobile' => '手机号',
            'il_real_name' => '真实姓名',
            'il_identification' => '身份证',
            'il_verify_error_desc' => '结果描述',
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('available_states', $available_states);
        $this->assign('result_states', $result_states);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);

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

        $this->assign('export_action', $this->url->link('user/identity/export'));

        $this->response->setOutput($this->load->view('user/identity_list', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('filter_type', 'il_id', 'il_user_id', 'il_user_mobile', 'il_real_name', 'il_identification', 'il_cert_time', 'il_has_photo', 'il_verify_state', 'il_verify_error_code', 'il_verify_error_desc', 'il_charged', 'il_api_reply'));

        $condition = array();
        if (is_numeric($filter['il_id'])) {
            $condition['il_id'] = (int)$filter['il_id'];
        }
        if (is_numeric($filter['il_user_id'])) {
            $condition['il_user_id'] = (int)$filter['il_user_id'];
        }
        if (!empty($filter['il_user_mobile'])) {
            $condition['il_user_mobile'] = array('like', "%{$filter['il_user_mobile']}%");
        }
        if (!empty($filter['il_real_name'])) {
            $condition['il_real_name'] = array('like', "%{$filter['il_real_name']}%");
        }
        if (!empty($filter['il_identification'])) {
            $condition['il_identification'] = array('like', "%{$filter['il_identification']}%");
        }
        if (!empty($filter['il_cert_time'])) {
            $il_cert_time = explode(' 至 ', $filter['il_cert_time']);
            $condition['il_cert_time'] = array(
                array('egt', strtotime($il_cert_time[0])),
                array('elt', bcadd(86399, strtotime($il_cert_time[1])))
            );
        }
        if (is_numeric($filter['il_has_photo'])) {
            $condition['il_has_photo'] = (int)$filter['il_has_photo'];
        }
        if (is_numeric($filter['il_verify_state'])) {
            $condition['il_verify_state'] = (int)$filter['il_verify_state'];
        }
        if (is_numeric($filter['il_verify_error_code'])) {
            $condition['il_verify_error_code'] = (int)$filter['il_verify_error_code'];
        }
        if (!empty($filter['il_verify_error_desc'])) {
            $condition['il_verify_error_desc'] = array('like', "%{$filter['il_verify_error_desc']}%");
        }
        if (is_numeric($filter['il_charged'])) {
            $condition['il_charged'] = (int)$filter['il_charged'];
        }

        if (!empty($filter['il_api_reply'])) {
            $condition['il_api_reply'] = array('like', "%{$filter['il_api_reply']}%");
        }

        $order = 'il_cert_time DESC';
        $limit = '';

        $result = $this->sys_model_identity->getIdentityList($condition, '*', $order, $limit);

        $list = array();
        if (is_array($result) && !empty($result)) {
            $available_states = get_common_boolean();
            $result_states = get_common_result();
            foreach ($result as $v) {
                $list[] = array(
                    'il_user_mobile' => $v['il_user_mobile'],
                    'il_real_name' => $v['il_real_name'],
                    'il_identification' => $v['il_identification'],
                    'il_cert_time' => date("Y-m-d Y:m:s",$v['il_cert_time']),
                    'il_has_photo' => $available_states[$v['il_has_photo']],
                    'il_verify_state' => $result_states[$v['il_verify_state']],
                    'il_verify_error_desc' => $v['il_verify_error_desc'],
                    'il_charged' => $v['il_charged'],
                );
            }
        }

        $data = array(
            'title' => '实名验证记录',
            'header' => array(
                'il_user_mobile' => '手机号',
                'il_real_name' => '真实名字',
                'il_identification' => '身份证',
                'il_cert_time' => '提交时间',
                'il_has_photo' => '是否有返照',
                'il_verify_state' => '认证结果',
                'il_verify_error_desc' => '结果描述',
                'il_charged' => '是否已扣费',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    // 表格字段
    protected function getDataColumns() {
        $this->setDataColumn('手机号');
        $this->setDataColumn('真实名字');
        $this->setDataColumn('身份证');
        $this->setDataColumn('提交时间');
        $this->setDataColumn('是否有返照');
        $this->setDataColumn('认证结果');
        $this->setDataColumn('结果描述');
        $this->setDataColumn('是否已扣费');
        return $this->data_columns;
    }


}