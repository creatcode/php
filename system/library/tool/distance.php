<?php
namespace Tool;
class Distance {
    public function getDistance($lng1, $lng2, $lat1, $lat2) {
        $lng1 = $lng1 * 0.01745329252;
        $lng2 = $lng2 * 0.01745329252;
        $lat1 = $lat1 * 0.01745329252;
        $lat2 = $lat2 * 0.01745329252;
        $d = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lng1 - $lng2)) * 6370.6935;
        // 由于系统计算精度，会出现NAN结果
        $d = is_nan($d) ? 0 : $d;
        return $d;
    }

    public function getDistance1($lng1, $lng2, $lat1, $lat2, $unit = 2, $decimal = 2) {
        $EARTH_RADIUS = 6370.996;
        $PI = 3.1415926;

        $radLat1 = $lat1 * $PI / 180.0;
        $radLat2 = $lat2 * $PI / 180.0;

        $radLng1 = $lng1 * $PI / 180.0;
        $radLng2 = $lng2 * $PI / 180.0;

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $distance = $distance * $EARTH_RADIUS * 1000;

        if ($unit == 2) {
            $distance = $distance / 1000;
        }

        return round($distance, $decimal);
    }

    /**
     * 获取范围点，数组
     * @param $lat float
     * @param $lng float
     * @param $distance string 单位是千米
     * @return array
     */
    public function getRange($lat, $lng, $distance,$scale = 15) {
        $range =  bcdiv(bcmul(bcdiv(180,pi(),$scale),$distance,$scale),6370.6935,$scale);
        $lng_range = $range / cos($lat * pi() / 180);
        $max_lat = $lat + $range;
        $min_lat = $lat - $range;
        $max_lng = $lng + $lng_range;
        $min_lng = $lng - $lng_range;

        return array(
            'max_lat' => $max_lat,
            'min_lat' => $min_lat,
            'max_lng' => $max_lng,
            'min_lng' => $min_lng
        );
    }

    public function sumDistance($data) {
        if (!is_array($data)) return 0;
        $count = count($data);
        if ($count <= 1) return 0;
        $distance = 0.00;
        for ($i = 0; $i < $count - 1; $i++) {
            $lng1 = $data[$i]['lng'];
            $lng2 = $data[$i + 1]['lng'];
            $lat1 = $data[$i]['lat'];
            $lat2 = $data[$i + 1]['lat'];
            if ($lat1 && $lng1) {
                $distance += $this->getDistance($lng1, $lng2, $lat1, $lat2);
            }
        }
        return $distance;
    }

    public function getNearestPoint($lat, $lng, $data) {
        if (!is_array($data) || empty($data)) return 0;
        $i = 0;
        $arr = array();
        foreach ($data as $point) {
            $lat1 = $point['lat'];
            $lng1 = $point['lng'];
            $arr[$i] = $this->getDistance($lng, $lng1, $lat, $lat1);
            $i++;
        }
        if (!empty($arr)) {
            $new_arr = krsort($arr);
            return key(reset($new_arr));
        } else {
            return 0;
        }
    }
}
