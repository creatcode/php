<?php
abstract class Controller {
	protected $output;
    protected $registry;
    protected $data_columns;
    protected $page_size = 10;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	public function setDataColumn($value) {
        $this->data_columns[] = array('text' => $value);
    }

	public function assign($key, $value = null) {
        if (is_object($key)) {
            $key = get_object_vars($key);
        }
        if (is_array($key)) {
            $this->output = array_merge((array) $this->output, $key);
        } else {
            $this->output[$key] = $value;
        }
    }

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}


    /**
     * 返回limit 给查询用
     * @param $total
     * @return string
     */
    protected function getPagination($total){
        $page = $this->request->get("page");
        $page = intval($page);
        if(!$page){
            $page = 1;
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $this->page_size;
        $pagination->url = $this->cur_url . '&amp;page={page}';
        $pagination = $pagination->render();

        $offset = ($page-1) * $this->page_size;
        $limit = "{$offset},{$this->page_size}";

        $this->assign('pagination',$pagination);

        return $limit;
    }
}