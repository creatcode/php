<?php
/**
 * 东莞市亦强软件有限公司
 * Author: 罗剑波
 * Time: 1/8/2017 9:51 AM
 */

class ControllerArticleIndex extends Controller {
    public function index() {

        $this->load->library('sys_model/article');
        $data = array();

        $condition = array();
        $order = 'article_sort ASC';
        $result = $this->sys_model_article->getArticleList($condition, $order);
        if (is_array($result) && !empty($result)) {
            foreach ($result as $val) {
                $data[] = array(
                    'id' => $val['article_id'],
                    'code' => $val['article_code'],
                    'link' => sprintf('%sarticle/zh/%s.html', HTTP_IMAGE, $val['article_code'])
                );
            }
        }

        $this->response->showSuccessResult($data);
    }

    public function ad() {
        $gets = $this->request->get(array('adv_id'));
        $adv_id = $gets['adv_id'];
        if ($adv_id) {
            $adv_id = intval($adv_id);
            $this->load->library('sys_model/advertisement');
            $advertisementInfo = $this->sys_model_advertisement->getAdvertisementInfo(array('adv_id' => $adv_id));
            if ($advertisementInfo) {
                $this->assign('title', $advertisementInfo['msg_title']);
                $this->assign('add_time', date('Y-m-d H:i:s', $advertisementInfo['adv_add_time']));
                $this->assign('image_path', HTTP_IMAGE . $advertisementInfo['adv_image']);
                $this->response->setOutput($this->load->view('article/advertisement', $this->output));
            } else {
                $this->response->setOutput($this->load->view('error/404', $this->output));
            }
        } else {
            $this->response->setOutput($this->load->view('error/404', $this->output));
        }
    }
}
