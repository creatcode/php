<?php

class ControllerOperationSystemMaintenance extends Controller {

    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载fault Model
        $this->load->library('sys_model/fault', true);
        $this->assign('lang',$this->language->all());
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
        $filter=array();
        $rows = $this->config->get('config_limit_admin');
        $total=100;

        
        //捏造表格数据
        for ($i = 0; $i <= 10; $i++) {
            $a = array(

                'content' => '这是反馈内容',

                'create_time' => date("Y-m-d H:i:s"),
                'info_action'=>$this->url->link("operation/systemMaintenance/edit")
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
        $this->assign('add_url', $this->url->link('operation/systemMaintenance/add'));
        $this->response->setOutput($this->load->view('operation/systemmaintenance_list', $this->output));
    }
    
    /**
     * 修改维护
     */
    public function edit(){
        $data=array();
        $this->assign('data_rows', $data);
        $this->response->setOutput($this->load->view('operation/systemmaintenance_edit', $this->output));
    }
    /**
     * 新增维护
     */
    public function add(){
        $data=array();
        $this->assign('data_rows', $data);
        $this->response->setOutput($this->load->view('operation/systemmaintenance_add', $this->output));
    }



    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {

        $this->setDataColumn($this->language->get('t2'));
        $this->setDataColumn($this->language->get('t3'));
        $this->setDataColumn($this->language->get('t4'));
        return $this->data_columns;
    }

}
