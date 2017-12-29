<?php

/**
 * Class 坐标转换
 */
namespace Tool;

class LatLng {
    public $lng = 0;
    public $lat = 0;

    public function __construct($lng, $lat) {
        $this->lng = $lng;
        $this->lat = $lat;
    }
}

define('X_PI', 3.14159265358979324 * 3000.0 / 180.0);

class Coordinate {
    private static $pi = 3.14159265358979324;  // 圆周率
    private static $a = 6378245.0; // WGS 长轴半径
    private static $ee = 0.00669342162296594323; // WGS 偏心率的平方
    /**
     * 将火星坐标系GCJ-02 坐标 转换成百度坐标系 BD-09 坐标
     * @param $gc_loc LatLng 火星坐标点(Class Coordinate)
     * @return $bg_loc LatLng 对象，百度地图经纬度坐标
     */
    public static function gcj_bd($gc_loc)
    {
        $x_pi = X_PI;
        $x = $gc_loc->lng;
        $y = $gc_loc->lat;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
        $bd_x = $z * cos($theta) + 0.0065;
        $bd_y = $z * sin($theta) + 0.006;
        $bg_loc = new LatLng($bd_x,$bd_y);
        return $bg_loc;
    }
    /**
     * 将百度坐标系 BD-09 坐标 转换成 火星坐标系GCJ-02 坐标
     * @param $bd_loc LatLng 火星坐标点(Class Coordinate)
     *  @return $bg_loc Coordinate对象，火星坐标系经纬度坐标
     */
    public static function bd_gcj($bd_loc)
    {
        $x_pi = X_PI;
        $x = $bd_loc->lng - 0.0065;
        $y = $bd_loc->lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $gc_x = $z * cos($theta);
        $gc_y = $z * sin($theta);
        $gc_loc = new LatLng($gc_x,$gc_y);
        return $gc_loc;
    }
    /**
     * 将国际通用坐标系WGS84坐标 转换成 火星坐标系GCJ-02 坐标
     * @param $wgs_loc LatLng WGS84坐标点(Class Coordinate)
     *  @return $bg_loc Coordinate对象，火星坐标系经纬度坐标
     */
    public static function wgs_gcj($wgs_loc)
    {
        $wgs_lon = $wgs_loc->lng;
        $wgs_lat = $wgs_loc->lat;

        if (self::outOfChina($wgs_lon,$wgs_lat)){
            return (new LatLng($wgs_lon,$wgs_lat));
        }
        $x_pi = X_PI;
        $dLat = self::transformLat($wgs_lon - 105.0, $wgs_lat - 35.0);
        $dLon = self::transformLon($wgs_lon - 105.0, $wgs_lat - 35.0);
        $radLat = $wgs_lat / 180.0 * $x_pi;
        $magic = sin($radLat);
        $magic = 1 - self::$ee * $magic * $magic;
        $sqrtMagic = sqrt($magic);
        $dLat = ($dLat * 180.0) / ((self::$a * (1 - self::$ee)) / ($magic * $sqrtMagic) * self::$pi);
        $dLon = ($dLon * 180.0) / (self::$a / $sqrtMagic * cos($radLat) * self::$pi);

        $mgLat = $wgs_lat + $dLat;
        $mgLon = $wgs_lon + $dLon;

        $gcj_loc = new LatLng($mgLon,$mgLat);
        return $gcj_loc;
    }

    /**
     * 将火星坐标系GCJ-02坐标 转换成 国际通用坐标系WGS84坐标
     * @param $gcj_loc LatLng GCJ-02坐标点(Class Coordinate)
     *  @return $wgs_loc LatLng 对象，国际通用坐标系WGS84坐标
     */
    public static function gcj_wgs($gcj_loc)
    {
        $to = self::wgs_gcj($gcj_loc);
        $lon = $gcj_loc->lng;
        $lat = $gcj_loc->lat;
        $gcl_lon = $to->lng;
        $gcl_lat = $to->lat;
        $d_lat = $gcl_lat - $lat;
        $d_lon = $gcl_lon - $lon;
        return new LatLng($lon - $d_lon,$lat - $d_lat);
    }

    private static function outOfChina($lon,$lat)
    {
        if ($lon < 72.004 || $lon > 137.8347)
            return true;
        if ($lat < 0.8293 || $lat > 55.8271)
            return true;

        return false;
    }

    private static function transformLat($x,$y)
    {
        $ret = -100.0 + 2.0 * $x + 3.0 * $y + 0.2 * $y * $y + 0.1 * $x * $y + 0.2 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * self::$pi) + 20.0 * sin(2.0 * $x * self::$pi)) * 2.0 / 3.0;
        $ret += (20.0 * sin($y * self::$pi) + 40.0 * sin($y / 3.0 * self::$pi)) * 2.0 / 3.0;
        $ret += (160.0 * sin($y / 12.0 * self::$pi) + 320 * sin($y * self::$pi / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

    private static function transformLon($x, $y)
    {
        $ret = 300.0 + $x + 2.0 * $y + 0.1 * $x * $x + 0.1 * $x * $y + 0.1 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * self::$pi) + 20.0 * sin(2.0 * $x * self::$pi)) * 2.0 / 3.0;
        $ret += (20.0 * sin($x * self::$pi) + 40.0 * sin($x / 3.0 * self::$pi)) * 2.0 / 3.0;
        $ret += (150.0 * sin($x / 12.0 * self::$pi) + 300.0 * sin($x / 30.0 * self::$pi)) * 2.0 / 3.0;

        return $ret;
    }
}