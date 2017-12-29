<?php
class Pagination {
	public $total = 0;
	public $page = 1;
	public $page_size = 20;
	public $num_links = 8;
	public $url = '';
	public $text_first = '|&lt';
	public $text_last = '&gt;|';
	public $text_next = '&gt;';
	public $text_prev = '&lt;';
	private $_limit;

    public function limit() {
        return $this->_limit;
    }

	public function render() {
		$total = $this->total;
		
		if ($this->page < 1) {
			$page = 1;
		} else {
			$page = $this->page;
		}
		
		if (!(int) $this->page_size) {
			$limit = 10;
		} else {
			$limit = $this->page_size;
		}
		
		$num_links = $this->num_links;
		$num_pages = ceil($total / $limit);
		
		$this->url = str_replace('%7Bpage%7D', '{page}', $this->url);
        $output ='&nbsp&nbsp&nbsp<div style="float: left;display: inline-block;padding-left: 0;margin: 20px 0;border-radius: 4px;line-height: 34px;">共'.$this->total.'条</div>';
		$output .= '<ul class="pagination">';
		if ($page > 1) {
			$output .= '<li><a href="' . str_replace('&amp;page={page}', '', $this->url) . '">' . $this->text_first . '</a></li>';
			if($page - 1 === 1){
				$output .= '<li><a href="' . str_replace('&amp;page={page}', '', $this->url) . '">' . $this->text_prev . '</a></li>';
			} else {
				$output .= '<li><a href="' . str_replace('{page}', $page - 1, $this->url) . '">' . $this->text_prev . '</a></li>';
			}
		}
		
		if ($num_pages > 1) {
			if ($num_pages <= $num_links) {
				$start = 1;
				$end = $num_pages;
			} else {
				$start = $page - floor($num_links / 2);
				$end = $page + floor($num_links / 2);

				if ($start < 1) {
					$end += abs($start) + 1;
					$start = 1;
				}

				if ($end > $num_pages) {
					$start -= ($end - $num_pages);
					$end = $num_pages;
				}
			}

            $this->_limit = $start . ', ' . $this->page_size;

			for ($i = $start; $i <= $end; $i++) {
				if ($page == $i) {
					$output .= '<li class="active"><span>' . $i . '</span></li>';
				} else {if($i === 1){
					$output .= '<li><a href="' . str_replace('&amp;page={page}', '', $this->url) . '">' . $i . '</a></li>';
				} else {
					$output .= '<li><a href="' . str_replace('{page}', $i, $this->url) . '">' . $i . '</a></li>';
				}
				}
			}
		} else {
            $this->_limit = '0, ' . $this->page_size;
        }
		
		if ($page < $num_pages) {
			$output .= '<li><a href="' . str_replace('{page}', $page + 1, $this->url) . '">' . $this->text_next . '</a></li>';
			$output .= '<li><a href="' . str_replace('{page}', $num_pages, $this->url) . '">' . $this->text_last . '</a></li>';
		}
		
		$output .= '</ul>';
		
		if ($num_pages > 1) {
			return $output;
		} else {
			return '';
		}
	}
}