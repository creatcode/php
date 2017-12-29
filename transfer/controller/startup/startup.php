<?php
class ControllerStartupStartup extends Controller {
    public function index() {
        $settings = $this->db->table('setting')->where(array('user_id' => '0'))->select();

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