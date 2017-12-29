<?php

/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/12
 * Time: 17:03
 */
use Tool\ArrayUtil;

class ControllerStationNotification extends Controller {

    public function index() {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }


        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $total = 100;

        for ($i = 0; $i <= 10; $i++) {
            $a = array(
                'type' => '调度',
                'operation_from' => '操作员a',
                'operation_to' => '运维员a',
                'send_status' => '是',
                'create_time' => date("Y-m-d H:i"),
                'content' => '把编号123的单车搬去***',
                'operation_status' => '待执行',
                'edit_url'=>$this->url->link('station/notification/edit')
            );
            $result[$i] = $a;
        }
        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);

        $this->load->library('sys_model/region');
        $regionList = $this->sys_model_region->getRegionList();
        $this->assign('regionList', $regionList);



        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('add_url', $this->url->link('station/notification/add'));
        $this->assign('nofification_url', $this->url->link('station/notification'));
        $this->assign('threshold_url', $this->url->link('station/notification/threshold'));
        $this->assign('unused_url', $this->url->link('station/notification/unused'));
        $this->response->setOutput($this->load->view('station/notification_list', $this->output));
    }

    protected function getDataColumns() {

        $this->setDataColumn('通知类型');
        $this->setDataColumn('指派员');
        $this->setDataColumn('指派到运维人员');
        $this->setDataColumn('是否发送');
        $this->setDataColumn('通知时间');
        $this->setDataColumn('执行状态');
        $this->setDataColumn('任务');

        return $this->data_columns;
    }
    /**
     * 添加通知
     */
    public function add() {
        
        $this->assign('title', '添加通知');
        $this->response->setOutput($this->load->view('station/notification_add', $this->output));
    }

    /**
     * 编辑修改通知
     */
    public function edit() {
        $this->assign('title', '编辑通知');
        $this->response->setOutput($this->load->view('station/notification_edit', $this->output));
    }
    
    /**
     * 阀值列表
     */
    public function threshold() {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }


        $rows = $this->config->get('config_limit_admin');
        $total = 100;

        for ($i = 0; $i <= 10; $i++) {
            $a = array(
                'station_name' => '站点名称',
                'area_name' => '地区名称',
                'city_name' => '城市名称',
                'type' => '高阀值（30）',
                'station_threshold'=>'16/9',
                'operation_name' => '未指派',
                'edit_url'=>$this->url->link('station/notification/edit')
            );
            $result[$i] = $a;
        }
        $data_columns = [['text'=>'站点'],['text'=>'地区'],['text'=>'城市'],['text'=>'类型'],['text'=>'站点高/低阀值'],['text'=>'指派运维员']];
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);

        $this->load->library('sys_model/region');
        $regionList = $this->sys_model_region->getRegionList();
        $this->assign('regionList', $regionList);



        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('add_url', $this->url->link('station/notification/add'));
        $this->assign('nofification_url', $this->url->link('station/notification'));
        $this->assign('threshold_url', $this->url->link('station/notification/threshold'));
        $this->assign('unused_url', $this->url->link('station/notification/unused'));
        $this->response->setOutput($this->load->view('station/threshold_list', $this->output));
    }
    
    /**
     * 久置未用列表
     */
    public function unused() {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }


        $rows = $this->config->get('config_limit_admin');
        $total = 100;

        for ($i = 0; $i <= 10; $i++) {
            $a = array(
                'station_name' => '站点名称',
                'area_name' => '地区名称',
                'city_name' => '城市名称',
                'unused_day' => '未用天数（10）',
                'station_unused'=>'5',
                'operation_name' => '未指派',
                'edit_url'=>$this->url->link('station/notification/edit')
            );
            $result[$i] = $a;
        }
        $data_columns = [['text'=>'站点'],['text'=>'地区'],['text'=>'城市'],['text'=>'未用天数'],['text'=>'站点设置未用天数'],['text'=>'指派运维员']];
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);

        $this->load->library('sys_model/region');
        $regionList = $this->sys_model_region->getRegionList();
        $this->assign('regionList', $regionList);



        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('add_url', $this->url->link('station/notification/add'));
        $this->assign('nofification_url', $this->url->link('station/notification'));
        $this->assign('threshold_url', $this->url->link('station/notification/threshold'));
        $this->assign('unused_url', $this->url->link('station/notification/unused'));
        $this->response->setOutput($this->load->view('station/unused_list', $this->output));
    }

}
