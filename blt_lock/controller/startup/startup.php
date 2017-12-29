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

        if (isset($this->request->get['lang'])) {
            $lang = $this->request->get['lang'];
        } else {
            $lang = $this->config->get('config_admin_language');
        }
        $where = array(
            'code' => $this->db->escape($lang)
        );

        //加载控制器对应的语言
        $result = $this->db->table('language')->where($where)->find();
        if ($result) {
            $this->config->set('config_language_id', $result['language_id']);
            $parts = explode('/', preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$this->request->get('route')));
            if (count($parts) >= 2) {
                $language = new Language($result['directory']);
                $language->load($result['directory']);
                $controller_language = sprintf('%s/%s', $parts[0], $parts[1]);
                $language->load($controller_language);
                $this->registry->set('language', $language);
            }
        }
    }
}