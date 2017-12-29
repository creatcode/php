<?php
class ControllerSystemCommon extends Controller {
    /**
     * 获取最新的版本信息（for 安卓）
     */
    public function version() {
        $this->load->library('sys_model/version', true);
        $version_info = $this->sys_model_version->getLastestVersionInfo(array('type' => 3));

        $this->response->showSuccessResult(array(
            'version_name' => $version_info['version_name'],
            'version_code' => $version_info['version_code'] + 0,
            'url' => HTTP_STATIC . $version_info['filepath'],
            'description' => $version_info['description'],
            'add_time' => $version_info['add_time']
        ));
    }
}