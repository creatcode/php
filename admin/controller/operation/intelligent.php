<?php

class ControllerOperationIntelligent extends Controller {

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/cooperator', true);
        $this->load->library('sys_model/intelligent', true);
        $this->assign('lang', $this->language->all());
    }

    /**
     * 长期定位单车
     */
    public function index() {
        $filter = $this->request->get(array('cooperator_id', 'search_time'));

        $condition = array();
        if (isset($filter['cooperator_id']) && (int) $filter['cooperator_id'] >= 0) {
            $condition['bicycle.cooperator_id'] = (int) $filter['cooperator_id'];
        }
        if (!empty($filter['search_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['search_time']);
            $starttime = strtotime($pdr_add_time[0]);
            $endtime = strtotime($pdr_add_time[1]);
            $condition['intelligent.stime'] = array('egt', $starttime);
            $condition['intelligent.etime'] = array('elt', $endtime);
        }
        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'intelligent.id asc';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $field = 'intelligent.*,bicycle.bicycle_sn,bicycle.lock_sn,cooperator.cooperator_name,bicycle.bicycle_id';


        //var_dump($condition);
        $result = $this->sys_model_intelligent->getBicycleList($condition, $limit, $field);
        $total = $this->sys_model_intelligent->getTotalLbsBicycles($condition);
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {


                $item['edit_action'] = $this->url->link('bicycle/bicycle/edit', 'bicycle_id=' . $item['bicycle_id']);
                $item['delete_action'] = $this->url->link('bicycle/bicycle/delete', 'bicycle_id=' . $item['bicycle_id']);
                $item['info_action'] = $this->url->link('bicycle/bicycle/info', 'bicycle_id=' . $item['bicycle_id']);
            }
        }



        // 使用中单车数
        $condition = array(
            'is_using' => 1
        );
        $using_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);
        // 故障单车数
        $condition = array(
            'fault' => 1
        );
        $fault_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);
        $cooperator = $this->sys_model_cooperator->getCooperatorList(array(), '', '', $field = 'cooperator.cooperator_id,cooperator.cooperator_name', $join = array());
        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('total_bicycle', $total);
        $this->assign('using_bicycle', $using_bicycle);
        $this->assign('fault_bicycle', $fault_bicycle);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('export_action', $this->url->link('operation/intelligent/export'));
        $this->assign('cooperator', $cooperator);
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
        $this->assign('time_type', get_time_type());
        $this->response->setOutput($this->load->view('intelligent/bicycle_list', $this->output));
    }

    protected function getDataColumns() {
        $this->setDataColumn($this->language->get('t2'));
        $this->setDataColumn($this->language->get('t3'));
 
        $this->setDataColumn($this->language->get('t19'));
        $this->setDataColumn($this->language->get('t20'));
        return $this->data_columns;
    }

    /**
     * 导出长期定位单车
     */
    public function export() {
        $filter = $this->request->get(array('cooperator_id'));
        $condition = array();
        if (isset($filter['cooperator_id'])) {
            $condition['bicycle.cooperator_id'] = (int) $filter['cooperator_id'];
        }
        $order = 'intelligent.id asc';
        $field = 'intelligent.*,bicycle.bicycle_sn,bicycle.lock_sn,cooperator.cooperator_name,bicycle.bicycle_id';
        $total = $this->sys_model_intelligent->getTotalLbsBicycles($condition);
        $rows = 500;
        $n = ceil($total / $rows);
        for ($i = 0; $i < $n; $i++) {
            $offset = $i * $rows;
            $limit = sprintf('%d, %d', $offset, $rows);
            $bicycles = $this->sys_model_intelligent->getBicycleList($condition, $limit, $field);
            foreach ($bicycles as $key => $bicycle) {
                $list[$offset + $key] = array(
                    'bicycle_sn' => $bicycle['bicycle_sn'],
                    'lock_sn' => $bicycle['lock_sn'],
                    'cooperator_name' => $bicycle['cooperator_name'],
                    'lbs_time' => $bicycle['lbs_times'],
                    'duration' => number_format(abs(($bicycle['etime'] - $bicycle['stime']) / 60), 2),
                );
            }
        }
        $data = array(
            'title' => '长期基站定位单车列表',
            'header' => array(
                'bicycle_sn' => '单车编号',
                'lock_sn' => '车锁编号',
                'cooperator_name' => '合伙人',
                'lbs_time' => '定位次数(次)',
                'duration' => '定位时长(分钟)',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 重复数据
     */
    public function repeatdata() {
        $filter = $this->request->get(array('type'));

        $condition = array();
        if (isset($filter['type'])) {//data_type 是判断读什么数据的依据,默认是单车
            switch ($filter['type']) {
                case 'bicycle':$data_type = 'bicycle';
                    break;
                case 'lock':$data_type = 'lock';
                    break;
                default :$data_type = 'bicycle';
                    break;
            }
        } else {
            $data_type = 'bicycle';
        }
        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }

        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        switch ($data_type) {
            case 'bicycle':
                $data_columns = array($this->language->get('t2'), $this->language->get('t3'), $this->language->get('t4'),$this->language->get('t5') , $this->language->get('t6'),  $this->language->get('t7'));
                $total = $this->sys_model_intelligent->getTotalRepeatdata('rich_bicycle as bicycle', 'bicycle_sn', 'bicycle_sn');
                $result = $this->sys_model_intelligent->getRepeatdataList($data_type, $limit);
                $model = array(
                    'type' => get_bicycle_type(),
                    'lock_type' => get_lock_type(),
                    'is_using' => get_common_boolean()
                );
                if (is_array($result) && !empty($result)) {
                    foreach ($result as &$item) {
                        foreach ($item as &$item2) {
                            foreach ($model as $k => $v) {
                                $item2[$k] = isset($v[$item2[$k]]) ? $v[$item2[$k]] : '';
                            }
                            $item2['edit_action'] = $this->url->link('bicycle/bicycle/edit', 'bicycle_id=' . $item2['bicycle_id']);
                            $item2['delete_action'] = $this->url->link('bicycle/bicycle/delete', 'bicycle_id=' . $item2['bicycle_id']);
                            $item2['info_action'] = $this->url->link('bicycle/bicycle/info', 'bicycle_id=' . $item2['bicycle_id']);
                        }
                    }
                }
                break;
            case 'lock':
                $data_columns = array($this->language->get('t8'), $this->language->get('t9'),  $this->language->get('t10'), $this->language->get('t11'), $this->language->get('t12'), $this->language->get('t13'));
                $total = $this->sys_model_intelligent->getTotalRepeatdata('rich_lock', 'lock_sn', 'lock_sn');
                $result = $this->sys_model_intelligent->getRepeatdataList($data_type, $limit);
                $lock_status = get_lock_status();
                if (is_array($result) && !empty($result)) {
                    foreach ($result as &$item) {
                        foreach ($item as &$item2) {
                            $item2['system_time'] = $item2['system_time'] == 0 ? '没有更新过' : date('Y-m-d H:i:s', $item2['system_time']);
                            $item2['lock_status'] = isset($lock_status[$item2['lock_status']]) ? $lock_status[$item2['lock_status']] : '';
                            $item2['battery'] = $item2['battery'] > 0 ? abs($item2['battery']) . '（正在充电）' : abs($item2['battery']);

                            $item2['edit_action'] = $this->url->link('lock/lock/edit', 'lock_id=' . $item2['lock_id']);
                            $item2['delete_action'] = $this->url->link('lock/lock/delete', 'lock_id=' . $item2['lock_id']);
                            $item2['info_action'] = $this->url->link('lock/lock/info', 'lock_id=' . $item2['lock_id']);
                        }
                    }
                }
                break;
        }

        $tab_data = array(//页面tab数据
            array(//重复bicyc_sn的单车
                'name' => '重复单车',
                'url' => $this->url->link('operation/intelligent/repeatdata', 'type=bicycle'),
                'type' => 'bicycle',
            ),
            array(//重复lock_sn的锁
                'name' => '重复锁',
                'url' => $this->url->link('operation/intelligent/repeatdata', 'type=lock'),
                'type' => 'lock',
            ),
        );



        // 使用中单车数
        $condition = array(
            'is_using' => 1
        );
        $using_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);
        // 故障单车数
        $condition = array(
            'fault' => 1
        );
        $fault_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);

        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('total_bicycle', $total);
        $this->assign('using_bicycle', $using_bicycle);
        $this->assign('fault_bicycle', $fault_bicycle);
        $this->assign('filter', $filter);
        $this->assign('tab_data', $tab_data);
        $this->assign('data_type', $data_type);
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->response->setOutput($this->load->view('intelligent/repeat_list', $this->output));
    }

}
