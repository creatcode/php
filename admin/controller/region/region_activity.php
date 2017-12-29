<?php
class ControllerRegionRegionActivity extends Controller {
	private $error = null;
	private $cur_url = null;
	
	public function __construct($registry) {
		parent::__construct($registry);
		
		$this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';
		
		$this->load->library('sys_model/region', true);
        $this->load->library('logic/admin', true);
	}
	
	public function index() {
		$filter = $this->request->get(array('region_id', 'add_time', 'effect_time'));
		
		$condition = array();
		if (!empty($filter['region_id'])) {
			$condition['region_id'] = $filter['region_id'];
		}
		//添加时间
		if (!empty($filter['add_time'])) {
			$add_time = explode(' 至 ', $filter['add_time']);
			$condition['add_time'] = array(
				array('gt', strtotime($add_time[0])),
				array('lt', bcadd(86399, strtotime($add_time[1])))
			);
		}
		
		
		
		if (isset($this->request->get['page'])) {
			$page = (int) $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$region_list = $this->sys_model_region->getRegionList();
		$output_region = array();
		foreach ($region_list as $region) {
			$output_region[$region['region_id']] = $region['region_name'];
		}
		
		$order = 'add_time DESC';
		$rows = $this->config->get('config_limit_admin');
		$offset = ($page - 1) * $rows;
		$limit = sprintf('%d, %d', $offset, $rows);

		$result = $this->sys_model_region->getRegionActivities($condition, $order, $limit);
		$total =  $this->sys_model_region->getRegionActivityTotal($condition);
		
		if (!empty($result)) {
			foreach ($result as &$item) {
                $item['region_name'] = isset($output_region[$item['region_id']]) ? $output_region[$item['region_id']] : '未分配区域';
				$item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '';
				$item['start_time'] = $item['start_time'] ? date('Y-m-d H:i:s', $item['start_time']) : '';
				$item['end_time'] = $item['end_time'] ? date('Y-m-d H:i:s', $item['end_time']) : '';
                $item['edit_action'] = $this->url->link('region/region_activity/edit', 'activity_id=' . $item['activity_id']);
                $item['delete_action'] = $this->url->link('region/region_activity/delete', 'activity_id=' . $item['activity_id']);
			}
		}
		
		$data_columns = $this->getDataColumns();
		$this->assign('data_columns', $data_columns);
		$this->assign('data_rows', $result);
		$this->assign('region_item', $output_region);
		$this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('region/region_activity/add'));

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

        $this->response->setOutput($this->load->view('region/region_activity_list', $this->output));
	}
	
	private function getDataColumns() {
		$this->setDataColumn('所在区域');
		$this->setDataColumn('开始时间');
		$this->setDataColumn('结束时间');
		$this->setDataColumn('优惠金额');
		$this->setDataColumn('添加时间');
		return $this->data_columns;
	}

	private $region = null;

	public function add() {
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $input = $this->request->post(array('region_id', 'start_time', 'end_time', 'price'));
            $data = array(
                'region_id' => $input['region_id'],
                'start_time' => strtotime($input['start_time']),
                'end_time' => bcadd(86399, strtotime($input['end_time'])),
                'price' => $input['price'],
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'add_time' => time(),
            );
            $this->sys_model_region->addRegionActivity($data);
            $this->session->data['success'] = '添加区域成功！';

            $this->load->controller('common/base/adminLog', '添加区域活动' . $this->region['region_name'] . '活动价格' . $data['price']);

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('region/region_activity', $filter, true));
        }

        $this->assign('title', '添加区域活动');
        $this->getForm();
	}
	
	public function edit() {
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $input = $this->request->post(array('region_id', 'start_time', 'end_time', 'price'));
            $activity_id = $this->request->get['activity_id'];
            $data = array(
                'region_id' => $input['region_id'],
                'start_time' => strtotime($input['start_time']),
                'end_time' => bcadd(86399, strtotime($input['end_time'])),
                'price' => $input['price']
            );

            $where = array('activity_id' => $activity_id);

            $this->sys_model_region->editRegionActivity($where, $data);

            $this->session->data['success'] = '编辑成功！';

            $this->load->controller('common/base/adminLogin', '编辑活动:' . $this->region['region_name']);

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('region/region_activity', $filter, true));
        }

        $this->assign('title', '编辑区域活动');
        $this->getForm();
	}
	
	private function validateForm() {
        if (!empty($this->request->post['effect_time'])) {
            $effect_times = explode(' 至 ', $this->request->post['effect_time']);
            $this->request->post['start_time'] = $effect_times[0];
            $this->request->post['end_time'] = $effect_times[1];
            if (strtotime($effect_times[0]) > strtotime($effect_times[1])) {
                $this->error['effect_time'] = '开始时间不能大于结束时间';
            }

            $input = $this->request->post(array('start_time', 'end_time'));
            foreach ($input as $k => $v) {
                if (empty($v)) {
                    $this->error['effect_time'] = '请添加' . $this->getFieldName($k);
                }
                if (!empty($v)) {
                    $v = strtotime($v);
                    $info = $this->sys_model_region->getRegionActivityInfo("(start_time <= $v AND $v <= end_time) AND region_id={$this->request->post['region_id']}");
                    if (!empty($info)) {
                        $gets = $this->request->get(array('activity_id'));
                        if ($info['activity_id'] != $gets['activity_id']) {
                            $this->error['effect_time'] = $this->getFieldName($k) . '不能跟其他活动时间重叠';
                        }
                    }
                }
            }
        } else {
            $this->error['effect_time'] = '活动时间范围不能空';
        }

		$region_info = $this->sys_model_region->getRegionInfo(array('region_id' => $this->request->post['region_id']));
        if (!empty($region_info)) {
            $price = $this->request->post['price'];
            if ($price > $region_info['region_charge_fee']) {
                $this->error['price'] = '活动价格不能大于地区价格，不然叫什么活动';
            }
            $this->region = $region_info;
        } else {
            $this->error['region_id'] = '不存在次地区，请重新选择地区';
        }

		if ($this->error) {
            $this->error['warning'] = '警告：存在错误，请检测！';
        }

        return !$this->error;
	}

	private function validateDelete() {
        return !$this->error;
    }
	
	private function getFieldName($key) {
		$result = array(
			'start_time' => '开始时间',
			'end_time' => '结束时间'
		);

		if (isset($result[$key])) {
			return $result[$key];
		} else {
			return '';
		}
	}

	public function delete() {
        if (isset($this->request->get['activity_id']) && $this->validateDelete()) {
            $condition = array(
                'activity_id' => $this->request->get['activity_id']
            );

            $this->sys_model_region->deleteRegionActivityInfo($condition);

            $this->load->controller('common/base/adminLog', '删除区域：');
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('region/region_activity', $filter, true));
    }

	private function getForm() {
		$info = $this->request->post(array('region_id', 'effect_time', 'price'));
        $gets = $this->request->get(array('activity_id'));
        $select_id = 0;
        if (isset($this->request->get['activity_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $where = array('activity_id' => $gets['activity_id']);
            $info = $this->sys_model_region->getRegionActivityInfo($where);
            if (!empty($info)) {
                $info['effect_time'] = date('Y-m-d', $info['start_time']) . ' 至 ' . date('Y-m-d', $info['end_time']);
                $select_id = $info['region_id'];
            }
        }
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $select_id = $this->request->post['region_id'];
        }

        $region_list = $this->sys_model_region->getRegionList();
        $output_region = array();
        foreach ($region_list as $region) {
            $output_region[$region['region_id']] = $region['region_name'];
        }

        $this->assign('is_edit', $gets['activity_id'] ? 1 : 0);
        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&activity_id=' . $gets['activity_id']);
        $this->assign('error', $this->error);
        $this->assign('region_activity_options', $output_region);
        $this->assign('select_id', $select_id);
        $this->assign('return_action', $this->url->link('region/region_activity'));

        $this->response->setOutput($this->load->view('region/region_activity_form', $this->output));
	}
}
?>