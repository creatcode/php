<?php
class ControllerAccountAd extends Controller {
    public function index() {
        $lat = $this->request->post['lat'];
        $lng = $this->request->post['lng'];
        $is_scenic = $this->request->get_request_header('sing') == 'BBC' ? 1 : 0;

        $data['has_ad'] = 1;
        $data['front_image'] = HTTP_IMAGE . 'images/ad/front/default.jpg';
        $data['ad_inner_image'] = HTTP_IMAGE . 'images/ad/front/time.jpg';
        $this->response->showSuccessResult($data);
    }

}