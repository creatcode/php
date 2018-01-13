<?php
namespace Sys_Model;

class Article {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ************************************************ 文章 start ************************************************
    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加文章
     * @param $data
     * @return mixed
     */
    public function addArticle($data) {
        return $this->db->table('article')->insert($data);
    }

    /**
     * 更新文章
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateArticle($where, $data) {
        return $this->db->table('article')->where($where)->update($data);
    }

    /**
     * 删除文章
     * @param $where
     * @return mixed
     */
    public function deleteArticle($where) {
        return $this->db->table('article')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取文章列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getArticleList($where = array(), $order = '', $limit = '',$field = '*') {
        return $this->db->table('article')->field($field)->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取文章信息
     * @param $where
     * @return mixed
     */
    public function getArticleInfo($where) {
        return $this->db->table('article')->where($where)->limit(1)->find();
    }

    /**
     * 统计文章信息
     * @param $where
     * @return mixed
     */
    public function getTotalArticles($where) {
        return $this->db->table('article')->where($where)->limit(1)->count(1);
    }
    // ************************************************ 文章 end ************************************************


    // ************************************************ 文章分类 start ************************************************
    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加文章分类
     * @param $data
     * @return mixed
     */
    public function addArticleCategory($data) {
        return $this->db->table('article_category')->insert($data);
    }

    /**
     * 更新文章分类
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateArticleCategory($where, $data) {
        return $this->db->table('article_category')->where($where)->update($data);
    }

    /**
     * 删除文章分类
     * @param $where
     * @return mixed
     */
    public function deleteArticleCategory($where) {
        return $this->db->table('article_category')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取文章分类列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getArticleCategoryList($where = array(), $order = '', $limit = '') {
        return $this->db->table('article_category')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取文章分类信息
     * @param $where
     * @return mixed
     */
    public function getArticleCategoryInfo($where) {
        return $this->db->table('article_category')->where($where)->limit(1)->find();
    }

    /**
     * 统计文章分类信息
     * @param $where
     * @return mixed
     */
    public function getTotalArticleCategories($where) {
        return $this->db->table('article_category')->where($where)->limit(1)->count(1);
    }
    // ************************************************ 文章分类 end ************************************************


}
