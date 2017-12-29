<?php

/**
 * 高德地图api
 *
 * Class amapApi
 */
class amapApi
{

    /**
     * 高德地图 转换高德地图经纬度
     *
     * @param $lng
     * @param $lat
     * @return string  返回坐标点
     */
    public function convert($lng, $lat)
    {
        $url = 'http://restapi.amap.com/v3/assistant/coordinate/convert?';
        $data = 'key=e4d3d0e0954b74376a1c177213585e56&locations='.$lng.','.$lat.'&coordsys=gps';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $data_array = json_decode($output,true);
        return $data_array['infocode'] == '10000' ? $data_array['locations'] : '';
    }


    /**
     * 高德地图 位置逆编码
     *
     * @param $coordinate
     * @return string  位置
     */
    public function regeo($coordinate)
    {
        $url = 'http://restapi.amap.com/v3/geocode/regeo?';
        $data = 'key=8ae81c05d17c8d426fc8b59c3bd1961e&location='.$coordinate;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $data_array = json_decode($output,true);
        return $data_array['infocode'] == '10000' ? $data_array['regeocode']['formatted_address'] : '';
    }


}