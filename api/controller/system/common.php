<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/1/3
 * Time: 17:38
 */
class ControllerSystemCommon extends Controller {

    private $wx_appid;
    private $wx_appsecret;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->wx_appid = 'wxcbfa44fc0c22072f';
        $this->wx_appsecret = 'dfa95aa9409e9c8586c7d256e851ad83';

        $this->wx_app_appid = 'wx8f9bbd8556b72750';
        $this->wx_app_appsecret = '1540f0e905743100089f04176c6dce0f';
		
		$this->scenic_spot_wx_app_appid = 'wx19b38eb4e493fa2a';
        $this->scenic_spot_wx_app_appsecret = '22cc1ace52b389740247207546c16e4f';
    }

    /**
     * 微信获取openId
     */
    public function wechat() {
        $code = $this->request->get('code');
        if (!empty($code)) {
            $res = $this->getAccessTokenMess($this->wx_appid, $this->wx_appsecret, $code);
            // $access_token = $res['access_token'];

            // openid
            $expire = TIMESTAMP + 60 * 60 * 24 * 30 * 12;
            setcookie("openid", $res['openid'], $expire, '/');

            // 重定向到微信端
            $redirect_url = $this->request->get('redirect_uri');
            $this->response->redirect($redirect_url);
        } else {
            $current = $this->url->link('system/common/wechat', 'redirect_uri='.$this->request->get_request_header('Referer'), true);
            $current = urlencode(htmlspecialchars_decode($current));
            // snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid）
            // snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
            $scope = 'snsapi_base';
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->wx_appid}&redirect_uri={$current}&response_type=code&scope={$scope}#wechat_redirect";
            $this->response->redirect($url);
        }
    }

    /**
     * 微信小程序获取openId
     */
    public function wechatapp() {
        $code = $this->request->post('code');
        //公众号来源
        $client = $this->request->post('client');
        switch ($client){
            
            //单车小程序
            case '1':
                $app_id = $this->wx_app_appid;
                $app_appsecret = $this->wx_app_appsecret;
                break;

            //景区小程序
            case '2':
                $app_id = $this->scenic_spot_wx_app_appid;
                $app_appsecret = $this->scenic_spot_wx_app_appsecret;
                break;

            //默认单车小程序
            default:
                $app_id = $this->wx_app_appid;
                $app_appsecret = $this->wx_app_appsecret;
                break;
        }
        if (!empty($code)) {
            $res = $this->getSessionKeyMess($app_id, $app_appsecret, $code);
            // $access_token = $res['access_token'];

            // openid
            $expire = TIMESTAMP + 60 * 60 * 24 * 30 * 12;
            setcookie("openid", $res['openid'], $expire, '/');

            $data = array(
                'openid' => $res['openid']
            );
            $this->response->showSuccessResult($data, $this->language->get('success_operation'));
        }
    }

    /**
     * 获取微信access_token信息
     * @param $appId
     * @param $appSecret
     * @param $code
     * @return mixed|null`
     */
    private function getAccessTokenMess($appid, $appSecret, $code) {
        $access_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appSecret}&code={$code}&grant_type=authorization_code";
        $res = json_decode($this->httpGet($access_url), true);
        return $res;
    }

    /**
     * 获取微信session_key信息(专供：微信小程序)
     * @param $appId
     * @param $appSecret
     * @param $code
     * @return mixed|null`
     */
    private function getSessionKeyMess($appid, $appSecret, $code) {
        $access_url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appSecret}&js_code={$code}&grant_type=authorization_code";
        $res = json_decode($this->httpGet($access_url), true);
        return $res;
    }

    /**
     * curl GET请求
     * @param $url
     * @return mixed
     */
    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }



    /**
     * 微信JSSDK参数
     */
    public function wechat_jssdk() {
        $config = array(
            'app_id' => $this->wx_appid,
            'app_secret' => $this->wx_appsecret,
            'url' => $this->request->get_request_header('Referer')
        );

        $obj = new Wechat_jssdk($config);
        $sign_package = $obj->GetSignPackage();

        $this->response->showSuccessResult($sign_package, $this->language->get('success_get_jssdk_data'));
    }

    /**
     * 获取联系方式
     */
    public function contact() {
        $this->response->showSuccessResult(array(
            'wechat' => $this->config->get('config_wechat'),
            'phone' => $this->config->get('config_phone'),
            'email' =>$this->config->get('config_email'),
            'web' => $this->config->get('config_web'),
            'hotline' => $this->config->get('config_hotline')
        ));
    }

    /**
     * 获取最新的版本信息（for 安卓）
     */
    public function version() {
        $this->load->library('sys_model/version', true);
        $version_info = $this->sys_model_version->getLastestVersionInfo(array('type' => 2));

        $this->response->showSuccessResult(array(
            'version_name' => $version_info['version_name'],
            'version_code' => $version_info['version_code'] + 0,
            'url' => HTTP_STATIC . $version_info['filepath'],
            'description' => $version_info['description'],
            'add_time' => $version_info['add_time']
        ));
    }

    /**
     * 获取当前的常规广告
     */
    public function ad() {
file_put_contents('/dev/shm/UA.log', date('Y-m-d H:i:s ') . getIP() . PHP_EOL . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL, FILE_APPEND);
        $get_data = $this->request->get;
        $this->load->library('logic/advertisement', true);
        $lat = isset($this->request->post['lat']) ? ($this->request->post['lat'] + 0) : 0;
        $lng = isset($this->request->post['lng']) ? ($this->request->post['lng'] + 0) : 0;
        //判断坐标是否在已开通的区域内；
        $this->load->library('sys_model/region', true);
        $where = array(
            'region_bounds_southwest_lat' => array('elt', $lat),
            'region_bounds_northeast_lat' => array('egt', $lat),
            'region_bounds_southwest_lng' => array('elt', $lng),
            'region_bounds_northeast_lng' => array('egt', $lng),
        );
        $region_list = $this->sys_model_region->getRegionList($where);
        if(empty($region_list)){
            //取出未开通区域的广告；
            $this->load->library('sys_model/advertisement', true);
            $items = $this->sys_model_advertisement->getAdvertisementList(array('adv_region_id' => '-99999'));
            foreach($items as &$v){
                $v['image']    = HTTP_STATIC.$v['adv_image'];
                $v['image1x'] = HTTP_STATIC.$v['adv_image_1x'];
                $v['image2x'] = HTTP_STATIC.$v['adv_image_2x'];
                $v['image3x'] = HTTP_STATIC.$v['adv_image_3x'];
                $v['image4x'] = HTTP_STATIC.$v['adv_image_3x'];
                $v['image5x'] = HTTP_STATIC.$v['adv_image_3x'];
            }
        }else{
            //取出相应地区的广告
            $items = $this->logic_advertisement->getAdvertisementByLocation($lat, $lng, 0);
        }

        foreach($items as $k => $v){
            // android已经是最高版本则不提示
            if($v['adv_max_version_android'] && $get_data['fromApi'] == 'android' ){
                if($get_data['version'] >= $v['adv_max_version_android']){
                    unset($items[$k]);
                }
            }
            // ios已经是最高版本则不提示
            if($v['adv_max_version_ios'] && $get_data['fromApi'] ==  'ios' ){
                if($get_data['version'] >= $v['adv_max_version_ios']){
                    unset($items[$k]);
                }
            }

        }

        $this->response->showSuccessResult(array(
            'has_ad' => !empty($items),
            'items' => $items
        ));
    }

    /**
     * 获取当前启动页的广告
     * 不能用常规广告接口，兼容就版本
     */
    public function launch_ad() {
        $this->load->library('logic/advertisement', true);
        $lat = isset($this->request->post['lat']) ? ($this->request->post['lat'] + 0) : 0;
        $lng = isset($this->request->post['lng']) ? ($this->request->post['lng'] + 0) : 0;
        $items = $this->logic_advertisement->getAdvertisementByLocation($lat, $lng, 1);
		$this->db->getLastSql();

        $this->response->showSuccessResult(array(
            'has_ad' => !empty($items),
            'items' => $items
        ));
    }
}
