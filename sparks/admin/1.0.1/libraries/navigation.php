<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Navigation Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Navigation{

	private $ci;

	public $main_menu = array();
	public $menu_on = null;
	public $submenu_on = null;
	public $current_route = null;

	public function __construct(){
		$this->ci =& get_instance();
	}

	/**
	 * Initialize menu
	 */

	public function init($menu_tree, $user_group_id){
		// loop through menu items
		$user_groups = $this->ci->config->item('user_groups');

		foreach($menu_tree as $menu){
			// check item permissions
			$menu['user_group'] = isset($menu['user_group']) ? $menu['user_group'] : sizeof($user_groups);

			// only add items if user group id is less than or equal to menu user group
			if ($user_group_id <= $menu['user_group']){

				// default item settings
				$menu['type'] = isset($menu['type']) ? $menu['type'] : 'main';
				$menu['icon'] = isset($menu['icon']) ? $menu['icon'] : false;
				$menu['label'] = isset($menu['label']) ? $menu['label'] : $menu['route'];
				$menu['label'] = $this->ci->lang->line($menu['label']) ? $this->ci->lang->line($menu['label']) : $menu['label'];
				$menu['route'] = ADMIN_PREFIX . $menu['route'];

				$main_id = $this->add_main_menu($menu['label'], $menu['route'], $menu['user_group'], $menu['type'], $menu['icon']);

				// set submenu items
				if (isset($menu['submenu'])){
					foreach($menu['submenu'] as $submenu){

						// submenu item permissions
						$submenu['user_group'] = isset($submenu['user_group']) ? $submenu['user_group'] : sizeof($user_groups);

						if ($user_group_id <= $submenu['user_group']){
							$submenu['icon'] = isset($submenu['icon']) ? $submenu['icon'] : false;
							$submenu['label'] = isset($submenu['label']) ? $submenu['label'] : $submenu['route'];
							$submenu['label'] = $this->ci->lang->line($submenu['label']) ? $this->ci->lang->line($submenu['label']) : $submenu['label'];
							$submenu['route'] = ADMIN_PREFIX . $submenu['route'];

							$this->add_submenu($main_id, $submenu['label'], $submenu['route'], $submenu['user_group'], $submenu['icon']);
						}
					}
				}
			}
		}

		// set menu on
    $this->current_route = MULTI_APP === true ? $this->ci->uri->segment(2) : $this->ci->uri->segment(1);
    if ($this->current_route === false){
    	$this->current_route = MULTI_APP === true ? $this->ci->router->routes['default_controller_admin'] : $this->ci->router->routes['default_controller'];
    }

		foreach($this->main_menu as $main_key => $menu) {
			if (sizeof($menu->submenu) > 0){
				foreach ($menu->submenu as $sub_key => $submenu) {
					if ($submenu->route === ADMIN_PREFIX . $this->current_route){
						$this->menu_on = $main_key;
						$this->submenu_on = $sub_key;
						$this->current_route = MULTI_APP === true ? ADMIN_PREFIX . $this->current_route : $this->current_route;
						return true;
					}
				}
			}else{
				if ($menu->route === ADMIN_PREFIX . $this->current_route){
					$this->menu_on = $main_key;
					$this->submenu_on = null;
					$this->current_route = MULTI_APP === true ? ADMIN_PREFIX . $this->current_route : $this->current_route;
					return true;
				}
			}
		}
		return false;

	}

	/**
	 * Render menu
	 */

	public function render($type = 'main'){
		$final_menu = array();
		foreach ($this->main_menu as $main_key => $menu) {
			if ($type === $menu->type){
				if (sizeof($menu->submenu) == 0){
					//simple menu item
					$li_classes = array();
					$attributes = array();
					if ($this->menu_on === $main_key){
						$li_classes[] = 'active';
					}
					if ($menu->icon !== false){
						$label = '<i class="' . $menu->icon . ' icon-gray"></i>';
						$attributes = array('rel' => 'tooltip', 'title' => $menu->label, 'data-placement' => 'bottom');
					}else{
						$label = $menu->label;
					}
					$item = li_ext(anchor($menu->route, $label, $attributes), array('class' => implode(' ', $li_classes)));
				}else{
					//dropdown menu
					$sub_item = internal_anchor('#',  $menu->label . '<b class="caret"></b>', array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
					$submenu_list = array();
					$main_classes = array('dropdown');
					foreach ($menu->submenu as $sub_key => $submenu) {
						$sub_classes = array();
						if ($this->menu_on === $main_key && $this->submenu_on === $sub_key){
							$main_classes[] = 'active';
							$sub_classes[] = 'active';
						}
						$label = $submenu->icon !== false ? '<i class="' . $submenu->icon . '"></i>' : $submenu->label;
						$submenu_list[] = li_ext(anchor($submenu->route, $label), array('class' => implode(' ', $sub_classes)));
					}
					$sub_item .= ul_ext($submenu_list, array('class' => 'dropdown-menu'));
					$item = li_ext($sub_item, array('class' => implode(' ', $main_classes)));
				}

				$final_menu[] = $item;
			}
		}
		return implode('', $final_menu);
	}

	/**
	 * Add main item
	 */

	private function add_main_menu ($label, $route, $user_group, $type, $icon) {
		$k = sizeof($this->main_menu);
		$this->main_menu[$k]->label = $label;
		$this->main_menu[$k]->route = $route;
		$this->main_menu[$k]->user_group = $user_group;
		$this->main_menu[$k]->type = $type;
		$this->main_menu[$k]->icon = $icon;
		$this->main_menu[$k]->submenu = array();
		return $k;
	}

	/**
	 * Add sub item
	 */

	private function add_submenu ($parent, $label, $route, $user_group, $icon) {
		$k = sizeof($this->main_menu[$parent]->submenu);
		$this->main_menu[$parent]->submenu[$k]->label = $label;
		$this->main_menu[$parent]->submenu[$k]->route = $route;
		$this->main_menu[$parent]->submenu[$k]->user_group = $user_group;
		$this->main_menu[$parent]->submenu[$k]->icon = $icon;
		if ($this->main_menu[$parent]->route == null && $k == 0){
			$this->main_menu[$parent]->route = $route;
		}
	}

	/**
	 * Get page title
	 */

	public function get_page_title(){
		if (sizeof($this->main_menu[$this->menu_on]->submenu) > 0){
			return $this->main_menu[$this->menu_on]->submenu[$this->submenu_on]->label;
		}else{
			return $this->main_menu[$this->menu_on]->label;
		}
	}

	/**
	 * Get page breadcrumbs
	 */

	public function get_breadcrumbs(){
		$breadcrumbs = array(
			array('route' => $this->main_menu[$this->menu_on]->route, 'label' => $this->main_menu[$this->menu_on]->label),
		);
		if (sizeof($this->main_menu[$this->menu_on]->submenu) > 0){
			$breadcrumbs[] = array('route' => $this->main_menu[$this->menu_on]->submenu[$this->submenu_on]->route, 'label' => $this->main_menu[$this->menu_on]->submenu[$this->submenu_on]->label);
		}
		return $breadcrumbs;
	}

}

/* End of file navigation.php */
/* Location: ./application/libraries/navigation.php */