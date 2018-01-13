<?php

class ControllerOperationPersonManagement extends Controller {

    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载fault Model
        $this->load->library('sys_model/fault', true);
    }

    /**
     * 维护列表
     */
    public function index() {
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }
        $rows = $this->config->get('config_limit_admin');
        $total=100;

        
        //捏造表格数据
        for ($i = 0; $i <= 10; $i++) {
            $a = array(
                'user_name'=>'运维员'.$i,
                'mobile'=>'1368888888'.$i,
                'times' => $i,
                'info_action'=>$this->url->link("operation/personManagement/edit")
            );
            $data[$i]=$a;
        }
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();


        $this->assign('pagination', $pagination);
        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $data);
        $this->assign('add_url', $this->url->link('operation/personManagement/add'));
        $this->assign('index_url', $this->url->link('operation/personManagement'));
        $this->assign('position_url', $this->url->link('operation/personManagement/position'));
        $this->assign('record_url', $this->url->link('operation/personManagement/record'));
        $this->response->setOutput($this->load->view('operation/personmanagement_list', $this->output));
    }
    /**
     * 维护记录明细
     */
    public function record() {
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }
        $rows = $this->config->get('config_limit_admin');
        $total=100;

        
        //捏造表格数据
        for ($i = 0; $i <= 10; $i++) {
            $a = array(
                'user_name'=>'运维员'.$i,
                'bike_sn'=>rand(10000,99999),
                'lock_sn'=>rand(10000,99999),
                'content'=>'运维事件',
                'create_time'=>date("Y-m-d H:i:s"),
            );
            $data[$i]=$a;
        }
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();


        $this->assign('pagination', $pagination);
        $data_columns = $this->getDataColumns_record();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $data);
        $this->assign('index_url', $this->url->link('operation/personManagement'));
        $this->assign('position_url', $this->url->link('operation/personManagement/position'));
        $this->assign('record_url', $this->url->link('operation/personManagement/record'));
        $this->response->setOutput($this->load->view('operation/personmanagement_record', $this->output));
    }
    /**
     * 人员定位
     */
    public function position() {
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }
        $rows = $this->config->get('config_limit_admin');
        $total=100;

        
        //捏造表格数据
        for ($i = 0; $i <= 10; $i++) {
            $a = array(
                'user_name'=>'运维员'.$i,
                'bike_sn'=>rand(10000,99999),
                'lock_sn'=>rand(10000,99999),
                'content'=>'运维事件',
                'create_time'=>date("Y-m-d H:i:s"),
            );
            $data[$i]=$a;
        }
        
        
        $this->assign('index_url', $this->url->link('operation/personManagement'));
        $this->assign('position_url', $this->url->link('operation/personManagement/position'));
        $this->assign('record_url', $this->url->link('operation/personManagement/record'));
        $this->response->setOutput($this->load->view('operation/personmanagement_position', $this->output));
    }
    
    
    
    
    /**
     * 修改维护
     */
    public function edit(){
        $this->assign('data_rows', $data);
         $this->assign('index_url', $this->url->link('operation/personManagement'));
        $this->assign('position_url', $this->url->link('operation/personManagement/position'));
        $this->assign('record_url', $this->url->link('operation/personManagement/record'));
        $this->response->setOutput($this->load->view('operation/personmanagement_edit', $this->output));
    }
    /**
     * 新增维护
     */
    public function add(){
        $this->assign('index_url', $this->url->link('operation/personManagement'));
        $this->assign('position_url', $this->url->link('operation/personManagement/position'));
        $this->assign('record_url', $this->url->link('operation/personManagement/record'));
        $this->response->setOutput($this->load->view('operation/personmanagement_edit', $this->output));
    }



    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {

        $this->setDataColumn('运维名称');
        $this->setDataColumn('电话');
        $this->setDataColumn('次数');
        $this->setDataColumn('操作');
        return $this->data_columns;
    }
    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns_record() {

        $this->setDataColumn('运维名称');
        $this->setDataColumn('车编号');
        $this->setDataColumn('锁编号');
        $this->setDataColumn('运维内容');
        $this->setDataColumn('时间');
        return $this->data_columns;
    }

}
