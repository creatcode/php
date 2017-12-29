<?php
namespace Logic;

class Setting {
    private $registry;
    public function __construct($registry) {
        $this->registry = $registry;
        $this->sys_model_setting = new \Sys_Model\Setting($registry);
    }

    public function editSetting($data = array()) {
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $condition = array(
                    'key' => $k
                );
                $total = $this->sys_model_setting->getTotalSettings($condition);
                if ($total <= 0) {          // 键名不存在时
                    $param = array(
                        'code' => 'config',
                        'key' => $k,
                        'value' => $v
                    );
                    $this->sys_model_setting->addSetting($param);
                } else {                    // 键名存在时
                    $param = array(
                        'value' => $v
                    );
                    $this->sys_model_setting->updateSetting($condition, $param);
                }

            }
            $this->registry->get('cache_file')->delete('system_config');
        }
    }
}