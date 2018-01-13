<?php

use Enum\ErrorCode;
use Tool\IosPush;
use Enum\PushCode;

class ControllerAccountArticle extends Controller{

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->library('sys_model/article');
    }

    /**
     * 获取指南列表
     */
    public function index(){

        $article_list = $this->sys_model_article->getArticleList($where = array(), '', '','article_id,article_title');
        $this->response->showSuccessResult($article_list, $this->language->get('success'));
    }

    /**
     * 
     */
    public function info(){
        $param = $this->request->post(['article_id']);
        if(empty($param['article_id'])){
            $this->response->showErrorResult($this->language->get("error_missing_parameter"),ErrorCode::ERROR_MISSING_PARAMETER);
        }

        $article = $this->sys_model_article->getArticleInfo(['article_id']);
        $this->response->showSuccessResult($article, $this->language->get('success'));

    }

}