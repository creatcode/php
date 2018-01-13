<?php

//error_reporting(0);
class ControllerOperatorComment extends Controller
{

    private $sys_model_comment;

    /**
     * ControllerOperatorComment constructor.
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
//        $this->load->library('sys_model/rbac',true);
        $this->sys_model_comment = new Sys_Model\Comment($registry);
    }

    /**
     * 获取评论标签列表
     * @api_param user_id
     * @api_param sign
     */
    public function getCommentTags()
    {
        $result = $this->sys_model_comment->getCommentTagList(array('is_show' => 1), 'comment_tag_id,comment_tag_name', 'display_order asc');
        $result ? $this->response->showSuccessResult($result, $this->language->get('success_get')):$this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
    }

    /**
     * 添加评论
     * @api_param user_id
     * @api_param sign
     * @api_param order_sn
     * @api_param star_num        评分数
     * @api_param [comment_tag]   评论标签 格式:1,2,3
     * @api_param [comment]       评论内容
     */
    public function addComment()
    {
        if (!isset($this->request->post['order_sn']) || empty($this->request->post['order_sn'])) {
            $this->response->showErrorResult($this->language->get('error_comment_order_sn'), 142);
        }
        if (!isset($this->request->post['star_num']) || empty($this->request->post['star_num'])) {
            $this->response->showErrorResult($this->language->get('error_comment_star_num'), 143);
        }
        if ($this->sys_model_comment->getCommentInfo(array('order_sn' => $this->request->post['order_sn']))) {
            $this->response->showErrorResult($this->language->get('can_not_comment_again'), 144);
        }

        $user_info = $this->startup_user->getUserInfo();
        $data['user_id'] = $this->startup_user->userId();
        $data['user_name'] = $user_info['mobile'];
        $data['comment'] = isset($this->request->post['comment']) ? $this->request->post['comment'] : '';
        $data['order_sn'] = $this->request->post['order_sn'];
        $data['star_num'] = $this->request->post['star_num'];
        $data['comment_tag'] = isset($this->request->post['comment_tag']) ? $this->request->post['comment_tag'] : '';
        $data['add_time'] = time();
        $data['comment_img'] = isset($this->request->post['comment_img']) ? $this->request->post['comment_img'] : '';

        $comment_id = $this->sys_model_comment->addComment($data);

        $comment_id ? $this->response->showSuccessResult('', $this->language->get('success_add')) : $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
    }

    /**
     * 获取用户某一条订单的评论
     * @api_param user_id
     * @api_param sign
     * @api_param order_sn
     */
    public function getComment()
    {
        if (!isset($this->request->post['order_sn']) || empty($this->request->post['order_sn'])) {
            $this->response->showErrorResult($this->language->get('error_comment_order_sn'), 142);
        }
        $condition['order_sn'] = $this->request->post['order_sn'];
        $field = 'comment,star_num,comment_tag';
        $result = $this->sys_model_comment->getCommentInfo($condition,$field);
        $tagList = $this->sys_model_comment->getCommentTagList(['comment_tag_id'=>['in',$result['comment_tag']]],'comment_tag_id,comment_tag_name');
        $result['tag_list'] = $tagList;
        $result ? $this->response->showSuccessResult($result, $this->language->get('success_get')) : $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
    }

}