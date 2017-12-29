<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/21
 * Time: 11:16
 */
namespace Logic;
class Location {
    protected $registry;
    public function __construct($registry) {
        $this->registry = $registry;
    }

    public function findDeviceCurrentLocation($device_id) {
        $sys_model_locations = new \Sys_Model\Location_Records($this->registry);
        return $sys_model_locations->findLastLocation($device_id);
    }
}