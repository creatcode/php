<?php
/**
 * Created by PhpStorm.
 * User: ljw
 * Date: 2017/6/20 0020
 * Time: 11:13
 */
class ControllerOperationOperationreport extends Controller
{
    private $cur_url = '';
    private $error   =   '';
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->load->library('sys_model/repair',true);
        $this->load->library('sys_model/admin',true);
        $this->load->library('sys_model/cooperator',true);

    }

    public function index(){

        $post   = $this->request->post(array('cooperator_id','search_time','mobile'));
        $get    = $this->request->get(array('page'));
        $post['cooperator_id'] = $this->logic_admin->getParam('cooperator_id');
        $w      = '1=1 ' ;
        if (!empty($post['search_time'])) {
            $add_time = explode(' 至 ', $post['search_time']);
            $w .= ' AND add_time > '.strtotime($add_time[0]).' AND add_time < '.bcadd(86399, strtotime($add_time[1]));
        }

        if (is_numeric($post['cooperator_id']) && $post['mobile']){
            #合伙人帅选
            $where = array('cooperator_id' => $post['cooperator_id']);
            $admin_list = $this->sys_model_admin->getAdminList($where,'admin_id DESC','','admin_id');
            $str_id = '999999';
            foreach($admin_list as $v){
                $str_id .= ','.$v['admin_id'];
            }
            #电话赛选；
            $where1 = array('mobile' => $post['mobile']);
            $admin_list2 = $this->sys_model_admin->getAdminList($where1,'admin_id DESC','','admin_id');
            $str2_id = '999999';
            foreach($admin_list2 as $v){
                $str2_id .= ','.$v['admin_id'];
            }
            #合并
            $arr_id = explode(',',$str_id);
            $arr1_id = explode(',',$str2_id);
            $new_arr = array_intersect($arr_id,$arr1_id);
            $new_str = implode(',',$new_arr);
            $w.= ' AND admin_id in ('.$new_str.')';
            $cooperator_id = $post['cooperator_id'];

        }else if(is_numeric($post['cooperator_id'])){
            #只有合伙人
            $where = array('cooperator_id' => $post['cooperator_id']);
            $admin_list = $this->sys_model_admin->getAdminList($where,'admin_id DESC','','admin_id');
            if(!empty($admin_list)){
                $str_id = '999999';
                foreach($admin_list as $v){
                    $str_id .= ','.$v['admin_id'];
                }
                $w.= ' AND admin_id in ('.$str_id.')';
            }
            $cooperator_id = $post['cooperator_id'];

        }else if($post['mobile']){
            #只有电话
            $where1 = array('mobile' => $post['mobile']);
            $admin_list2 = $this->sys_model_admin->getAdminList($where1,'admin_id DESC','','admin_id');
            $str_id = '999999';
            foreach($admin_list2 as $v){
                    $str_id .= ','.$v['admin_id'];
                }
            $w.= ' AND admin_id in ('.$str_id.')';
            $cooperator_id = '';
        }else{
            $cooperator_id = '';
        }

        $limit  = '0,10';
        $rows   = $this->config->get('config_limit_admin');
        if($get['page']){
            $page   = $get['page'];
            $offset = ($page - 1) * $rows;
            $limit  = sprintf('%d, %d', $offset, $rows);
        }else{
            $page = 1;
        }

        $view_result = $this->sys_model_repair->getSumRepairsByAdminId($w,$limit);
        unset($v);
        foreach($view_result as &$v){
            $where = array('admin_id' => $v['admin_id']);
            $admin_info = $this->sys_model_admin->getAdminInfo($where);
            $v['nickname'] = !$admin_info['nickname'] ? '平台' : $admin_info['nickname'];
            $v['admin_name'] = !$admin_info['admin_name'] ? '平台' : $admin_info['admin_name'];
            $v['mobile'] = !$admin_info['mobile'] ? '平台' : $admin_info['mobile'];
        }
        $total = $this->sys_model_repair->getSumRepairsByAdminIdTotal($w);
        $total = empty($total) ?  $total = 0 : $total['total'];
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}';
        $pagination = $pagination->render();

        $cooper_list = $this->sys_model_cooperator->getCooperatorList();
        $clist = array('0' => '平台');
        unset($v);
        foreach($cooper_list as $v){
            $clist[$v['cooperator_id']] = $v['cooperator_name'];
        }
        $data_columns = $this->getDataColumns();

        $this->assign('cooper_list',$clist);
        $this->assign('data_columns',$data_columns);
        $this->assign('total',$total);
        $this->assign('list',$view_result);
        $this->assign('cooperator_id',$cooperator_id);
        $this->assign('action',$this->url->link('operation/operationreport'));
        $this->assign('post_data',$post);
        $this->assign('pagination',$pagination);

        $this->response->setOutput($this->load->view('operation/operationreport_list', $this->output));

    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns()
    {
        $this->setDataColumn('昵称');
        $this->setDataColumn('账号');
        $this->setDataColumn('电话');
        $this->setDataColumn('总数');
        return $this->data_columns;
    }

}