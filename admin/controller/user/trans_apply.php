<?php
/**
 * [转账申请]
 * @Author   vincent
 * @DateTime 2017-08-09T22:34:02+0800
 */
error_reporting(E_ALL); //E_ALL
function cache_shutdown_error() {
    $_error = error_get_last();
    if ($_error && in_array($_error['type'], array(1, 4, 16, 64, 256, 4096, E_ALL))) {
        echo '<font color=red>你的代码出错了：</font></br>';
        echo '致命错误:' . $_error['message'] . '</br>';
        echo '文件:' . $_error['file'] . '</br>';
        echo '在第' . $_error['line'] . '行</br>';
    }
}
register_shutdown_function("cache_shutdown_error");
use Tool\ArrayUtil;
class ControllerUserTransApply  extends Controller {
	private $cur_url = null;
	private $error = null;

	public function __construct($registry) {
		parent::__construct($registry);
		// 当前网址
		$this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

		// 加载log Model
		$this->load->library('sys_model/trans', true);
		$this->load->library('sys_model/deposit', true);
	}

	/**
	 * [index 转账申请列表]
	 * @return   [type]                   [description]
	 * @Author   vincent
	 * @DateTime 2017-08-09T22:34:02+0800
	 */
	public function index() {
		
		$filter = $this->request->get(array('apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time','city_id','time_type','region_id'));

		$condition = array();
		if (!empty($filter['apply_user_name'])) {
			$condition['apply_user_name'] = array('like', "%{$filter['apply_user_name']}%");
		}
		if (!empty($filter['pdr_sn'])) {
			$condition['pdr_sn'] = $filter['pdr_sn'];
		}
		if (!empty($filter['apply_admin_name'])) {
			$condition['apply_admin_name'] = array('like', "%{$filter['apply_admin_name']}%");
		}
		if (!empty($filter['apply_audit_admin_name'])) {
			$condition['apply_audit_admin_name'] = array('like', "%{$filter['apply_audit_admin_name']}%");
		}
		if (is_numeric($filter['apply_state'])) {
			$condition['apply_state'] = (int)$filter['apply_state'];
		}
		if (is_numeric($filter['city_id'])) {
			$condition['user.city_id'] = (int)$filter['city_id'];
		}
		if (is_numeric($filter['region_id'])) {
			$condition['user.region_id'] = (int)$filter['region_id'];
		}
		$apply_add_time = explode(' 至 ', $filter['apply_add_time']);
        if($filter['time_type']==1){
            if (!empty($filter['apply_add_time'])) {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime($apply_add_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($apply_add_time[1].'-12-31'))))
                );
            } else {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['apply_add_time'])) {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime($apply_add_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_add_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['apply_add_time'])) {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime($apply_add_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_add_time[1])))
                );
            }else{
                $condition['apply_add_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['apply_add_time'])) {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime($apply_add_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_add_time[1])))
                );
            }
        }

		$apply_audit_time = explode(' 至 ', $filter['apply_audit_time']);
        if($filter['time_type']==1){
            if (!empty($filter['apply_audit_time'])) {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime($apply_audit_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($apply_audit_time[1].'-12-31'))))
                );
            } else {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['apply_audit_time'])) {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime($apply_audit_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_audit_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['apply_audit_time'])) {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime($apply_audit_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_audit_time[1])))
                );
            }else{
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['apply_audit_time'])) {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime($apply_audit_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_audit_time[1])))
                );
            }
        }

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }

		$order 	= 'apply_add_time DESC';
		$rows 	= $this->config->get('config_limit_admin');
		$offset = ($page - 1) * $rows;
		$limit 	= sprintf('%d, %d', $offset, $rows);
		$join = array(
			'user' => 'user.user_id=trans_apply.apply_user_id',
			'city' => 'city.city_id=user.city_id',
			'region'=>'region.region_id=user.region_id'
		);
		
		$result = $this->sys_model_trans->getTransApplyList($condition, $order, $limit,'',$join);

		$total 	= $this->sys_model_trans->getTotalTransApply($condition,$join);

		$apply_states 			= get_apply_states_deposit();
		$apply_states_colors 	= array(0=>'text-blue', 1=>'text-blue', 2=>'text-green',-1=>'text-red');
		// 是否拥有审核权限
		$show_audit_action_tech = $this->logic_admin->hasPermission('user/trans_apply/audit_tech');
		$show_audit_action_fina = $this->logic_admin->hasPermission('user/trans_apply/audit_fina');
		if (is_array($result) && !empty($result)) {
			foreach ($result as &$item) {
				$item = array(
					'region_name'				=> $item['region_name'],
					'city_name'					=> $item['city_name'],
					'apply_user_name' 			=> $item['apply_user_name'],
					'pdr_sn' 					=> $item['pdr_sn'],
					'apply_admin_name' 			=> $item['apply_admin_name'],
					'apply_amount' 				=> $item['apply_amount'],
					'apply_state' 				=> sprintf('<span class="%s">%s</span>', $apply_states_colors[$item['apply_state']], $apply_states[$item['apply_state']]),
					'apply_reason' 				=> $item['apply_reason'],
					'apply_add_time' 			=> !empty($item['apply_add_time']) ? date('Y-m-d H:i:s', $item['apply_add_time']) : '',
					'apply_audit_admin_name' 	=> $item['apply_audit_admin_name'],
					'apply_audit_result' 		=> $item['apply_audit_result'],
					'apply_audit_time' 			=> !empty($item['apply_audit_time']) ? date('Y-m-d H:i:s', $item['apply_audit_time']) : '',
					'audit_action_tech' 		=> $show_audit_action_tech && $item['apply_state'] == 0 ? $this->url->link('user/trans_apply/audit_tech', http_build_query($filter) . '&page='. $page . '&apply_id='. $item['apply_id']) : '',
					'audit_action_fina' 		=> $show_audit_action_fina && $item['apply_state'] == 1 ? $this->url->link('user/trans_apply/audit_fina', http_build_query($filter) . '&page='. $page . '&apply_id='. $item['apply_id']) : '',
				);
			}
		}

		$filter_types = array(
			'apply_user_name' 			=> '用户名称',
			'pdr_sn' 					=> '订单编号',
			'apply_admin_name' 			=> '申请管理员',
			'apply_audit_admin_name' 	=> '审核管理员',
		);
		$filter_type = $this->request->get('filter_type');
		if (empty($filter_type)) {
			reset($filter_types);
			$filter_type = key($filter_types);
		}

		$data_columns = $this->getDataColumns();
		$this->assign('time_type',get_time_type());
		$this->assign('data_columns', $data_columns);
		$this->assign('apply_states', $apply_states);
		$this->assign('data_rows', $result);
		$this->assign('filter', $filter);
		$this->assign('filter_type', $filter_type);
		$this->assign('filter_types', $filter_types);
		$this->assign('action', $this->cur_url);
		$this->assign('add_action', $this->url->link('user/cashapply&pdc_payment_state=4'));
		// $this->assign('add_action', $this->url->link('user/trans_apply/from'));

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
		$this->assign('filter_regions', $filter_regions);
		
		$this->assign('export_action', $this->url->link('user/trans_apply/export'));
		$this->assign('index_action', $this->url->link('user/trans_apply'));

		$this->response->setOutput($this->load->view('user/trans_apply_list', $this->output));
	}
	public function from(){
		$this->getForm();
	}
	/**
	 * [export 导出充值申请表]
	 * @return   [type]                   [description]
	 * @Author   vincent
	 * @DateTime 2017-08-11T09:06:27+0800
	 */
	public function export() {
		$filter = $this->request->get(array('apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time','city_id','time_type','region_id'));

		$condition = array();
		if (!empty($filter['apply_user_name'])) {
			$condition['apply_user_name'] = array('like', "%{$filter['apply_user_name']}%");
		}
		if (!empty($filter['pdr_sn'])) {
			$condition['pdr_sn'] = $filter['pdr_sn'];
		}
		if (!empty($filter['apply_admin_name'])) {
			$condition['apply_admin_name'] = array('like', "%{$filter['apply_admin_name']}%");
		}
		if (!empty($filter['apply_audit_admin_name'])) {
			$condition['apply_audit_admin_name'] = array('like', "%{$filter['apply_audit_admin_name']}%");
		}
		if (is_numeric($filter['apply_state'])) {
			$condition['apply_state'] = (int)$filter['apply_state'];
		}
		if (is_numeric($filter['city_id'])) {
			$condition['user.city_id'] = (int)$filter['city_id'];
		}
		if (is_numeric($filter['region_id'])) {
			$condition['user.region_id'] = (int)$filter['region_id'];
		}
		$apply_add_time = explode(' 至 ', $filter['apply_add_time']);
        if($filter['time_type']==1){
            if (!empty($filter['apply_add_time'])) {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime($apply_add_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($apply_add_time[1].'-12-31'))))
                );
            } else {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['apply_add_time'])) {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime($apply_add_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_add_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['apply_add_time'])) {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime($apply_add_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_add_time[1])))
                );
            }else{
                $condition['apply_add_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['apply_add_time'])) {
                $condition['apply_add_time'] = array(
                    array('egt', strtotime($apply_add_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_add_time[1])))
                );
            }
        }

        $apply_audit_time = explode(' 至 ', $filter['apply_audit_time']);
        if($filter['time_type']==1){
            if (!empty($filter['apply_audit_time'])) {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime($apply_audit_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($apply_audit_time[1].'-12-31'))))
                );
            } else {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['apply_audit_time'])) {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime($apply_audit_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_audit_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['apply_audit_time'])) {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime($apply_audit_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_audit_time[1])))
                );
            }else{
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['apply_audit_time'])) {
                $condition['apply_audit_time'] = array(
                    array('egt', strtotime($apply_audit_time[0])),
                    array('elt', bcadd(86399, strtotime($apply_audit_time[1])))
                );
            }
        }

		$order = 'apply_id DESC';
		$limit='';
		$join = array(
			'user' => 'user.user_id=trans_apply.apply_user_id',
			'city' => 'city.city_id=user.city_id',
			'region'=>'region.region_id=user.region_id'
		);
		$result = $this->sys_model_trans->getTransApplyList($condition, $order,$limit,'',$join);

		$apply_states = get_apply_states();
		$list = array();
		if (is_array($result) && !empty($result)) {
			foreach ($result as $v) {
				$list[] = array(
					'region_name'		=> $v['region_name'],
					'city_name'			=> $v['city_name'],
					'apply_user_name' 	=> $v['apply_user_name'],
					'pdr_sn' 			=> $v['pdr_sn'],
					'apply_admin_name' 	=> $v['apply_admin_name'],
					'apply_amount' 		=> $v['apply_amount'],
					'apply_state' 		=> $apply_states[$v['apply_state']],
					'apply_reason' 		=> $v['apply_reason'],
					'apply_add_time' 	=> !empty($v['apply_add_time']) ? date('Y-m-d H:i:s', $v['apply_add_time']) : '',
					'apply_audit_admin_name' => $v['apply_audit_admin_name'],
					'apply_audit_result'=> $v['apply_audit_result'],
					'apply_audit_time' 	=> !empty($v['apply_audit_time']) ? date('Y-m-d H:i:s', $v['apply_audit_time']) : '',
				);
			}
		}

		$data = array(
			'title' => '退款申请',
			'header' => array(
				'region_name'		=> '区域',
				'city_name'		=> '城市',
				'apply_user_name' 	=> '用户名称',
				'pdr_sn' 			=> '订单编号',
				'apply_admin_name' 	=> '申请管理员',
				'apply_amount' 		=> '申请金额',
				'apply_state' 		=> '申请状态',
				'apply_reason' 		=> '申请理由',
				'apply_add_time' 	=> '申请时间',
				'apply_audit_admin_name' => '审核管理员',
				'apply_audit_result'=> '审核结果',
				'apply_audit_time' 	=> '审核时间'
			),
			'list' => $list
		);
		$this->load->controller('common/base/exportExcel', $data);
	}

	/**
	 * [validateForm 验证表单]
	 * @return   [type]                   [description]
	 * @Author   vincent
	 * @DateTime 2017-08-11T09:09:20+0800
	 */
	private function validateForm() {
		$input = $this->request->post(array('apply_state'));
		foreach ($input as $k => $v) {
			if (empty($v)) {
				$this->error[$k] = '请完善此项';
			}
		}
		// 不通过时必须填写驳回理由
		$apply_audit_result = $this->request->post('apply_audit_result');
		if ($input['apply_state'] == 2 && empty($apply_audit_result)) {
			 $this->error['apply_audit_result'] = '请填写不通过的原因';
		}
		if ($this->error) {
			$this->error['warning'] = '警告：存在错误，请检查！';
		}
		return !$this->error;
	}

	/**
	 * [getForm 显示表单]
	 * @return   [type]                   [description]
	 * @Author   vincent
	 * @DateTime 2017-08-10T17:42:07+0800
	 */
	private function getForm() {
		// 申请提现金额
		$data = $this->request->post(array('apply_state', 'apply_audit_result'));
		// 充值订单id
		$apply_id = $this->request->get['apply_id'];

		// 提现申请信息
		 $condition = array(
			 'apply_id' => $apply_id
		 );
		$cash_apply_info = $this->sys_model_trans->getTransApplyInfo($condition);

		// 充值记录
		$condition = array(
			'pdr_sn' =>  $cash_apply_info['pdr_sn'],
		);
		$fields = 'dr.*,u.mobile,u.available_deposit';
		$recharge_info = $this->sys_model_deposit->getRechargeInfo($condition, $fields);

		// 支付途径
		$payment_types = get_payment_type();
		$recharge_info['pdr_payment_type'] = $payment_types[$recharge_info['pdr_payment_type']];
		// 充值订单状态
		$payment_states = get_payment_state();
		$recharge_info['pdr_payment_state'] = $payment_states[$recharge_info['pdr_payment_state']];
		// 充值时间
		$recharge_info['pdr_payment_time'] = !empty($recharge_info['pdr_payment_time']) ? date('Y-m-d H:i:s', $recharge_info['pdr_payment_time']) : '';

		$has_cash_amount = 0;
		// 退款记录
		$condition = array(
			'pdr_sn' =>  $cash_apply_info['pdr_sn'],
		);
		$cash_logs = $this->sys_model_deposit->getDepositCashList($condition);
		if (is_array($cash_logs) && !empty($cash_logs)) {
			foreach ($cash_logs as $cash_log) {
				if ($cash_log['pdc_payment_state'] == 1) {
					$has_cash_amount += $cash_log['pdc_amount'];
				}
			}
		}

		$filter = $this->request->get(array('apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'page', 'apply_id'));
		$this->assign('data', $data);
		$this->assign('cash_apply_info', $cash_apply_info);
		$this->assign('recharge_info', $recharge_info);
		$this->assign('cash_logs', $cash_logs);
		$this->assign('has_cash_amount', $has_cash_amount);
		$this->assign('return_action', $this->url->link('user/trans_apply'). '&' . http_build_query($filter));
		$this->assign('action', $this->cur_url . '&' . http_build_query($filter));
		$this->assign('error', $this->error);

		$this->response->setOutput($this->load->view('user/trans_form', $this->output));
	}

	/**
	 * [getDataColumns 表格字段]
	 * @return   [type]                   [description]
	 * @Author   vincent
	 * @DateTime 2017-08-11T09:18:24+0800
	 */
	protected function getDataColumns() {
		$this->setDataColumn('区域');
		$this->setDataColumn('城市');
		$this->setDataColumn('用户名称');
		$this->setDataColumn('订单编号');
		$this->setDataColumn('申请管理员');
		$this->setDataColumn('申请金额');
		$this->setDataColumn('申请状态');
		$this->setDataColumn('申请理由');
		$this->setDataColumn('申请时间');
		$this->setDataColumn('审核管理员');
		$this->setDataColumn('审核结果');
		$this->setDataColumn('审核时间');
		return $this->data_columns;
	}

	/**
	 * [audit_tech 转转审核-技术]
	 * @return   [type]                   [description]
	 * @Author   vincent
	 * @DateTime 2017-08-10T17:38:52+0800
	 */
	public function audit_tech() {
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
			$apply_id = $this->request->get('apply_id');
			$input = $this->request->post(array('apply_state', 'apply_audit_result'));

			$condition = array(
				'apply_id' => $apply_id
			);
			$pdt_info 	= $this->sys_model_trans->getTransApplyInfo($condition);

			if(empty($pdt_info)){//申请订单未找到
				$this->error['warning'] = '审核失败，申请订单未找到！';
				goto showForm;
			}

			$this->load->library('sys_model/user', true);

			$condition2['user_id'] 		= $pdt_info['apply_user_id'];
			$usr_info 	= $this->sys_model_user->getUserInfo($condition2,'freeze_deposit');


			$condition3['pdr_sn'] 			= $pdt_info['pdr_sn'];
			$pdr_info 	= $this->sys_model_deposit->getOneRecharge($condition3);

			$this->db->begin();
			$res1 = $res2 = true;

			// 审核通过
			if ($input['apply_state'] == 1) {
				if(empty($usr_info)){//用户不存在
					$this->db->rollback();
					$this->error['warning'] = '审核失败，用户不存在！';
					goto showForm;
				}
				if($usr_info['freeze_deposit']<$pdt_info['apply_amount']){//申请金额大于冻结金额
					$this->db->rollback();
					$this->error['warning'] = '审核失败，申请金额:'.$pdt_info['apply_amount'].'>冻结金额:'.$usr_info['freeze_deposit'];
					goto showForm;
				}

				if(empty($pdr_info)){//充值订单号不存在
					$this->db->rollback();
					$this->error['warning'] = '审核失败，充值订单号不存在！';
					goto showForm;
				}
				// 已退金额
				$where = array(
					'pdr_sn' 			=> $pdt_info['pdr_sn'],
					'pdc_payment_state' => 1,
				);
				$fields = 'sum(`pdc_amount`) as total';
				$cash_total = $this->sys_model_deposit->getDepositCashInfo($where);
				$has_cash_amount = !empty($cash_total) && isset($cash_total['total']) ? $cash_total['total'] : 0;
				// 充值订单剩余可退金额
				$allow_cash_amount = $pdr_info['pdr_amount'] - $has_cash_amount;
				if($pdt_info['apply_amount'] > $allow_cash_amount){
					$this->db->rollback();
					$this->error['warning'] = '审核失败，申请金额:'.$pdt_info['apply_amount'].'>用户可退金额:'.$allow_cash_amount;
					goto showForm;
				}
				$apply_state    = 1;
			}else{
				$apply_state    = -1;
			}

			//更新退款记录
			$condition1['pdc_sn'] 			= $pdt_info['pdc_sn'];
			$pdc_info 	= $this->sys_model_deposit->getDepositCashInfo($condition1);
			if(empty($pdc_info)){//退款订单未找到
				$this->db->rollback();
				$this->error['warning'] = '审核失败，退款订单未找到！';
				goto showForm;
			}
			if($input['apply_state'] != 1){//审核不通过，退款状态4
				$data1['pdc_payment_state'] 	= 4;
				$res1 	= $this->sys_model_deposit->updateDepositCash($condition1, $data1);
			}

			// 更新押金退款申请记录
			$now = time();
			$condition2 = array(
				'apply_id'      => $apply_id,
				'apply_state'   => 0,//只能审核状态为0的申请
			);
			$data2 = array(
				'apply_state'				=> $apply_state,
				'apply_audit_result'		=> $input['apply_audit_result'],
				'apply_audit_admin_id' 		=> $this->logic_admin->getId(),
				'apply_audit_admin_name' 	=> $this->logic_admin->getadmin_name(),
				'apply_audit_time' 			=> $now
			);
			$res2 	= $this->sys_model_trans->updateTransApply($condition2, $data2);

			if($res1 && $res2){//数据更新成功
				$this->db->commit();
				$filter = $this->request->get(array('apply_payment_type' ,'apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'page'));
				$this->load->controller('common/base/redirect', html_entity_decode($this->url->link('user/trans_apply', $filter, true)));
			}else{//失败
				$this->db->rollback();
				$this->error['warning'] = '审核失败!更新提现记录 : '.$res1
											.'; 更新转账申请记录：' . $res2;
				goto showForm;
			}

		}

		showForm:
		$this->assign('title', '转账审核');
		$this->getForm();
	}

	/**
	 * [audit_deposit 押金退款审核-财务]
	 * @return   [type]                   [description]
	 * @Author   vincent
	 * @DateTime 2017-07-26T16:23:25+0800
	 */
	public function audit_fina() {
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
			$apply_id = $this->request->get('apply_id');
			$input = $this->request->post(array('apply_state', 'apply_audit_result'));

			$condition = array(
				'apply_id' => $apply_id
			);
			$pdt_info 	= $this->sys_model_trans->getTransApplyInfo($condition);

			if(empty($pdt_info)){//申请订单未找到
				$this->error['warning'] = '审核失败，申请订单未找到！';
				goto showForm;
			}

			$this->load->library('sys_model/user', true);

			$condition2['user_id'] 		= $pdt_info['apply_user_id'];
			$usr_info 	= $this->sys_model_user->getUserInfo($condition2);


			$condition3['pdr_sn'] 			= $pdt_info['pdr_sn'];
			$pdr_info 	= $this->sys_model_deposit->getOneRecharge($condition3);

			$this->db->begin();
			$res1 = $res2 = $res3 = $res4 = true;

			// 审核通过
			if ($input['apply_state'] == 1) {

				//更新user表冻结押金值
				if(empty($usr_info)){//用户不存在
					$this->db->rollback();
					$this->error['warning'] = '审核失败，用户不存在！';
					goto showForm;
				}
				if($usr_info['freeze_deposit']<$pdt_info['apply_amount']){//申请金额大于冻结金额
					$this->db->rollback();
					$this->error['warning'] = '审核失败，申请金额:'.$pdt_info['apply_amount'].'>冻结金额:'.$usr_info['freeze_deposit'];
					goto showForm;
				}
				$data2['freeze_deposit'] 	= $usr_info['freeze_deposit'] - $pdt_info['apply_amount'];
				$res2 	= $this->sys_model_user->updateUser($condition2, $data2);

				//更新充值表记录状态
				if(empty($pdr_info)){//充值订单号不存在
					$this->db->rollback();
					$this->error['warning'] = '审核失败，充值订单号不存在！';
					goto showForm;
				}
				// 已退金额
				$where = array(
					'pdr_sn' 			=> $pdt_info['pdr_sn'],
					'pdc_payment_state' => 1,
				);
				$fields = 'sum(`pdc_amount`) as total';
				$cash_total = $this->sys_model_deposit->getDepositCashInfo($where);
				$has_cash_amount = !empty($cash_total) && isset($cash_total['total']) ? $cash_total['total'] : 0;
				// 充值订单剩余可退金额
				$allow_cash_amount = $pdr_info['pdr_amount'] - $has_cash_amount;
				if($pdt_info['apply_amount'] > $allow_cash_amount){
					$this->db->rollback();
					$this->error['warning'] = '审核失败，申请金额:'.$pdt_info['apply_amount'].'>用户可退金额:'.$allow_cash_amount;
					goto showForm;
				}
				if($pdt_info['apply_amount'] < $allow_cash_amount){//部分退款
					if($pdr_info['pdr_payment_state'] != -2){
						$data3['pdr_payment_state'] 	= -2;
					}
				}else{//全部退款
					$data3['pdr_payment_state'] 	= -1;
				}
				if(!empty($data3)){
					$res3 	= $this->sys_model_deposit->updateRecharge($condition3,$data3);
				}

				//var_dump($this->db->getLastSql());
				$apply_state    = 2;
			}else{
				$apply_state    = -1;
			}

			//更新退款记录
			$condition1['pdc_sn'] 			= $pdt_info['pdc_sn'];
			$pdc_info 	= $this->sys_model_deposit->getDepositCashInfo($condition1);
			if(empty($pdc_info)){//退款订单未找到
				$this->db->rollback();
				$this->error['warning'] = '审核失败，退款订单未找到！';
				goto showForm;
			}
			if($input['apply_state'] == 1){//审核通过，退款状态1
				$data1['pdc_payment_state'] 	= 1;
			}else{//审核未通过，押金退款状态0
				$data1['pdc_payment_state'] 	= 4;
			}
			$res1 	= $this->sys_model_deposit->updateDepositCash($condition1, $data1);//var_dump($this->db->getLastSql());

			// 更新押金退款申请记录
			$now = time();
			$condition4 = array(
				'apply_id'      => $apply_id,
				'apply_state'   => 1,//只能审核状态为1的申请
			);
			$data4 = array(
				'apply_state'				=> $apply_state,
				'apply_audit_result'		=> $input['apply_audit_result'],
				'apply_audit_admin_id' 		=> $this->logic_admin->getId(),
				'apply_audit_admin_name' 	=> $this->logic_admin->getadmin_name(),
				'apply_audit_time' 			=> $now
			);
			$res4 	= $this->sys_model_trans->updateTransApply($condition4, $data4);

			if($res1 && $res2 && $res3 && $res4){//数据更新成功
				$res6 = $res7 = true;
				if($input['apply_state'] == 1){//审核通过，转账操作
					$out_trade_no 	= $pdt_info['pdr_sn'];
					$trade_no 		= $pdr_info['trace_no'];
					$rs 			= $this->queryOrder($out_trade_no,$trade_no,array('type'=>'alipay'));//查询充值信息
					if(!$rs['state']){
						$this->db->rollback();
						$this->error['warning'] 	= $rs['msg'];
						goto showForm;

					}

					$account 	= $rs['data']['account'];

					//添加到转账记录表
					$trans_data 	= array(
						'user_id' 		=> $usr_info['user_id'],
						'user_name' 	=> $usr_info['mobile'],
						'pdt_sn' 		=> $pdt_info['pdt_sn'],
						'trace_no' 		=> '',
						'payment_code'	=> 'alipay',
						'account'		=> $account,
						'account_type'	=> '',
						'real_name'		=> $usr_info['real_name'],
						'amount' 		=> $pdt_info['apply_amount'],
						'payment_state' => 0,
						'add_time' 		=> time(),
						'payment_time' 	=> 0,
						);
					/**
					 * add Vincent:2017-08-11 增加try catch，避免插入重复pdt_sn 导致的错误
					 */
					try{
						$res5 	= $this->sys_model_trans->addTrans($trans_data);
					}catch(\Exception $e){
						$this->db->rollback();
						$this->error['warning'] 	= '添加退款记录信息失败：'.$e->getMessage();
						goto showForm;
					}

					if(!$res5){
						$this->db->rollback();
						$this->error['warning'] 	= '添加转账记录失败！';
						goto showForm;
					}
					switch ($pdr_info['pdr_payment_code']) {
						case 'alipay':
							$account_type 	= 'ALIPAY_USERID';//ALIPAY_USERID,ALIPAY_LOGONID
							$transData 	= array(
								'out_biz_no' 		=> $pdt_info['pdt_sn'],//商户订单号
								'payee_type' 		=> $account_type,//ALIPAY_USERID,ALIPAY_LOGONID
								'payee_account' 	=> $account,//
								'amount' 			=> $pdt_info['apply_amount'],//
								'payer_show_name' 	=> '广东亦强科技有限公司',//
								'payee_real_name' 	=> $usr_info['real_name'],
								'remark' 			=> '小强单车充值退款',//
								);
							$res 	= $this->trans($transData,array('type'=>'alipay'));//发起转账
							if(!$res['state']){
								$this->db->rollback();
								$this->error['warning'] 	= $res['msg'];
								goto showForm;
							}
							$trace_no 		= $res['data']['trace_no'];
							$payment_time 	= $res['data']['payment_time'];
							break;
						default:
							$this->db->rollback();
							$this->error['warning'] = '仅支持支付宝转账退款！';
							goto showForm;
							break;
					}
					$this->db->commit();
					$res6 	= $this->sys_model_deposit->updateDepositCash(array('pdc_sn'=>$pdt_info['pdc_sn']),array('pdc_payment_time'=>$payment_time));
					$update_trans 	= array(
						'trace_no' 		=> $trace_no,
						'account_type'	=> $account_type,
						'payment_state' => 1,
						'payment_time' 	=> $payment_time,
						);
					$res7 	= $this->sys_model_trans->updateTrans(array('pdt_id'=>$res5),$update_trans);
				}else{
					$this->db->commit();
				}

				if($res6 && $res7){
					$filter = $this->request->get(array('apply_payment_type' ,'apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'page'));
					$this->load->controller('common/base/redirect', html_entity_decode($this->url->link('user/trans_apply', $filter, true)));
				}else{
					$this->error['warning'] = '转账成功，更新付款时间：'.$res6.';更新转账记录：'.$res7;
					goto showForm;
				}
			}else{//失败
				$this->db->rollback();
				$this->error['warning'] = '审核失败!更新提现记录 : '.$res1
											.'; 更新用户冻结金额：' . $res2
											.'; 更新充值记录状态：' . $res3
											.'; 更新转账申请记录：' . $res4;
				goto showForm;
			}

		}

		showForm:
		$this->assign('title', '转账审核');
		$this->getForm();
	}

	/**
	 * [queryOrder 查询支付订单信息]
	 * @param    [type]                   $out_trade_no [description]
	 * @param    [type]                   $trace_no     [description]
	 * @param    [type]                   $options      [description]
	 * @return   [type]                                 [description]
	 * @Author   vincent
	 * @DateTime 2017-08-10T19:52:41+0800
	 */
	private function queryOrder($out_trade_no,$trace_no,$options){
		$type 	= isset($options['type'])?isset($options['type']):'';
		$data 	= array();
		switch ($type) {
			case 'alipay':
				$res 	= $this->sys_model_trans->AlipayTradeQueryRequest($out_trade_no,$trace_no);
				if($res['state']){
					$data 	= array(
						'account' 	=> $res['data']['buyer_user_id'],
					);
					$res 	= callback(true,'success',$data);
				}
				break;
			default:
				$res 	= callback(false,'仅支持支付宝转账退款！');
				break;
		}
		return $res;
	}

	/**
	 * [trans 转账操作]
	 * @param    [type]                   $apply_id [description]
	 * @return   [type]                             [description]
	 * @Author   vincent
	 * @DateTime 2017-08-10T18:40:10+0800
	 */
	private function trans($data,$options) {
		$type 	= isset($options['type'])?isset($options['type']):'';
		switch ($type) {
			case 'alipay':
				$res 	= $this->sys_model_trans->AlipayFundTransToaccountTransferRequest($data,$options);
				break;
			default:
				$res 	= callback(false,'仅支持支付宝转账退款！');
				break;
		}
		return $res;
	}
}
