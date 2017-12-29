<?php

class ControllerCommonMenu extends Controller {

    public function index() {

        $this->load->library('sys_model/menu', true);

        $menuIds = $this->logic_admin->getParam('menu');

        $condition = array(
            'menu_id' => array('in', $menuIds)
        );
        $order = 'menu_level ASC, menu_sort ASC';
        $menu = $this->sys_model_menu->getMenuList($condition, $order);
        $menu = makeTree($menu, array('menu_id' => 0), 'menu_id', 'menu_parent_id');

        return $this->buildMenuTree($menu, true);
    }

    /**
     * 把树形结构的数组转换成<ul>-<li>的树结构标签
     * @param array $tree 树结构的数组数据，每个节点包含下标：menu_id，text_code，action，parameter，icon和children。注意：如果节点是页节点，那么
     * @param boolean $is_root 是否为根节点
     * @return string
     */
    private function buildMenuTree($tree, $is_root = false) {
        $ul = '';
        if( !empty($tree) ) {
            $ul .= $is_root ? '<ul class="sidebar-menu search-menu">' : '<ul class="treeview-menu search-menu">';
            foreach ($tree as $menu) {
                $ul .= '<li>';
                $ul .= !empty($menu['children']) ? '<a href="javascript:;">' : '';
                $ul .= (!empty($menu['menu_action']) && empty($menu['children'])) ? '<a href="' . $this->url->link($menu['menu_action'], '', true) . '">' : '';
                $ul .= !empty($menu['menu_icon']) ? '<i class="fa ' . $menu['menu_icon'] . ' fa-fw"></i> <span>' : '';
                $ul .= $this->language->get($menu['menu_name']);
                $ul .= !empty($menu['menu_icon']) ? '</span>' : '';
                $ul .= (!empty($menu['menu_action']) && empty($menu['children'])) ? '</a>' : '';
                $ul .= !empty($menu['children']) ? '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' : '';
                $ul .= !empty($menu['children']) ? $this->buildMenuTree( $menu['children'] ) : '';
                $ul .= '</li>';
            }
            $ul .= '</ul>';
        }
        return $ul;
    }

}
