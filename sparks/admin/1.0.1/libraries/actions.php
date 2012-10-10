<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Actions Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Actions{

	private $btns = array();
	public $checkboxes_on = array();
	public $allow;

	public function __construct() {
		$this->ci =& get_instance();
		$this->allow = new stdClass();
  }

	/**
	 * Load action
	 */

	public function load($type, $route, $label = null, $params = null) {
		if ($label === null){
			$label = $this->ci->lang->line($route);
		}else{
			$label = $this->ci->lang->line($label) ? $this->ci->lang->line($label) : $label;
		}

		$this->btns[$route]->type = $type;
		$this->btns[$route]->label = $label;
		$this->btns[$route]->id = null;
		$this->btns[$route]->class = null;
		$this->btns[$route]->icon = null;
		$this->btns[$route]->modal = false;
		$this->btns[$route]->modal_body = false;
		$this->btns[$route]->modal_footer = false;
		$this->btns[$route]->modal_form = false;
		$this->btns[$route]->tooltip = false;
		$this->btns[$route]->html = false;
		$this->btns[$route]->when = false;
		$this->btns[$route]->when_not = false;
		$this->btns[$route]->action = $route;

		if ($params !== null){
			foreach($params as $key => $value){
				$this->btns[$route]->$key = $value;
			}
		}

		if ($type === 'table'){
			$this->checkboxes_on = true;
		}

		// set allow
		$this->allow->$route = true;
	}

	/**
	 * Unload action
	 */

	public function unload($route) {
		unset($this->btns[$route]);
		$this->allow->$route = false;
	}

	/**
	 * Check if action is allowed
	 */

	public function allowed($route) {
		return isset($this->allow->$route) && $this->allow->$route === true ? true : false;
	}

	/**
	 * Override btn settings
	 */

	public function set_btn($route, $key, $value){
		if (isset($this->btns[$route])){
			$this->btns[$route]->$key = $value;
		}
	}

	/**
	 * Get actions
	 */

	public function get_actions($types) {
		$types = is_array($types) ? $types : array($types);
		$btns = array();

		foreach($this->btns as $route => $btn){
			if (in_array($btn->type, $types)){
				$btns[$route] = $btn;
			}
		}
		if (sizeof($btns) > 0){
			return $btns;
		}else{
			return false;
		}
	}

	/**
	 * Get main action btns
	 */

	public function render_main_btns() {
		$btns = $this->get_actions('main');
		$btns_str = '';
		if ($btns !== false){
			foreach($btns as $route => $btn){
				$btns_str .= $this->render_btn($route, array('as_btn' => true));
			}
		}
		return $btns_str;
	}

	/**
	 * Get direct action btns
	 */

	public function render_direct_btns() {
		$btns = $this->get_actions('direct');
		$btns_str = '';
		$options = array();
		if ($btns !== false){
			if (sizeof($btns) > 1){
				foreach($btns as $route => $btn){
					$options[] = $this->render_btn($route);
				}
				$btns_str .= render_btn_dropdown('<i class="icon-wrench icon-white"></i> ' . $this->ci->lang->line('direct_action'), $options, array('class' => 'btn-inverse', 'right' => true));
			}else{
				$route = key($btns);
				$class = $btns[$route]->class !== null ? $btns[$route]->class : 'btn-inverse';
				$btns_str .= $this->render_btn($route, array('as_btn' => true, 'class' => $class));
			}
		}
		return $btns_str;
	}

	/**
	 * Get table action btns
	 */

	public function render_table_btns() {
		$btns = $this->get_actions('table');
		$btns_str = '';
		$options = array();
		if ($btns !== false){
			if (sizeof($btns) > 1){
				foreach($btns as $route => $btn){
					$options[] = $this->render_btn($route);
				}
				$btns_str .= render_btn_dropdown('<i class="icon-th-list icon-white"></i> ' . $this->ci->lang->line('table_action'), $options, array('class' => 'btn-inverse'));
			}else{
				$route = key($btns);
				$class = $btns[$route]->class !== null ? $btns[$route]->class : 'btn-inverse';
				$btns_str .= $this->render_btn($route, array('as_btn' => true, 'class' => $class));
			}
		}
		return $btns_str;
	}

	/**
	 * Get action btn
	 */

	public function render_btn($route, $params = array()){
		// params
	  $as_btn = isset($params['as_btn']) ? $params['as_btn'] : false;
	  $row = isset($params['row']) ? $params['row'] : false;
	  $class = isset($params['class']) ? $params['class'] : false;

		if (isset($this->btns[$route]) === false){
			return false;
		}
		$btn = $this->btns[$route];

		if ($btn->html !== false){
			return $btn->html;
		}

		$attributes = array(
			'id' => 'action-' . $route,
		);
		$classes = array();

		// if row action
		if ($row !== false){
			if (isset($row[$btn->when['field']]) && $row[$btn->when['field']] != $btn->when['value']){
				return false;
			}
			if (isset($row[$btn->when_not['field']]) && $row[$btn->when_not['field']] == $btn->when_not['value']){
				return false;
			}
		}

		// if button
		if ($as_btn === true){
			$classes[] = 'btn';
			if ($btn->class !== null || $class !== false){
				if ($btn->icon !== null){
					$btn->icon .= ' icon-white';
				}
				$classes[] = $btn->class !== null ? $btn->class : $class;
			}
		}

		// if modal
		if ($btn->modal === true){
			$attributes['data-target'] = '#modal-' . $route;
			if ($btn->type !== 'table'){
				$classes[] = 'action-modal';
			}
		}

		// if table btn
		if ($btn->type === 'table'){
			$classes[] = 'action-table';
		}

		// set tooltip
		if ($btn->tooltip !== false){
			$attributes['rel'] = 'tooltip';
			$attributes['title'] = $btn->tooltip;
			$attributes['data-placement'] = 'bottom';
		}

		$attributes['class'] = implode(' ', $classes);

		// set label
		$icon = $btn->icon !== null ? '<i class="' . $btn->icon . '"></i> ' : '';
		$label = $icon . $btn->label;

		// if row action
		$url = strpos($btn->action, 'http') === false ? $this->ci->navigation->current_route . '/' . $btn->action : $btn->action;
		if ($row === false){
			return anchor($url, $label, $attributes);
		}else{
			return anchor($url . '/' . $row['id'], $label, $attributes);
		}
	}

	/**
	 * Get action modals
	 */

	public function render_modals($type = false) {
		$output = '';

		$modals = array();
		foreach($this->btns as $route => $btn){
			if ($btn->modal === true && ($type === false || $btn->type === $type)){

				$output .= '<div id="modal-' . $route . '" class="modal hide fade">';
			  $output .= form_open('', array('class' => 'form-vertical modal-form'));

				$output .= '<div class="modal-header"><a href="#" class="close" data-dismiss="modal">&times;</a>';
				$output .= heading($btn->label, 3);
			  $output .= '</div>';
			  $output .= '<div class="modal-body">';

			  // body
			  if ($btn->modal_body !== false){
			  	$output .= $btn->modal_body;
			  }else{
			  	$output .= '<p>' . $this->ci->lang->line('confirm_general') . '</p>';
			  }

			  $output .= '</div>';
			  $output .= '<div class="modal-footer">';
			  if (isset($btn->modal_footer) && $btn->modal_footer !== false){
				  $output .= '<p class="pull-left">' . $btn->modal_footer . '</p>';
			  }

				// output confirm btns
				switch ($btn->type){
					case 'table':
						$output .= anchor($this->ci->navigation->current_route . '/' . $route, $this->ci->lang->line('confirm'), array('data-action' => $route, 'class' => 'btn btn-primary action-table confirm-btn', 'data-loading-text' => $this->ci->lang->line('wait') . '...', 'auto-complete' => 'off'));
						break;
					default:
						$output .= form_submit(array('value' => $this->ci->lang->line('confirm'), 'data-action' => $route, 'class' => 'btn btn-primary confirm-btn', 'data-loading-text' => $this->ci->lang->line('wait') . '...', 'auto-complete' => 'off'));
						break;
				}

				// cancel
				$output .= internal_anchor('#', $this->ci->lang->line('cancel'), array('class' => 'btn cancel', 'data-dismiss' => 'modal'));

			  $output .= '</div>';

			  $output .= form_close();
				$output .= '</div>';

			}
		}

		return $output;
	}

}

/* End of file actions.php */
/* Location: ./application/libraries/actions.php */