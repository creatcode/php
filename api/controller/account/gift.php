<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2017/2/9
 * Time: 15:19
 */
class ControllerAccountGift extends Controller {
    public function __construct($registry) {
        parent::__construct($registry);

        $this->load->library('sys_model/gift', true);
    }

    /**
     * 获取礼品活动信息
     */
    public function index() {
        $activity_id = $this->request->post('activity_id');
        $user_id = $this->startup_user->userId();

        // 活动信息
        $condition = array(
            'activity_id' => $activity_id,
            'activity_state' => '1'
        );
        $activityInfo = $this->sys_model_gift->getGiftActivityInfo($condition);
        if (empty($activityInfo)) {
            $this->response->showErrorResult('活动不存在或已失效');
        }

        // 用户活动订单信息
        $condition = array(
            'activity_id' => $activity_id,
            'user_id' => $user_id
        );
        $giftOrderInfo = $this->sys_model_gift->getGiftOrderInfo($condition);
        $has_order = 0;
        $orderInfo = array();
        if (!empty($giftOrderInfo)) {
            $has_order = 1;
            $orderInfo = array(
                'consignee'                => $giftOrderInfo['consignee'],
                'phone'                     => $giftOrderInfo['phone'],
                'gift_name'                => $giftOrderInfo['gift_name'],
                'address'                  => $giftOrderInfo['address'],
                'state'                    => $giftOrderInfo['state'],
                'shipping_company'       => $giftOrderInfo['shipping_company'],
                'shipping_code'           => $giftOrderInfo['shipping_code'],
            );
        }

        $data = array(
            'activity_title' => $activityInfo['activity_title'],
            'activity_image' => !empty($activityInfo['activity_image']) ? HTTP_IMAGE . $activityInfo['activity_image'] : HTTP_IMAGE . 'images/default.jpg',
            'activity_description' => $activityInfo['activity_description'],
            'has_order' => $has_order,
            'order_info' => $orderInfo,
        );

        $this->response->showSuccessResult($data);
    }

    /**
     * 礼品列表
     */
    public function getGiftList() {
        $where = "is_show='1' and salenum < storage";
        $order = 'sort_order DESC';

        $page = isset($this->request->post['page']) ? (intval($this->request->post['page']) ? intval($this->request->post['page']) : 1) : 1;
        $start = ($page - 1) * $this->config->get('config_limit_admin');
        $end = $this->config->get('config_limit_admin');
        $limit = "$start, $end";
        $field = 'gift_id, gift_name';

        $total = $this->sys_model_gift->getTotalGifts($where);
        $gift_list = $this->sys_model_gift->getGiftList($where, $order, $limit, $field);

        $result = array(
            'total_items_count' => $total + 0,
            'total_pages' => ceil($total / $this->config->get('config_limit_admin')),
            'page' => $page,
            'items' => $gift_list
        );

        $this->response->showSuccessResult($result);
    }

    /**
     * 兑换礼品
     */
    public function exchange() {
        $input = $this->request->post(array('gift_id', 'activity_id', 'consignee', 'phone', 'address'));
        $now = time();

        // 活动信息
        $condition = array(
            'activity_start_time' => array('elt', $now),
            'activity_end_time' => array('egt', $now),
            'activity_state' => '1'
        );
        $activityInfo = $this->sys_model_gift->getGiftActivityInfo($condition);
        if (empty($activityInfo)) {
            $this->response->showErrorResult('活动不存在或已失效');
        }

        // 是否参数活动的礼品
        $condition = array(
            'activity_id' => $input['activity_id'],
            'gift_id' => $input['gift_id'],
        );
        $isactivityGift = $this->sys_model_gift->getGiftActivityToGiftList($condition);
        if (empty($isactivityGift)) {
            $this->response->showErrorResult('该礼品没有参与此次活动');
        }

        // 礼品信息
        $where = "gift_id = '{$input['gift_id']}' and is_show='1' and salenum < storage";
        $giftInfo = $this->sys_model_gift->getGiftInfo($where);
        if (empty($giftInfo)) {
            $this->response->showErrorResult('礼品已下架');
        }

        // 限制兑换数量
        $userInfo = $this->startup_user->getUserInfo();
        if ($giftInfo['is_limit_num'] == 1) {
            $condition = array(
                'user_id' => $userInfo['user_id'],
                'gift_id' => $giftInfo['gift_id']
            );
            $userGiftOrderTotal = $this->sys_model_gift->getTotalGiftOrders($condition);
            if ($userGiftOrderTotal >= $giftInfo['limit_num']) {
                $this->response->showErrorResult('兑换数量已达上限');
            }
        }

        // 兑换数量
        $gift_num = 1;

        $order_sn = token(32);
        $data = array(
            'order_sn'      => $order_sn,
            'user_id'       => $userInfo['user_id'],
            'activity_id'   => $input['activity_id'],
            'gift_id'       => $giftInfo['gift_id'],
            'gift_name'     => $giftInfo['gift_name'],
            'gift_num'      => $gift_num,
            'consignee'     => $input['consignee'],
            'phone'         => $input['phone'],
            'address'       => $input['address'],
            'state'         => '0',
            'add_time'      => $now,
        );
        $order_id = $this->sys_model_gift->addGiftOrder($data);
        if ($order_id) {
            // 添加礼品售出数量
            $condition = array(
                'gift_id' => $data['gift_id']
            );
            $data = array(
                'salenum' => 'salenum+1'
            );
            $this->sys_model_gift->updateGift($condition, $data);
            $this->response->showSuccessResult();
        }
    }

    /**
     * 获取礼品订单详情
     */
    public function getGiftOrderInfo() {
        $order_sn = $this->request->post('order_sn');
        $user_id = $this->startup_user->userId();
        if (empty($order_sn)) {
            $this->response->showErrorResult('订单不存在');
        }
        $condition = array(
            'order_sn' => $order_sn,
            'user_id' => $user_id,
        );
        $userGiftOrderInfo = $this->sys_model_gift->getGiftOrderInfo($condition);
        if (!empty($userGiftOrderInfo) && is_array($userGiftOrderInfo)) {
            $this->response->showSuccessResult($userGiftOrderInfo);
        }
        $this->response->showErrorResult('订单不存在');
    }
}