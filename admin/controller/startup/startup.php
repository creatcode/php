<?php
class ControllerStartupStartup extends Controller {
    public function index() {
        $this->registry->set('cache_file', new \Cache\File());
        if ($this->cache_file->get('system_config')) {
            $settings = $this->cache_file->get('system_config');
        } else {
            $settings = $this->db->table('setting')->where(array('user_id' => '0'))->select();
            $this->cache_file->set('system_config', $settings);
        }

        foreach ($settings as $setting) {
            if (!$setting['serialized']) {
                $this->config->set($setting['key'], $setting['value']);
            } else {
                $this->config->set($setting['key'], json_decode($setting['value'], true));
            }
        }

        $where = array(
            'code' => $this->db->escape($this->config->get('config_admin_language'))
        );

        $result = $this->db->table('language')->where($where)->find();
        if ($result) {
            $this->config->set('config_language_id', $result['language_id']);
        }

        $language = new Language($this->config->get('config_admin_language'));
        $language->load($this->config->get('config_admin_language'));
        $this->registry->set('language', $language);
    }
}