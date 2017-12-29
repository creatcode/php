<?php
class ControllerFeedbackFeedback extends Controller {
    /**
     * 反馈信息
     */
    public function addFeedback() {
        if (!isset($this->request->post['content']) || empty($this->request->post['content'])) {
            $this->response->showErrorResult($this->language->get('error_feedback_content'), 141);
        }
        $user_info = $this->startup_user->getUserInfo();
        $data['user_id'] = $this->startup_user->userId();
        $data['user_name'] = $user_info['mobile'];
        $data['content'] = $this->request->post['content'];
        $data['add_time'] = time();

        $this->load->library('sys_model/feedback');
        $insert_id = $this->sys_model_feedback->addFeedback($data);

        $insert_id ? $this->response->showSuccessResult(array('feedback_id' => $insert_id), $this->language->get('text_feedback')) : $this->response->showErrorResult($this->language->get('error_database_operation_failure'),4);
    }

    /**
     * 停车拍照
     */
    public function addNormalParking() {
        if (!isset($this->request->post['lat']) || empty($this->request->post['lat'])) {
            $this->response->showErrorResult($this->language->get('error_empty_lat'), 136);
        }
        if (!isset($this->request->post['lng']) || empty($this->request->post['lng'])) {
            $this->response->showErrorResult($this->language->get('error_empty_lng'), 137);
        }

        if (!isset($this->request->post['bicycle_sn']) || empty($this->request->post['bicycle_sn'])) {
            $this->response->showErrorResult($this->language->get('error_empty_bicycle_sn'), 138);
        }

        if (!((isset($this->request->post['parking_image']) && !empty($this->request->post['parking_image'])) //base_64图片
            || isset($this->request->files['parking_image']) && !empty($this->request->files['parking_image']))) // 文件图片
        {
            $this->response->showErrorResult($this->language->get('error_empty_parking_image'), 139);
        }

        $user_info = $this->startup_user->getUserInfo();
        $data['bicycle_sn'] = $this->request->post['bicycle_sn'];

        $data['lat'] = $this->request->post['lat'];
        $data['lng'] = $this->request->post['lng'];
        $data['content'] = isset($this->request->post['content']) ? $this->request->post['content'] : '';
        $data['user_id'] = $user_info['user_id'];
        $data['user_name'] = $user_info['real_name'];
        $data['add_time'] = time();

        $this->load->library('sys_model/bicycle', true);
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('bicycle_sn' => $data['bicycle_sn']));
        if (empty($bicycle_info)) {
            $this->response->showErrorResult($this->language->get('error_bicycle_sn_nonexistence'), 140);
        }
        $file_info['state'] = 'FAILURE';
        if (isset($this->request->files['parking_image']) || isset($this->request->post['parking_image'])) {
            $uploader = new Uploader(
                'parking_image',
                array(
                    'allowFiles' => array('.jpeg', '.jpg', '.png'),
                    'maxSize' => 10 * 1024 * 1024,
                    'pathFormat' => 'normal_parking/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:4}'
                ),
                empty($this->request->files['parking_image']) ? 'base64' : 'upload', // upload, base64 or remote
                $this->request->files //文件上传变量数组，base64的不用提供，内部直接用$_POST[字段名]作为数据
            );
            $file_info = $uploader->getFileInfo();
        }

        if ($file_info['state'] == 'SUCCESS') {
            // 图片压缩
            $image_obj = new \Image(DIR_STATIC . $file_info['filePath']);
            $w = $image_obj->getWidth();
            $h = $image_obj->getHeight();
            $image_obj->resize($w, $h);
            $image_obj->save(DIR_STATIC . $file_info['filePath']);

            $data['parking_image'] = $file_info['url'];
        }
        $this->load->library('sys_model/feedback', true);
        $insert_id = $this->sys_model_feedback->addNormalParking($data);
        $insert_id ? $this->response->showSuccessResult(array('nor_parking_id' => $insert_id), $this->language->get('success_submit')) : $this->response->showErrorResult($this->language->get('error_database_operation_failure'),4);
    }
}