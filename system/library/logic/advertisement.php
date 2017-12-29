<?php

/**
 * 东莞市亦强软件有限公司
 * Author: 罗剑波
 * Time: 2017/4/28 11:03
 */

namespace Logic;

/**
 * 广告
 * Class Advertisement
 * @package Logic
 */
class Advertisement
{
    private $db;

    /**
     * Advertisement constructor.
     * @param $registry
     */
    public function __construct($registry) {
        $this->db = $registry->get('db');
    }

    /**
     * 获取某个定位所在地的广告（包含全国性广告）
     * @param $lat 纬度（高德地图坐标系，可以忽略坐标系之间的微小差距）
     * @param $lng 经度（高德地图坐标系，可以忽略坐标系之间的微小差距）
     * @return array 返回已经通过审批的，当前时间有效的且排好序的广告数组，数组每一项是一个数组，包含image，image1x，image2x，image3x，link字段
     */
    public function getAdvertisementByLocation($lat, $lng, $adv_type) {
        $lat += 0; $lng += 0;
        $now = time();
        $today = date('Ymd');
        $sql = "SELECT distinct(a.adv_id),"
            ."CONCAT('" . HTTP_STATIC . "', a.adv_image,'?t=$today') AS image, "
            ."CONCAT('" . HTTP_STATIC . "', a.adv_image_1x,'?t=$today') AS image1x, "
            ."CONCAT('" . HTTP_STATIC . "', a.adv_image_2x,'?t=$today') AS image2x, "
            ."CONCAT('" . HTTP_STATIC . "', a.adv_image_3x,'?t=$today') AS image3x, "
            ."CONCAT('" . HTTP_STATIC . "', a.adv_image_4x,'?t=$today') AS image4x, "
            ."CONCAT('" . HTTP_STATIC . "', a.adv_image_5x,'?t=$today') AS image5x, "
            ."a.adv_link AS link, "
            ."a.adv_max_version_android AS adv_max_version_android, "
            ."a.adv_max_version_ios AS adv_max_version_ios, "
            ."a.ios_link AS ios_link "
            ."FROM " . DB_PREFIX . "advertisement AS a "
            ."INNER JOIN " . DB_PREFIX . "region AS r "
            ."ON a.adv_region_id=r.region_id OR a.adv_region_id=0 "
            ."WHERE a.adv_approved=1 AND a.adv_start_time<$now AND a.adv_end_time>$now "
            ."AND (a.adv_region_id=0 OR "
            ."($lat>r.region_bounds_southwest_lat "
            ."AND $lat<r.region_bounds_northeast_lat "
            ."AND $lng>r.region_bounds_southwest_lng "
            ."AND $lng<r.region_bounds_northeast_lng)) "
            ."AND adv_type='$adv_type' "
            ."ORDER BY ((a.adv_sort<>0) * a.adv_effect_time + 10000000000 * a.adv_sort) ASC, a.adv_id ASC";
        return $this->db->getRows($sql);
    }
}