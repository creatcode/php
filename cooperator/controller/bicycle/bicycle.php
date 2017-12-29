<?php
class ControllerBicycleBicycle extends Controller {
    private $cooperator_id = null;
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');

        // 加载bicycle Model
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/lock', true);

        // 加载 region Model
        $this->load->library('sys_model/region', true);
    }

    /**
     * 单车列表
     */
    public function index() {
        $filter = $this->request->get(array('bicycle_sn', 'type', 'lock_sn', 'region_name', 'is_using', 'add_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (is_numeric($filter['type'])) {
            $condition['type'] = (int)$filter['type'];
        }
        if (!empty($filter['lock_sn'])) {
            $condition['lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (!empty($filter['region_name'])) {
            $condition['region.region_name'] = array('like', "%{$filter['region_name']}%");
        }
        if (is_numeric($filter['is_using'])) {
            $condition['is_using'] = (int)$filter['is_using'];
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['bicycle.add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
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

        $field = 'bicycle.*,region.region_name';

        $join = array(
            'region' => 'region.region_id=bicycle.region_id',
        );

        $result = $this->sys_model_bicycle->getBicycleList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_bicycle->getTotalBicycles($condition, $join);

        $model = array(
            'type' => get_bicycle_type(),
            'is_using' => get_common_boolean()
        );
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                foreach ($model as $k => $v) {
                    $item[$k] = isset($v[$item[$k]]) ? $v[$item[$k]] : '';
                }
                $item['add_time'] = isset($item['add_time']) && !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['edit_action'] = $this->url->link('bicycle/bicycle/edit', 'bicycle_id='.$item['bicycle_id']);
                $item['delete_action'] = $this->url->link('bicycle/bicycle/delete', 'bicycle_id='.$item['bicycle_id']);
                $item['info_action'] = $this->url->link('bicycle/bicycle/info', 'bicycle_id='.$item['bicycle_id']);
            }
        }

        $filter_types = array(
            'bicycle_sn' => '单车编号',
            'lock_sn' => '车锁编号',
            'region_name' => '区域'
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        // 使用中单车数
        $condition = array(
            'is_using' => 1
        );
        $using_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);
        // 故障单车数
        $condition = array(
            'fault' => 1
        );
        $fault_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('total_bicycle', $total);
        $this->assign('using_bicycle', $using_bicycle);
        $this->assign('fault_bicycle', $fault_bicycle);
        $this->assign('model', $model);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('import_action', $this->url->link('bicycle/bicycle/import'));
        $this->assign('add_action', $this->url->link('bicycle/bicycle/add'));
        $this->assign('batchadd_action', $this->url->link('bicycle/bicycle/batchadd'));
        $this->assign('lock_action', $this->url->link('lock/lock'));
        $this->assign('export_action', $this->url->link('bicycle/bicycle/export'));
        $this->assign('export_qrcode_action', $this->url->link('bicycle/bicycle/export_qrcode'));
        $this->assign('exchange_lock_action', $this->url->link('bicycle/bicycle/exchangelock','type=normal'));
        $this->assign('exchange_lock_action_bt', $this->url->link('bicycle/bicycle/exchangelock','type=bt'));

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

        $this->response->setOutput($this->load->view('bicycle/bicycle_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('单车编号');
        $this->setDataColumn('车锁编号');
        $this->setDataColumn('单车类型');
        $this->setDataColumn('城市');
        $this->setDataColumn('是否使用中');
        $this->setDataColumn('添加时间');
        return $this->data_columns;
    }

    /**
     * 批量添加单车
     */
    public function batchadd() {
        $bicycle_ids = $bicycles = array();
        $bicycle_types = get_bicycle_type();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateBatchaddForm()) {
            $input = $this->request->post(array('bicycle_sn_start', 'bicycle_sn_end', 'type', 'lock_sn', 'region_id'));

            // 区域信息
            $condition = array(
                'region_id' => $input['region_id']
            );
            $region = $this->sys_model_region->getRegionInfo($condition);
            $now = time();

            $data = array(
                'region_id' => $input['region_id'],
                'region_name' => $region['region_name'],
                'cooperator_id' => $this->cooperator_id,
                'type' => (int)$input['type'],
                'add_time' => $now
            );
            $bicycleData = array(
                'region_id' => $input['region_id'],
                'region_name' => $region['region_name'],
                'cooperator_id' => $this->cooperator_id,
                'type' => (int)$input['type'],
                'type_name' => $bicycle_types[$data['type']],
                'add_time' => $now
            );

            $bicycle_num = $input['bicycle_sn_end'] - $input['bicycle_sn_start'];

            for ($i = 0; $i <= $bicycle_num; $i++) {
                $bicycle_sn = sprintf('%06d', $input['bicycle_sn_start'] + $i);
                $rec = $this->checkBicycleSN($bicycle_sn);
                if (!$rec) {
                    continue;
                }
                $bicycleData['bicycle_sn'] = $data['bicycle_sn'] = $bicycle_sn;
                $bicycle_id = $this->sys_model_bicycle->addBicycle($data);
                $bicycleData['bicycle_id'] = $bicycle_ids[] = $bicycle_id;
                $bicycles[] = $bicycleData;

                // 生成二维码图片
                $qrcodeInfo = array(
                    'qrcodeText' => sprintf('http://bike.e-stronger.com/bike/app.php?b=%03d%02d%06d', $region['region_city_code'], $region['region_city_ranking'], $data['bicycle_sn']),
                    'fullcode' => sprintf('%03d%02d %06d', $region['region_city_code'], $region['region_city_ranking'], $data['bicycle_sn']),
                    'code' => $data['bicycle_sn']
                );
                $this->load->controller('common/qrcode/buildQrCode', $qrcodeInfo);
                $this->load->controller('common/qrcode/buildWordImage', $qrcodeInfo);
                $this->load->controller('common/qrcode/buildFrontQrCode', $qrcodeInfo);
                $this->load->controller('common/qrcode/buildBackQrCode', $qrcodeInfo);
            }

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：批量添加单车：' . implode(',', $bicycle_ids),
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 7,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->session->data['success'] = '批量添加单车成功！';
        }

        $this->assign('title', '批量添加');

        // 编辑时获取已有的数据
        $info = $this->request->post(array('bicycle_num', 'type', 'region_id'));

        $condition = array();
        $order = 'region_sort ASC';
        $regionList = $this->sys_model_region->getRegionList($condition, $order);

        $this->assign('data', $info);
        $this->assign('bicycles', $bicycles);
        $this->assign('regions', $regionList);
        $this->assign('types', get_bicycle_type());
        $this->assign('action', $this->cur_url);
        $this->assign('return_action', $this->url->link('bicycle/bicycle'));
        $this->assign('export_qrcode_action', $this->url->link('bicycle/bicycle/export_qrcode'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('bicycle/bicycle_batchadd', $this->output));
    }

    /**
     * 添加单车
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('bicycle_sn', 'type', 'lock_sn', 'region_id'));

            // 区域信息
            $condition = array(
                'region_id' => $input['region_id']
            );
            $region = $this->sys_model_region->getRegionInfo($condition);

            $now = time();
            $data = array(
                'cooperator_id' => $this->cooperator_id,
                'bicycle_sn' => $input['bicycle_sn'],
                'region_id' => $input['region_id'],
                'region_name' => $region['region_name'],
                'type' => (int)$input['type'],
                'lock_sn' => $input['lock_sn'],
                'add_time' => $now
            );
            $bicycle_id = $this->sys_model_bicycle->addBicycle($data);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：添加单车：' . $bicycle_id,
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 7,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);


            // 生成二维码图片
            $data = array(
                'qrcodeText' => sprintf('http://bike.e-stronger.com/bike/app.php?b=%03d%02d%06d', $region['region_city_code'], $region['region_city_ranking'], $input['bicycle_sn']),
                'fullcode' => sprintf('%03d%02d %06d', $region['region_city_code'], $region['region_city_ranking'], $input['bicycle_sn']),
                'code' => $input['bicycle_sn']
            );
            $this->load->controller('common/qrcode/buildQrCode', $data);
            $this->load->controller('common/qrcode/buildWordImage', $data);
            $this->load->controller('common/qrcode/buildFrontQrCode', $data);
            $this->load->controller('common/qrcode/buildBackQrCode', $data);


            $this->session->data['success'] = '添加单车成功！';
            
            $filter = array('bicycle_sn', 'type', 'lock_sn', 'region_name', 'cooperator_name', 'is_using');

            $this->load->controller('common/base/redirect', $this->url->link('bicycle/bicycle', $filter, true));
        }

        $this->assign('title', '新增单车');
        $this->getForm();
    }

    /**
     * 编辑单车
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('bicycle_sn', 'type', 'lock_sn', 'region_id'));
            $bicycle_id = $this->request->get['bicycle_id'];

            // 区域信息
            $condition = array(
                'region_id' => $input['region_id']
            );
            $region = $this->sys_model_region->getRegionInfo($condition);

            $data = array(
                'bicycle_sn' => $input['bicycle_sn'],
                'region_id' => $input['region_id'],
                'region_name' => $region['region_name'],
                'type' => (int)$input['type'],
                'lock_sn' => $input['lock_sn']
            );
            $condition = array(
                'bicycle_id' => $bicycle_id,
                'cooperator_id' => $this->cooperator_id
            );
            $this->sys_model_bicycle->updateBicycle($condition, $data);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：编辑单车：' . $bicycle_id,
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 7,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            // 生成二维码图片
            $data = array(
                'qrcodeText' => sprintf('http://bike.e-stronger.com/bike/app.php?b=%03d%02d%06d', $region['region_city_code'], $region['region_city_ranking'], $input['bicycle_sn']),
                'fullcode' => sprintf('%03d%02d %06d', $region['region_city_code'], $region['region_city_ranking'], $input['bicycle_sn']),
                'code' => $input['bicycle_sn']
            );
            $this->load->controller('common/qrcode/buildQrCode', $data);
            $this->load->controller('common/qrcode/buildWordImage', $data);
            $this->load->controller('common/qrcode/buildFrontQrCode', $data);
            $this->load->controller('common/qrcode/buildBackQrCode', $data);

            $this->session->data['success'] = '编辑单车成功！';

            $filter = array('bicycle_sn', 'type', 'lock_sn', 'region_name', 'cooperator_name', 'is_using');

            $this->load->controller('common/base/redirect', $this->url->link('bicycle/bicycle', $filter, true));
        }

        $this->assign('title', '编辑单车');
        $this->getForm();
    }

    /**
     * 删除单车
     */
    public function delete() {
        if (isset($this->request->get['bicycle_id']) && $this->validateDelete()) {
            $condition = array(
                'bicycle_id' => $this->request->get['bicycle_id'],
                'cooperator_id' => $this->cooperator_id
            );
            $this->sys_model_bicycle->deleteBicycle($condition);

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '合伙人端：删除单车：' . $this->request->get['bicycle_id'],
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 7,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->session->data['success'] = '删除单车成功！';
        }
        $filter = array('bicycle_sn', 'type', 'lock_sn', 'region_name', 'cooperator_name', 'is_using');
        $this->load->controller('common/base/redirect', $this->url->link('bicycle/bicycle', $filter, true));
    }

    /**
     * 单车详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $bicycle_id = $this->request->get('bicycle_id');
        $condition = array(
            'bicycle_id' => $bicycle_id,
            'cooperator_id' => $this->cooperator_id
        );
        $info = $this->sys_model_bicycle->getBicycleInfo($condition);
        if (!empty($info)) {
            $model = array(
                'type' => get_bicycle_type(),
                'is_using' => get_common_boolean()
            );
            foreach ($model as $k => $v) {
                $info[$k] = isset($v[$info[$k]]) ? $v[$info[$k]] : '';
            }
            $condition = array(
                'lock_sn' => $info['lock_sn']
            );

            $lock = $this->sys_model_lock->getLockInfo($condition);
            if (!empty($lock)) {
                $info['lng'] = $lock['lng'];
                $info['lat'] = $lock['lat'];
            }
        }

        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('bicycle/bicycle'));

        $this->response->setOutput($this->load->view('bicycle/bicycle_info', $this->output));
    }

    /**
     * 导入单车
     */
    public function import() {
        // 获取上传EXCEL文件数据
        $excelData = $this->load->controller('common/base/importExcel');

        if (is_array($excelData) && !empty($excelData)) {
            $count = count($excelData);
            // 从第3行开始
            if ($count >= 3) {
                for ($i = 3; $i <= $count; $i++) {
                    $data = array(
                        'bicycle_sn' => isset($excelData[$i][0]) ? $excelData[$i][0] : '',
                        'type' => 1,
                        'cooperator_id' => $this->cooperator_id,
                        'lock_sn' => isset($excelData[$i][1]) ? $excelData[$i][1] : '',
                        'add_time' => TIMESTAMP
                    );
                    $this->sys_model_bicycle->addBicycle($data);
                }
            }
        }

        $this->response->showSuccessResult('', '导入成功');
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('bicycle_sn', 'type', 'lock_sn', 'region_name', 'cooperator_name', 'is_using'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (is_numeric($filter['type'])) {
            $condition['type'] = (int)$filter['type'];
        }
        if (!empty($filter['lock_sn'])) {
            $condition['lock_sn'] = $filter['lock_sn'];
        }
        if (!empty($filter['region_name'])) {
            $condition['region.region_name'] = array('like', "%{$filter['region_name']}%");
        }
        if (is_numeric($filter['is_using'])) {
            $condition['is_using'] = (int)$filter['is_using'];
        }
        $order = 'bicycle.add_time DESC';
        $limit = '';

        $bicycles = $this->sys_model_bicycle->getBicycleList($condition, $order, $limit);
        $list = array();
        if (is_array($bicycles) && !empty($bicycles)) {
            $bicycle_types = get_bicycle_type();
            $use_states = get_common_boolean();
            foreach ($bicycles as $bicycle) {
                $list[] = array(
                    'bicycle_sn' => $bicycle['bicycle_sn'],
                    'lock_sn' => $bicycle['lock_sn'],
                    'type' => $bicycle_types[$bicycle['type']],
                    'region_name' => $bicycle['region_name'],
                    'cooperator_name' => $bicycle['cooperator_name'],
                    'is_using' => $use_states[$bicycle['is_using']]
                );
            }
        }

        $data = array(
            'title' => '单车列表',
            'header' => array(
                'bicycle_sn' => '单车编号',
                'lock_sn' => '车锁编号',
                'type' => '单车类型',
                'region_name' => '区域',
                'is_using' => '是否使用中',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 导出二维码
     */
    public function export_qrcode() {
        if (isset($this->request->get['operation']) && $this->request->get['operation'] == 'export') {
            $filter = $this->request->post(array('bicycle_sn', 'type', 'lock_sn', 'region_name', 'cooperator_name', 'is_using', 'add_time'));

            $condition = array(
                'cooperator_id' => $this->cooperator_id
            );
            $emptyFilter = true;
            if (!empty($filter['bicycle_sn'])) {
                $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
                $emptyFilter = false;
            }
            if (is_numeric($filter['type'])) {
                $condition['type'] = (int)$filter['type'];
                $emptyFilter = false;
            }
            if (!empty($filter['lock_sn'])) {
                $condition['lock_sn'] = $filter['lock_sn'];
                $emptyFilter = false;
            }
            if (!empty($filter['region_name'])) {
                $condition['region.region_name'] = array('like', "%{$filter['region_name']}%");
                $emptyFilter = false;
            }
            if (is_numeric($filter['is_using'])) {
                $condition['is_using'] = (int)$filter['is_using'];
                $emptyFilter = false;
            }
            if (!empty($filter['add_time'])) {
                $add_time = explode(' 至 ', $filter['add_time']);
                $condition['bicycle.add_time'] = array(
                    array('egt', strtotime($add_time[0])),
                    array('elt', bcadd(86399, strtotime($add_time[1])))
                );
                $emptyFilter = false;
            }

            $bicycles = array();
            if (!$emptyFilter) {
                $bicycles = $this->sys_model_bicycle->getBicycleList($condition);
            }

            $filesname = array();
            if (!empty($bicycles) && is_array($bicycles)) {
                foreach ($bicycles as $bicycle) {
                    $filesname[] = DIR_STATIC . 'images/qrcode/' . $bicycle['bicycle_sn'] . '.png';
                    $filesname[] = DIR_STATIC . 'images/qrcode/word_' . $bicycle['bicycle_sn'] . '.png';
                    $filesname[] = DIR_STATIC . 'images/qrcode/front_' . $bicycle['bicycle_sn'] . '.png';
                    $filesname[] = DIR_STATIC . 'images/qrcode/back_' . $bicycle['bicycle_sn'] . '.png';
                }
            }

//        $filename = DIR_STATIC . 'images/qrcode/bak.zip'; //最终生成的文件名（含路径）
            $filename = tempnam("/tmp", "QRCODE");

            if (is_file($filename) && file_exists($filename)) {
                @unlink($filename);
            }
            //重新生成文件
            $zip = new ZipArchive();//使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
            if ($zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {
                exit('无法打开文件，或者文件创建失败');
            }
            foreach ($filesname as $val) {
                if (file_exists($val)) {
                    $zip->addFile($val, basename($val));//第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
                }
            }
            $zip->close();//关闭
            if (!is_file($filename) || !file_exists($filename)) {
                exit("无法找到文件"); //即使创建，仍有可能失败。。。。
            }
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=自行车二维码.zip'); //文件名
            header("Content-Type: application/zip"); //zip格式的
            header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
            header('Content-Length: ' . filesize($filename)); //告诉浏览器，文件大小
            @readfile($filename);
            @unlink($filename);
        } else {
            $filter = $this->request->get(array('bicycle_sn', 'type', 'lock_sn', 'region_name', 'cooperator_name', 'is_using', 'add_time'));

            $condition = array(
                'cooperator_id' => $this->cooperator_id
            );
            $emptyFilter = true;
            if (!empty($filter['bicycle_sn'])) {
                $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
                $emptyFilter = false;
            }
            if (is_numeric($filter['type'])) {
                $condition['type'] = (int)$filter['type'];
                $emptyFilter = false;
            }
            if (!empty($filter['lock_sn'])) {
                $condition['lock_sn'] = $filter['lock_sn'];
                $emptyFilter = false;
            }
            if (!empty($filter['region_name'])) {
                $condition['region.region_name'] = array('like', "%{$filter['region_name']}%");
                $emptyFilter = false;
            }
            if (is_numeric($filter['is_using'])) {
                $condition['is_using'] = (int)$filter['is_using'];
                $emptyFilter = false;
            }
            if (!empty($filter['add_time'])) {
                $add_time = explode(' 至 ', $filter['add_time']);
                $condition['bicycle.add_time'] = array(
                    array('egt', strtotime($add_time[0])),
                    array('elt', bcadd(86399, strtotime($add_time[1])))
                );
                $emptyFilter = false;
            }

            $result = array();
            $total = 0;
            if (!$emptyFilter) {
                $order = 'bicycle.add_time DESC';
                $limit = '';
                $field = 'bicycle.*,region.region_name';
                $join = array(
                    'region' => 'region.region_id=bicycle.region_id'
                );
                $result = $this->sys_model_bicycle->getBicycleList($condition, $order, $limit, $field, $join);
                $total = $this->sys_model_bicycle->getTotalBicycles($condition, $join);
            }


            $model = array(
                'type' => get_bicycle_type(),
                'is_using' => get_common_boolean()
            );
            if (is_array($result) && !empty($result)) {
                foreach ($result as &$item) {
                    $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
                    foreach ($model as $k => $v) {
                        $item[$k] = isset($v[$item[$k]]) ? $v[$item[$k]] : '';
                    }
                }
            }

            $filter_types = array(
                'bicycle_sn' => '单车编号',
                'lock_sn' => '车锁编号',
                'region_name' => '区域',
            );
            $filter_type = $this->request->get('filter_type');
            if (empty($filter_type)) {
                reset($filter_types);
                $filter_type = key($filter_types);
            }

            // 使用中单车数
            $condition = array(
                'is_using' => 2
            );
            $using_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);
            // 故障单车数
            $condition = array(
                'is_using' => 1
            );
            $fault_bicycle = $this->sys_model_bicycle->getTotalBicycles($condition);

            $data_columns = array(
                '单车编号',
                '车锁编号',
                '单车类型',
                '区域',
                '是否使用中',
                '添加时间',
            );
            $this->assign('data_columns', $data_columns);
            $this->assign('data_rows', $result);
            $this->assign('total_bicycle', $total);
            $this->assign('using_bicycle', $using_bicycle);
            $this->assign('fault_bicycle', $fault_bicycle);
            $this->assign('model', $model);
            $this->assign('filter', $filter);
            $this->assign('filter_type', $filter_type);
            $this->assign('filter_types', $filter_types);
            $this->assign('action', $this->cur_url);
            $this->assign('import_action', $this->url->link('bicycle/bicycle/import'));
            $this->assign('add_action', $this->url->link('bicycle/bicycle/add'));
            $this->assign('batchadd_action', $this->url->link('bicycle/bicycle/batchadd'));
            $this->assign('lock_action', $this->url->link('lock/lock'));
            $this->assign('export_action', $this->url->link('bicycle/bicycle/export'));
            $this->assign('export_qrcode_action', $this->url->link('bicycle/bicycle/export_qrcode', http_build_query($filter) . '&operation=export'));

            if (isset($this->session->data['success'])) {
                $this->assign('success', $this->session->data['success']);
                unset($this->session->data['success']);
            }

            $this->response->setOutput($this->load->view('bicycle/bicycle_qrcode', $this->output));
        }
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('bicycle_sn', 'type', 'lock_sn', 'region_id'));
        $bicycle_id = $this->request->get('bicycle_id');
        if (isset($this->request->get['bicycle_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'bicycle_id' => $this->request->get['bicycle_id'],
                'cooperator_id' => $this->cooperator_id
            );
            $info = $this->sys_model_bicycle->getBicycleInfo($condition);
        }

        // 加载区域 model
        $this->load->library('sys_model/region', true);
        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        $limit = $order = '';
        $field = 'region.*';
        $join = array(
            'region' => 'region.region_id=cooperator_to_region.region_id'
        );
        $regionList = $this->sys_model_region->getCooperatorToRegionList($condition, $order, $limit, $field, $join);

        $this->assign('data', $info);
        $this->assign('regions', $regionList);
        $this->assign('types', get_bicycle_type());
        $this->assign('action', $this->cur_url . '&bicycle_id=' . $bicycle_id);
        $this->assign('return_action', $this->url->link('bicycle/bicycle'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('bicycle/bicycle_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('bicycle_sn', 'type', 'lock_sn', 'region_id'));

        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }
        return !$this->error;
    }

    /**
     * 验证删除条件
     */
    private function validateDelete() {
        return !$this->error;
    }

    /**
     * 验证批量添加条件
     */
    private function validateBatchaddForm() {
        return !$this->error;
    }

    private function buildBicycleSN() {
        $bicycle_sn = token(6, 'number');

        $rec = $this->checkBicycleSN($bicycle_sn);
        if (!$rec) {
            return self::buildBicycleSN();
        }
        return $bicycle_sn;
    }

    private function checkBicycleSN($bicycle_sn) {
        $condition = array(
            'bicycle_sn' => $bicycle_sn
        );
        $rec = $this->sys_model_bicycle->getTotalBicycles($condition);
        if ($rec) {
            return false;
        }
        return true;
    }

    /**
     * 换锁
     */
    public function exchangelock() {
        if(!empty($this->request->get['type'])&&$this->request->get['type']==='bt'){
            $type='bt';
        }elseif(!empty($this->request->get['type'])&&$this->request->get['type']==='normal'){
            $type='normal';
        }
        if(!empty($this->request->post['op'])&&$this->request->post['op']==='search_lock_sn'){//搜索锁，因为权限问题，不想整那么多权限，放在同一个控制器好了
            $lock_sn=$this->request->post['search_lock_sn'];
            $this->load->library('sys_model/lock', true);//加载锁模型
            $c=array();//查询条件
            $c['lock_sn']=array('like','%'.$lock_sn.'%');
            $lock_info=$this->sys_model_lock->getLockList($c);//查询符合条件的数据
            $lock_num=count($lock_info);
            $return_data=array(
                'lock_num'=>$lock_num,
                'lock_info'=>$lock_info
            );
            $this->response->showSuccessResult($return_data);
            die();
        }
        if(!empty($this->request->post['op'])&&$this->request->post['op']==='search_bicycle_sn'){//搜索单车的，理由同上
            $bicycle_sn=$this->request->post['search_bicycle_sn'];
            $c=array();//查询条件
            $c['bicycle_sn']=array('like','%'.$bicycle_sn.'%');
            $bicycle_info=$this->sys_model_bicycle->getBicycleList($c);//查询符合条件的数据
            $bicycle_num=count($bicycle_info);
            $return_data=array(
                'bicycle_num'=>$bicycle_num,
                'bicycle_info'=>$bicycle_info
            );
            $this->response->showSuccessResult($return_data);
            die();
        }
        if(!empty($this->request->post['op'])&&$this->request->post['op']==='exchange'){//提交保存改锁的，理由同上
            $lock_sn=empty($this->request->post['lock_sn'])?0:$this->request->post['lock_sn'];
            $lock_name=empty($this->request->post['lock_name'])?'':$this->request->post['lock_name'];
            $lock_type=empty($this->request->post['lock_type'])?0:$this->request->post['lock_type'];
            $lock_cooperator_id=empty($this->request->post['lock_cooperator_id'])?0:$this->request->post['lock_cooperator_id'];
            $lock_platform=empty($this->request->post['lock_platform'])?0:$this->request->post['lock_platform'];
            $lock_factory=empty($this->request->post['lock_factory'])?0:$this->request->post['lock_factory'];
            $lock_batch_number=empty($this->request->post['lock_batch_number'])?0:$this->request->post['lock_batch_number'];
            $bicycle_sn=empty($this->request->post['bicycle_sn'])?'':$this->request->post['bicycle_sn'];
            $bicycle_type=empty($this->request->post['bicycle_type'])?0:$this->request->post['bicycle_type'];
            $bicycle_region_id=empty($this->request->post['bicycle_region_id'])?0:$this->request->post['bicycle_region_id'];
            $bicycle_cooperator_id=empty($this->request->post['bicycle_cooperator_id'])?0:$this->request->post['bicycle_cooperator_id'];
            /*处理锁 开始 */
            if($type==='normal') {//普通换锁处理
                $c2=array(
                    'lock_sn'=>$lock_sn
                );
                $bicycle_info2= $this->sys_model_bicycle->getBicycleInfo($c2);
                if($bicycle_info2['bicycle_id'] > 0){
                    $return_data = array(
                        'info' => '此锁已用在单车'.$bicycle_info2['bicycle_sn'].'上，此次操作失败。',
                        'state' => -3,
                    );
                    $this->response->showSuccessResult($return_data);
                    die();
                }
                $this->load->library('sys_model/lock', true);//加载锁模型
                $c = array(
                    'lock_sn' => $lock_sn
                );
                $lock_info = $this->sys_model_lock->getLockInfo($c);//查询符合条件的数据
                $d = array(
                    'lock_sn' => $lock_sn,
                    'lock_name' => $lock_name,
                    'lock_type' => $lock_type,
                    'cooperator_id' => $lock_cooperator_id,
                    'lock_platform' => $lock_platform,
                    'lock_factory' => $lock_factory,
                    'batch_num' => $lock_batch_number,
                );
                if ($lock_info['lock_id'] > 0) {//数据库已有记录，则更新
                    $lock_id = $lock_info['lock_id'];
                    $c = array(
                        'lock_id' => $lock_id
                    );
                    $this->sys_model_lock->updateLock($c, $d);//按表id更新
                } else {//数据库无记录，插入
                    $lock_id = $this->sys_model_lock->addLock($d);//查询符合条件的数据
                }
                /*处理锁 结束*/

                /*处理单车 开始*/
                $old_lock_sn = null;//旧锁编号
                $c = array(
                    'bicycle_sn' => $bicycle_sn
                );
                $bicycle_info = $bicycle_info = $this->sys_model_bicycle->getBicycleInfo($c);
                $this->load->library('sys_model/region', true);//加载区域模型
                $c = array(
                    'region_id' => $bicycle_region_id
                );
                $region_info = $this->sys_model_region->getRegionInfo($c);//获取区域数据
                $d = array(
                    'bicycle_sn' => $bicycle_sn,
                    'type' => $bicycle_type,
                    'region_id' => $bicycle_region_id,
                    'cooperator_id' => $bicycle_cooperator_id,
                    'lock_id' => $lock_id,
                    'lock_sn' => $lock_sn,
                    'region_name' => $region_info['region_name']
                );
                if ($bicycle_info['bicycle_id'] > 0) {
                    $old_lock_sn = $bicycle_info['lock_sn'];
                    if($old_lock_sn==$lock_sn){
                        $return_data = array(
                            'info' => '相同锁编号，无需更换',
                            'state' => -2,
                        );
                        $this->response->showSuccessResult($return_data);
                        die();
                    }
                    $c = array(
                        'bicycle_id' => $bicycle_info['bicycle_id']
                    );
                    $this->sys_model_bicycle->updateBicycle($c, $d);
                } else {
                    $old_lock_sn = '新车，没旧锁';
                    $full_bicycle_sn = sprintf('%03d%02d%06d', $region_info['region_city_code'], $region_info['region_city_ranking'], $bicycle_sn);
                    $d2 = array(
                        'add_time' => time(),
                        'full_bicycle_sn' => $full_bicycle_sn//单车管理那边改地区也不会影响full_bicycle_sn属性，所以应该放在这里
                    );
                    $d = array_merge($d, $d2);
                    $this->sys_model_bicycle->addBicycle($d);

                }
                /*处理单车 结束*/
                $return_data = array(
                    'bicycle_sn' => $bicycle_sn,
                    'old_lock_sn' => $old_lock_sn,
                    'new_lock_sn' => $lock_sn
                );
                $this->response->showSuccessResult($return_data);
                die();
            }elseif($type==='bt'){
                $c = array(
                    'bicycle_sn' => $bicycle_sn
                );
                $bicycle_info = $bicycle_info = $this->sys_model_bicycle->getBicycleInfo($c);
                $this->load->library('sys_model/region', true);//加载区域模型
                $c = array(
                    'region_id' => $bicycle_region_id
                );
                $region_info = $this->sys_model_region->getRegionInfo($c);//获取区域数据

                $d = array(
                    'bicycle_sn' => $bicycle_sn,
                    'type' => $bicycle_type,
                    'region_id' => $bicycle_region_id,
                    'cooperator_id' => $bicycle_cooperator_id,
                    'lock_id' => 0,
                    'lock_sn' => '',
                    'region_name' => $region_info['region_name']
                );
                if ($bicycle_info['bicycle_id'] > 0) {
                    $c = array(
                        'bicycle_id' => $bicycle_info['bicycle_id']
                    );
                    $this->sys_model_bicycle->updateBicycle($c, $d);
                } else {
                    $full_bicycle_sn = sprintf('%03d%02d%06d', $region_info['region_city_code'], $region_info['region_city_ranking'], $bicycle_sn);
                    $d2 = array(
                        'add_time' => time(),
                        'full_bicycle_sn' => $full_bicycle_sn//单车管理那边改地区也不会影响full_bicycle_sn属性，所以应该放在这里
                    );
                    $d = array_merge($d, $d2);
                    $this->sys_model_bicycle->addBicycle($d);
                }
                $return_data = array(
                    'info' => '操作完成',
                    'state' => 1,
                );
                $this->response->showSuccessResult($return_data);
                die();
            }
        }
        $this->assign('title','换锁');
        $this->load->library('sys_model/cooperator', true);//加载合伙人
        $cooperators = $this->sys_model_cooperator->getCooperatorList();//获取合伙人数据
        $this->assign('cooperators', $cooperators);

        $search['search_lock'] = $this->url->link('bicycle/bicycle/searchlock');//搜索的链接，搜索锁
        $search['search_bicycle'] = $this->url->link('bicycle/bicycle/searchbicycle');//搜索的链接，搜索单车
        $this->assign('search', $search);

        $this->assign('return_action',$this->url->link('bicycle/bicycle'));//单车类型
        $this->assign('types', get_bicycle_type());//单车类型

        $this->load->library('sys_model/region', true);//加载区域模型
        $condition = array();
        $order = 'region_sort ASC';
        $regionList = $this->sys_model_region->getRegionList($condition, $order);//获取区域数据
        $this->assign('regions', $regionList);
        $this->assign('type', $type);
        $this->response->setOutput($this->load->view('bicycle/bicycle_exchangelock', $this->output));
    }
}
