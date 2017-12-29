<?php
/**
 * 用户评分列表.
 * User: ljw
 * Date: 2017/7/26 0026
 * Time: 9:19
 */

class ControllerUserUserRating extends Controller{

    private $cur_url;
    private $error;
    private $page_rows;

    public function __construct($registry){
        parent::__construct($registry);
        $this->cur_url = $this->request->get['route'];
        $this->load->library('sys_model/comment', true);
        $this->load->library('sys_model/user', true);
        $this->page_rows = $this->config->get('config_limit_admin');
    }

    public function index(){

        $get_data = $this->request->get(array('mobile','tag_id','add_time','star_num','cooperator_id'));
        $tag_arr  = $this->getCommemtTagArray();
        $this->load->library('sys_model/cooperator', true);
        $cooperators = $this->sys_model_cooperator->getCooperatorList();
        $cooperator_list = array();
        foreach($cooperators as $it){
            $cooperator_list[$it['cooperator_id']] = $it['cooperator_name'];
        }
        $where    = array();
        if($get_data['mobile']){
            $this->load->library("sys_model/user");
            $user_info = $this->sys_model_user->getUserInfo(array('mobile' => $get_data['mobile']), 'user_id');
            $where['comment.user_id'] =  $user_info['user_id'];
            $this->assign('filter_mobile',$get_data['mobile']);
        }
        if (!empty($get_data['add_time'])) {
            $add_time = explode(' 至 ', $get_data['add_time']);
            $where['comment.add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
            $this->assign('filter_add_time',$get_data['add_time']);
        }
        if($get_data['star_num']){
            $where['comment.star_num']  = $get_data['star_num'];
            $this->assign('filter_star_num',$get_data['star_num']);
        }

        if($get_data['cooperator_id']){
            $where['user.cooperator_id'] = $get_data['cooperator_id'];
            $this->assign('cooperator_id',$get_data['cooperator_id']);
        }

        $join = array(
            'user' => 'user.user_id=comment.user_id',
            'orders' => 'orders.order_sn=comment.order_sn'
        );
        $page   = isset($this->request->get['page']) ? $this->request->get['page'] : 1 ;
        $offset = $this->page_rows*($page - 1);
        $limit  = $this->page_rows*($page - 1).",".$this->page_rows;
        $field = "comment.*,user.cooperator_id,orders.bicycle_sn,orders.end_time as use_end_time,orders.add_time as use_start_time";
        // $comment_data = $this->sys_model_comment->getCommentJoinList($where, ' comment.comment_id DESC', $limit, $field, $join);
        // $total  = $this->sys_model_comment->getCommentJoinListTotals($where,$join);
        foreach($comment_data as &$v){
            $v['comment_tag'] ? $arr = strstr($v['comment_tag'], ',') ? explode(',', $v['comment_tag']) : array($v['comment_tag']) : $arr = array();
            $str = false;
            foreach($arr as $item){
                if($str){
                    $str .= ','.(isset($tag_arr[$item]) ? $tag_arr[$item] : '未知');
                }else{
                    $str = isset($tag_arr[$item]) ? $tag_arr[$item] : '未知';
                }
            }
            $v['comment_tag_text'] = $str;
            $v['use_time'] = $v['use_end_time'] >= $v['use_start_time'] ? round(($v['use_end_time'] - $v['use_start_time'])/60) : 0;
            $v['cooperator_name'] = $v['cooperator_id'] ? isset($cooperator_list[$v['cooperator_id']]) ? $cooperator_list[$v['cooperator_id']] : '平台' : '平台';
        }

        $this->set_column(array('用户手机号码','归属合伙人','骑行单车','用户评价','评价星数','评价标签','骑行时间(分钟)','添加时间'));
        $this->assign('data_columns',$this->data_columns);
        $this->assign('action',$this->url->link('user/user_rating'));
        $this->assign('data_rows',$comment_data);
        $this->assign('user_rating_chart_action', $this->url->link('user/user_rating/chart'));
        $this->assign('export_action', $this->url->link('user/user_rating/export'). '&amp;' . str_replace('&', '&amp;', http_build_query($get_data)));

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $this->page_rows;
        $pagination->url = $this->url->link($this->cur_url) . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($get_data));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $this->page_rows)) ? $total : ($offset + $this->page_rows), $total, ceil($total / $this->page_rows));
        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('cooperators', $cooperators);

        $this->response->setOutPut($this->load->view('user/user_rating', $this->output));

    }

    public function getCommemtTagArray($show = true){

        if(!$show){
            $where = array('is_show' => 1);
        }else{
            $where = array();
        }
        $comment_tag_data = $this->sys_model_comment->getCommentTagList($where);
        $return_data = array();
        foreach($comment_tag_data as $v){
            $return_data[$v['comment_tag_id']] = $v['comment_tag_name'];
        }
        return $return_data;
    }

    public function set_column($data){
        foreach($data as $item){
            $this->setDataColumn($item);
        }
    }

    public function chart(){


        $get_data = $this->request->get(array('mobile','tag_id','add_time','star_num','cooperator_id'));

        $this->load->library('sys_model/cooperator', true);
        $cooperators = $this->sys_model_cooperator->getCooperatorList();

        $where = '1 = 1 ';
        if (!empty($get_data['add_time'])) {
            $add_time = explode(' 至 ', $get_data['add_time']);
            $where .= " AND comment.add_time >= ".strtotime($add_time[0])." AND comment.add_time <= ".bcadd(86399, strtotime($add_time[1]));
            $this->assign('filter_add_time',$get_data['add_time']);
        }

        if($get_data['cooperator_id']){
            $where .= " AND user.cooperator_id = ".$get_data['cooperator_id'] . " ";
            $this->assign('cooperator_id',$get_data['cooperator_id']);
        }

        $total  = $this->sys_model_comment->getDifferenceStarNumTotalsTotals($where);
        $star_result = $this->sys_model_comment->getDifferenceStarNumTotals($where);

        $result = [];
        foreach ($star_result as $item) {
            $result[] = [
                'label' => $item['star_num']."星",
                'value' => isset($item['totals']) ? $item['totals'] : 0
            ];
        }
        $this->assign('action',$this->url->link('user/user_rating/chart'));
        $this->assign('data', json_encode($result));
        $this->assign('total', $total);

        $tag_arr     = $this->sys_model_comment->getCommentTagList(array(), 'comment_tag_id,comment_tag_name');
        $tag_new_arr = array();
        $label_total = 0;
        foreach($tag_arr as $value){
            $tag_new_arr[$value['comment_tag_id']] = array(
                'comment_tag_id'    => $value['comment_tag_id'],
                'comment_tag_name'  => $value['comment_tag_name'],
                'totals'            => 0,
            );
        }

        $comment_data = $this->sys_model_comment->getCommentAllTag($where);

        foreach($comment_data as $v){
            $v['comment_tag'] ? $arr = strstr($v['comment_tag'], ',') ? explode(',', $v['comment_tag']) : array($v['comment_tag']) : $arr = array();
            foreach($arr as $v2){
                if(isset($tag_new_arr[$v2])){
                    ++$label_total;
                    $tag_new_arr[$v2]['totals'] = $tag_new_arr[$v2]['totals']+1;
                }
            }
        }
        $result = [];
        foreach ($tag_new_arr as $item2) {
            $result[] = [
                'label' => $item2['comment_tag_name'],
                'value' => isset($item2['totals']) ? $item2['totals'] : 0
            ];
        }
        $this->assign('label_data', json_encode($result));
        $this->assign('label_total', $label_total);
        $this->assign('cooperators', $cooperators);
        $this->assign('user_rating_action',$this->url->link('user/user_rating'));
        $this->response->setOutPut($this->load->view('user/user_rating_chart',$this->output));
    }



    /**
     * 导出
     */
    public function export() {

        $get_data = $this->request->get(array('mobile','tag_id','add_time','star_num','cooperator_id'));
        $tag_arr  = $this->getCommemtTagArray();
        $this->load->library('sys_model/cooperator', true);
        $cooperators = $this->sys_model_cooperator->getCooperatorList();
        $cooperator_list = array();
        foreach($cooperators as $it){
            $cooperator_list[$it['cooperator_id']] = $it['cooperator_name'];
        }
        $where    = array();
        if($get_data['mobile']){
            $this->load->library("sys_model/user");
            $user_info = $this->sys_model_user->getUserInfo(array('mobile' => $get_data['mobile']), 'user_id');
            $where['comment.user_id'] =  $user_info['user_id'];
        }
        if (!empty($get_data['add_time'])) {
            $add_time = explode(' 至 ', $get_data['add_time']);
            $where['comment.add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        if($get_data['star_num']){
            $where['comment.star_num']  = $get_data['star_num'];
        }
        if($get_data['cooperator_id']){
            $where['user.cooperator_id'] = $get_data['cooperator_id'];
        }

        $join = array(
            'user' => 'user.user_id=comment.user_id',
            'orders' => 'orders.order_sn=comment.order_sn'
        );
        $field = "comment.*,user.cooperator_id,orders.bicycle_sn,orders.end_time as use_end_time,orders.add_time as use_start_time";
        $comment_data = $this->sys_model_comment->getCommentJoinList($where, ' comment.comment_id DESC', '', $field, $join);
        $total  = $this->sys_model_comment->getCommentJoinListTotals($where,$join);
        $result = array();
        foreach($comment_data as &$v){
            $v['comment_tag'] ? $arr = strstr($v['comment_tag'], ',') ? explode(',', $v['comment_tag']) : array($v['comment_tag']) : $arr = array();
            $str = false;
            foreach($arr as $item){
                if($str){
                    $str .= ','.(isset($tag_arr[$item]) ? $tag_arr[$item] : '未知');
                }else{
                    $str = isset($tag_arr[$item]) ? $tag_arr[$item] : '未知';
                }
            }
            $result[] = array(
                'mobile'          => $v['mobile'],
                'cooperator_name' => $v['cooperator_id'] ? isset($cooperator_list[$v['cooperator_id']]) ? $cooperator_list[$v['cooperator_id']] : '平台' : '平台',
                'bicycle_sn'         => $v['bicycle_sn'],
                'comment'         => $v['comment'],
                'star_num'        => $v['star_num'],
                'comment_tag'     => $str,
                'use_time'        => $v['use_end_time'] >= $v['use_start_time'] ? round(($v['use_end_time'] - $v['use_start_time'])/60) : 0,
                'add_time'        => date('Y-m-d',$v['add_time']),
            );

        }

        $data = array(
            'title'     => '用户评分列表',
            'list'      => $result,
            'header'    => array(
                'mobile'            => '用户手机号码',
                'cooperator_name'   => '归属合伙人',
                'bicycle_sn'           => '骑行单车',
                'comment'           => '用户评价',
                'star_num'          => '评价星数',
                'comment_tag'       => '评价标签',
                'use_time'          => '骑行时间',
                'add_time'          => '添加时间',
            )
        );
        unset($result);
        $this->load->controller('common/base/exportExcel', $data);
    }





}