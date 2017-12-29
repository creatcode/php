<?php

/**
 * Class ControllerOperationBikeFault
 */
class ControllerOperationBikeFault extends Controller {
    private $cur_url = null;
    private $error = null;

    /**
     * ControllerOperationBikeFault constructor.
     * @param $registry
     */
    public function __construct($registry) {
        parent::__construct($registry);
        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载fault Model
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/orders', true);
        $this->load->library('sys_model/fault', true);
        $this->load->library('sys_model/lock', true);
    }

    /**
     * 故障记录列表
     */
    public function index() {
        $filter = $this->request->get(array('filter_type', 'cooperator_name', 'bicycle_sn' , 'lock_type', 'lost_time'));

        $condition = array();
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle.bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['lock_type'])) {
            $condition['lock.lock_type'] = $filter['lock_type'];
        }
        if (!empty($filter['lost_time'])) {
            $before_time = time()-(60*60*$filter['lost_time']);
            $condition['lock.system_time'] = array('lt', $before_time);
        }

        $condition['bicycle.lock_sn'] = array('NEQ', "");
        $condition['cooperator.cooperator_id'] = $this->logic_admin->getParam('cooperator_id');

        $filter_types = array(
            'bicycle_sn' => '单车编号',
        );

        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'bicycle.add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'bicycle.bicycle_sn, lock.lock_sn, lock.lock_type, lock.battery, lock.lng ,lock.lat, lock.system_time, lock.regeo_time, lock.position, cooperator.cooperator_name';
        $join = array(
            'lock' => 'lock.lock_sn = bicycle.lock_sn',
            'cooperator' => 'cooperator.cooperator_id = bicycle.cooperator_id',
        );
        $result = $this->sys_model_bicycle->getBicycleList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_bicycle->getTotalBicycles($condition, $join);

        $model = array(
            'lock_type' => get_lock_type()
        );

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                foreach ($model as $k => $v) {
                    $item[$k] = isset($v[$item[$k]]) ? $v[$item[$k]] : '';
                }
                //判断最后更新时间是否大于逆转码，是的话就开始进行逆转码，并且保存地址
                if($item['system_time'] > $item['regeo_time']){
                    if($item['lng'] && $item['lat']){
                        $coordinate = $this->convert($item['lng'],$item['lat']);
                        $item['position'] = $this->regeo($coordinate);
                        $this->sys_model_lock->updateLock(array('lock_sn'=>$item['lock_sn']),array('regeo_time'=>time(),'position'=>$item['position']));
                    }
                }
                //获取最后一个使用单车的用户
                $last_order_info = $this->sys_model_orders->getOrdersInfo(array('bicycle_sn'=>$item['bicycle_sn'],'order_state'=>2),'user_name','end_time desc');
                $item['user_name'] = $last_order_info['user_name'];
                //故障次数
                $fault_num = $this->sys_model_fault->getTotalFaults(array('bicycle_sn'=>$item['bicycle_sn']));
                $item['fault_num'] = $fault_num;
                $item['last_update_time'] = date("Y-m-d H:i:s",$item['system_time']);
                $item['lost_day'] = ceil((time() - $item['system_time'])/86400);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->assign('export_action', $this->url->link('operation/bikeFault/export'));

        $this->response->setOutput($this->load->view('operation/bike_fault_list', $this->output));
    }


    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('单车编号');
        $this->setDataColumn('合伙人');
        $this->setDataColumn('锁编号');
        $this->setDataColumn('锁类型');
        $this->setDataColumn('锁电量');
        $this->setDataColumn('故障次数');
        $this->setDataColumn('最后更新时间');
        $this->setDataColumn('失联天数');
        $this->setDataColumn('最后位置');
        $this->setDataColumn('最后锁车的用户');
        return $this->data_columns;
    }


    /**
     * 位置逆编码
     *
     * @param $coordinate
     * @return string
     */
    private function regeo($coordinate)
    {
        $url = 'http://restapi.amap.com/v3/geocode/regeo?';
        $data = 'key=8ae81c05d17c8d426fc8b59c3bd1961e&location='.$coordinate;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $data_array = json_decode($output,true);
        return $data_array['infocode'] == '10000' ? $data_array['regeocode']['formatted_address'] : '';
    }

    /**
     * 高德地图 转换高德地图经纬度
     *
     * @param $lng
     * @param $lat
     * @return string
     */
    private function convertAmap($lng, $lat)
    {
        $url = 'http://restapi.amap.com/v3/assistant/coordinate/convert?';
        $data = 'key=e4d3d0e0954b74376a1c177213585e56&locations='.$lng.','.$lat.'&coordsys=gps';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $data_array = json_decode($output,true);
        return $data_array['infocode'] == '10000' ? $data_array['locations'] : '';
    }

    /**
     * 转换高德地图经纬度
     *
     * @param $lng
     * @param $lat
     * @return string
     */
    private function convert($lng, $lat)
    {
        $ee = 0.00669342162296594323;
        $a = 6378245.0;
        $dlat = $this->transformlat($lng - 105.0, $lat - 35.0);
        $dlng = $this->transformlng($lng - 105.0, $lat - 35.0);
        $radlat = $lat / 180.0 * M_PI;
        $magic = sin($radlat);
        $magic = 1 - $ee * $magic * $magic;
        $sqrtmagic = sqrt($magic);
        $dlat = ($dlat * 180.0) / (($a * (1 - $ee)) / ($magic * $sqrtmagic) * M_PI);
        $dlng = ($dlng * 180.0) / ($a / $sqrtmagic * cos($radlat) * M_PI);
        $mglat = $lat + $dlat;
        $mglng = $lng + $dlng;
        $b = $lng * 2 - $mglng;
        $c = $lat * 2 - $mglat;
        return "$b,$c";
    }

    /**
     * @param $lng
     * @param $lat
     * @return string
     */
    private function transformlat($lng, $lat)
    {
        $ret = -100.0 + 2.0 * $lng + 3.0 * $lat + 0.2 * $lat * $lat + 0.1 * $lng * $lat + 0.2 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * M_PI) + 20.0 * sin(2.0 * $lng * M_PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lat * M_PI) + 40.0 * sin($lat / 3.0 * M_PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($lat / 12.0 * M_PI) + 320 * sin($lat * M_PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

    /**
     * @param $lng
     * @param $lat
     * @return string
     */
    private function transformlng($lng, $lat)
    {
        $ret = 300.0 + $lng + 2.0 * $lat + 0.1 * $lng * $lng + 0.1 * $lng * $lat + 0.1 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * M_PI) + 20.0 * sin(2.0 * $lng * M_PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lng * M_PI) + 40.0 * sin($lng / 3.0 * M_PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($lng / 12.0 * M_PI) + 300.0 * sin($lng / 30.0 * M_PI)) * 2.0 / 3.0;
        return $ret;
    }

    /**
     * 导出
     */
    public function export() {
        set_time_limit(0);

        $filter = $this->request->post(array('filter_type', 'cooperator_name', 'bicycle_sn' , 'lock_type', 'lost_time'));

        $condition = array();
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle.bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['cooperator_name'])) {
            $condition['cooperator.cooperator_name'] = array('like', "%{$filter['cooperator_name']}%");
        }
        if (!empty($filter['lock_type'])) {
            $condition['lock.lock_type'] = $filter['lock_type'];
        }
        if (!empty($filter['lost_time'])) {
            $before_time = time()-(60*60*$filter['lost_time']);
            $condition['lock.system_time'] = array('lt', $before_time);
        }

        $condition['bicycle.lock_sn'] = array('NEQ', "");

        $filter_types = array(
            'bicycle_sn' => '单车编号',
            'cooperator_name' => '合伙人',
        );

        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type))
        {
            reset($filter_types);
        }

        $order = 'bicycle.add_time DESC';
        $field = 'bicycle.bicycle_sn, lock.lock_sn, lock.lock_type, lock.battery, lock.lng ,lock.lat, lock.system_time, lock.regeo_time, lock.position, cooperator.cooperator_name';
        $join = array(
            'lock' => 'lock.lock_sn = bicycle.lock_sn',
            'cooperator' => 'cooperator.cooperator_id = bicycle.cooperator_id',
        );
        $result = $this->sys_model_bicycle->getBicycleList($condition, $order, '', $field, $join);

        //导出判断，如果太多没有地址的，就不给导出
        $condition['lock.system_time'] = array('gt', 'lock.regeo_time');
        $bicycle_count = $this->sys_model_bicycle->getTotalBicycles($condition,$join);
        if($bicycle_count > 100){
            $data = [
                'title' => '地址缺失太多',
                'header' => [''],
                'list' => ['']
            ];
            $this->load->controller('common/base/exportExcel', $data);
            exit();
        }


        $model = array(
            'lock_type' => get_lock_type()
        );

        $list = array();

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                foreach ($model as $k => $v) {
                    $item[$k] = isset($v[$item[$k]]) ? $v[$item[$k]] : '';
                }
                if($item['system_time'] > $item['regeo_time']){
                    $coordinate = $this->convert($item['lng'],$item['lat']);
                    /** @var TYPE_NAME $coordinate */
                    $item['position'] = $this->regeo($coordinate);
                    $this->sys_model_lock->updateLock(array('lock_sn'=>$item['lock_sn']),array('regeo_time'=>time(),'position'=>$item['position']));
                }
                $last_order_info = $this->sys_model_orders->getOrdersInfo(array('bicycle_sn'=>$item['bicycle_sn'],'order_state'=>2),'user_name','end_time desc');
                $item['user_name'] = $last_order_info['user_name'];
                $fault_num = $this->sys_model_fault->getTotalFaults(array('bicycle_sn'=>$item['bicycle_sn']));
                $item['fault_num'] = $fault_num;
                $item['last_update_time'] = date("Y-m-d H:i:s",$item['system_time']);
                $item['lost_day'] = ceil((time() - $item['system_time'])/86400);
                $list[] = array(
                    'bicycle_sn' => $item['bicycle_sn'],
                    'cooperator_name' => $item['cooperator_name'],
                    'lock_sn' => $item['lock_sn'],
                    'lock_type' => $item['lock_type'],
                    'battery' => $item['battery'],
                    'fault_num' => $item['fault_num'],
                    'last_update_time' => $item['last_update_time'],
                    'lost_day' => $item['lost_day'],
                    'position' => $item['position'],
                    'user_name' => $item['user_name'],
                );
            }
        }

        $data = array(
            'title' => '故障单车列表',
            'header' => array(
                'bicycle_sn' => '单车编号',
                'cooperator_name' => '合伙人',
                'lock_sn' => '锁编号',
                'lock_type' => '锁类型',
                'battery' => '锁电量',
                'fault_num' => '故障次数',
                'last_update_time' => '最后更新时间',
                'lost_day' => '失联天数',
                'position' => '最后位置',
                'user_name' => '最后锁车的用户',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

}