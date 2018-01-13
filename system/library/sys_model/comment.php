<?php

namespace Sys_Model;

class Comment
{

    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }


    public function addComment($data)
    {
        return $this->db->table('comment')->insert($data);
    }


    public function updateComment($where, $data)
    {
        return $this->db->table('comment')->where($where)->update($data);
    }


    public function deleteComment($where)
    {
        return $this->db->table('comment')->where($where)->delete();
    }


    public function getCommentList($where = array(), $order = '', $limit = '',$field = '*')
    {
        return $this->db->table('comment')->where($where)->field($field)->order($order)->limit($limit)->select();
    }


    public function getCommentJoinList($where = array(), $order = '', $limit = '', $field = 'comment.*', $join = array(), $group = '')
    {
        $table = 'comment as comment';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->field($field)->where($where)->group($group)->order($order)->limit($limit)->select();
    }

    public function getCommentJoinListTotals($where, $join = array())
    {
        $table = 'comment as comment';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as `%s`', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }

        return $this->db->table($table)->where($where)->limit(1)->count(1);
    }



    public function getCommentInfo($where, $field = '*')
    {
        return $this->db->table('comment')->where($where)->field($field)->limit(1)->find();
    }


    public function getTotalComments($where)
    {
        return $this->db->table('comment')->where($where)->limit(1)->count(1);
    }


    public function addCommentTag($data)
    {
        return $this->db->table('comment_tag')->insert($data);
    }


    public function updateCommentTag($where, $data)
    {
        return $this->db->table('comment_tag')->where($where)->update($data);
    }


    public function deleteCommentTag($where)
    {
        return $this->db->table('comment_tag')->where($where)->delete();
    }


    public function getCommentTagList($where = array(), $field = '*', $order = '', $limit = '')
    {
        return $this->db->table('comment_tag')->where($where)->field($field)->order($order)->limit($limit)->select();
    }


    public function getCommentTagInfo($where)
    {
        return $this->db->table('comment_tag')->where($where)->limit(1)->find();
    }


    public function getTotalCommentTags($where)
    {
        return $this->db->table('comment_tag')->where($where)->limit(1)->count(1);
    }

    public function getDifferenceStarNumTotals($where = 1){
        $sql = 'SELECT count(comment_id) as totals,star_num FROM rich_comment as comment LEFT JOIN rich_user as user ON user.user_id = comment.user_id  WHERE '.$where.' group by star_num';
        return $this->db->getRows($sql);
    }

    public function getDifferenceStarNumTotalsTotals($where = 1){
        $sql = 'SELECT count(comment_id) as totals FROM rich_comment as comment LEFT JOIN rich_user as user ON user.user_id = comment.user_id  WHERE '.$where;
        return $this->db->getRow($sql)['totals'];
    }

    public function getCommentAllTag($where = 1){
        $sql = 'SELECT comment.comment_tag FROM rich_comment as comment LEFT JOIN rich_user as user ON user.user_id = comment.user_id  WHERE '.$where;
        return $this->db->getRows($sql);
    }

    // public function getDepositRecharge($where,$field,$limit,$join,$order){
    //     $table = 'deposit_recharge as deposit_recharge';
    //     if (is_array($join) && !empty($join)) {
    //         $addTables = array_keys($join);
    //         $joinType = '';
    //         if (!empty($addTables) && is_array($addTables)) {
    //             foreach ($addTables as $v) {
    //                 $table .= sprintf(',%s as %s', $v, $v);
    //                 $joinType .= ',left';
    //             }
    //         }
    //         $on = implode(',', $join);
    //         $this->db->join($joinType)->on($on);
    //     }
    //     return $this->db->table($table)->field($field)->join($join)->where($where)->order($order)->limit($limit)->select();

    // }
    public function getDepositRecharge($where,$field = '*',$limit = '',$order = 'pdr_id DESC'){

        if($limit){
            $limit = "limit ".$limit;
        }
        $sql = "SELECT ".$field." FROM rich_deposit_recharge as deposit_recharge LEFT JOIN rich_user as user ON user.user_id = deposit_recharge.pdr_user_id  LEFT JOIN rich_city as city on city.city_id=user.city_id left join rich_region as region on region.region_id=city.region_id WHERE ".$where." order by ". $order ." ".$limit;
        return $this->db->getRows($sql);

    }

    //////////////////////////////////////////
    //充值优惠设置
    public function getPrensentRecharge($where,$field = '*',$limit = '',$order = 'prc_id DESC'){

        if($limit){
            $limit = "limit ".$limit;
        }
        $sql = "SELECT ".$field." FROM rich_present_recharge as present_recharge   LEFT JOIN rich_city as city on city.city_id=present_recharge.present_city_id left join rich_region as region on region.region_id=present_recharge.present_region_id WHERE ".$where." order by ". $order ." ".$limit;
        return $this->db->getRows($sql);

    }

    public function getPrensentRechargeToals($w = '1 = 1'){
        $sql = "SELECT count(present_recharge.prc_id) as totals FROM rich_present_recharge as present_recharge  LEFT join rich_city as city on city.city_id = present_recharge.present_city_id left join rich_region as region on region.region_id=present_recharge.present_region_id WHERE ".$w;
        return $this->db->getRow($sql)['totals'];
        
    }
    ///////////////////////////////////////////////////

/**
 * 注册金统计
 */
    public function getRegin($where,$field = '*',$limit = '',$order = 'pdr_id DESC'){

        if($limit){
            $limit = "limit ".$limit;
        }
        $sql = "SELECT ".$field." FROM rich_reginster as a LEFT JOIN rich_user as user ON user.user_id = a.regin_user_id  LEFT JOIN rich_city as city on city.city_id=user.city_id left join rich_region as region on region.region_id=city.region_id WHERE ".$where." order by ". $order ." ".$limit;
        return $this->db->getRows($sql);

    }

    public function getRegin2($where,$field = '*'){

        
        $sql = "SELECT ".$field." FROM rich_reginster as a LEFT JOIN rich_user as user ON user.user_id = a.regin_user_id  LEFT JOIN rich_city as city on city.city_id=user.city_id left join rich_region as region on region.region_id=city.region_id WHERE ".$where." group by date";
        return $this->db->getRows($sql);

    }

    public function getDepositRecharge2($where,$field = '*'){

        
        $sql = "SELECT ".$field." FROM rich_deposit_recharge as deposit_recharge LEFT JOIN rich_user as user ON user.user_id = deposit_recharge.pdr_user_id  LEFT JOIN rich_city as city on city.city_id=user.city_id left join rich_region as region on region.region_id=city.region_id WHERE ".$where." group by date";
        return $this->db->getRows($sql);

    }

    public function getDepositRechargeToals($w = '1 = 1'){
        $sql = "SELECT count(deposit_recharge.pdr_id) as totals FROM rich_deposit_recharge as deposit_recharge LEFT JOIN rich_user as user ON user.user_id = deposit_recharge.pdr_user_id LEFT join rich_city as city on city.city_id = user.user_id left join rich_region as region on region.region_id=city.region_id WHERE ".$w;
        return $this->db->getRow($sql)['totals'];
        
    }

    //注册金总计
    public function getReginToals($w = '1 = 1'){
        $sql = "SELECT count(a.regin_id) as totals FROM rich_reginster as a LEFT JOIN rich_user as user ON user.user_id = a.regin_user_id LEFT join rich_city as city on city.city_id = user.user_id left join rich_region as region on region.region_id=city.region_id WHERE ".$w;
        return $this->db->getRow($sql)['totals'];
        
    }

    public function getReginLong($where){
        $sql = "SELECT SUM(a.regin_amount) as orderAmountTotal FROM rich_reginster as a LEFT JOIN rich_user as user ON user.user_id = a.regin_user_id LEFT join rich_city as city on city.city_id = user.user_id left join rich_region as region on region.region_id=city.region_id WHERE ".$where;
        return $this->db->getRow($sql);
        
    }

    public function getDepositRechargeLong($where){
        $sql = "SELECT SUM(deposit_recharge.pdr_amount) as orderAmountTotal, SUM(deposit_recharge.pdr_present_amount) as refundAmountTotal FROM rich_deposit_recharge as deposit_recharge LEFT JOIN rich_user as user ON user.user_id = deposit_recharge.pdr_user_id LEFT join rich_city as city on city.city_id = user.user_id left join rich_region as region on region.region_id=city.region_id WHERE ".$where;
        return $this->db->getRow($sql);
        
    }

}
