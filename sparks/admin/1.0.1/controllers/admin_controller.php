<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Admin_Controller Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Admin_Controller extends MY_Controller {

	public $user = false;

  public function __construct(){
    parent::__construct();
    $this->load->spark('admin/1.0.0');

  	// check for multi app
		define('MULTI_APP', $this->config->item('multi_app'));
		define('ADMIN_PREFIX', $this->config->item('admin_prefix'));

		// set config labels
		set_config_labels('bool_options');

		// date format
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
			$this->config->set_item('default_date_format', '%#d %b, %Y');
			$this->config->set_item('default_datetime_format', '%#d %b, %Y %H:%M');
			$this->config->set_item('default_day_month_format', '%#d %b');
		}

    // set user
   	$this->user = $this->session->userdata('user');

    // profiler
    if ($this->input->is_ajax_request() === false){
	    $this->output->enable_profiler($this->config->item('enable_profiler'));
    }
  }

	/**
	 * Default admin view
	 */

  public function _get_admin_view_data($data = array(), $params = array()){
  	//$this->carabiner->empty_cache();

		// css
		$css_groups = $this->config->item('css_groups');
		$groups = array(
			'bootstrap',
		);
		if (isset($params['css_groups']) && isset($params['override_css']) && $params['override_css'] === true){
			$groups = $params['css_groups'];
		}else if (isset($params['css_groups'])){
			$groups = array_merge($groups, $params['css_groups']);
		}

  	foreach($groups as $group){
  		$this->carabiner->css($css_groups[$group]);
		}

		// local js
		$local_scripts = $this->config->item('local_scripts');
		$locals = array(
			'cache',
			'no_cache',
		);
		if (isset($params['local_scripts']) && isset($params['override_local']) && $params['override_local'] === true){
			$locals = $params['local_scripts'];
		}else if (isset($params['local_scripts'])){
			$locals = array_merge($locals, $params['local_scripts']);
		}

		foreach($locals as $local){
			$this->carabiner->group($local, array('js' => $local_scripts[$local]));
		}

		// external js
		$external_scripts = $this->config->item('external_scripts');
		$externals = array();
    if (isset($params['external_scripts'])){
			$externals = array_merge($externals, $params['external_scripts']);
		}

		foreach($externals as &$external){
			$external = $external_scripts[$external];
		}

		$jquery = ENVIRONMENT === 'development' ? $this->config->item('jquery_dev') : $this->config->item('jquery_production');

  	$default_data = array(
      'title' => isset($this->navigation) ? $this->navigation->get_page_title() : '',
      'breadcrumbs' => isset($this->navigation) ? $this->navigation->get_breadcrumbs() : '',
	  	'jquery' => $jquery,
  		'local_scripts' => $locals,
  		'external_scripts' => $externals,
  	);
    return array_merge($data, $default_data);
  }

	/**
	 * Restrict access
	 */

	public function _restrict_access($force = false){
	  if ($this->_is_logged_in() === false || $force === true){
    	if ($this->input->is_ajax_request() === false){
				$loc = MULTI_APP === true ? ADMIN_PREFIX . 'login' : 'login';
				redirect($loc);
    	}else{
				$this->output->set_status_header(401);
	    	exit;
    	}
    }
	}

	/**
	 * Is user logged in
	 */

	public function _is_logged_in(){
		return $this->session->userdata('logged_in') === true ? true : false;
	}

  /**
	 * Is super user
	 */

	public function _is_super_user($rows = false){
  	$restrict = false;
		if ($rows === false){
			$id = MULTI_APP ? $this->uri->segment(4) : $this->uri->segment(3);
	    if ($id == 1){
	    	$restrict = true;
	    }
  	}else{
      $rows = $this->input->post('rows');
		  if (is_array($rows)){
		  	foreach($rows as $id){
		    	if ($id == 1){
		    		$restrict = true;
		    		break;
		    	}
		  	}
		  }
  	}

		if ($restrict === true){
	  	$this->alerts->alert('error_super_user');
			redirect($this->navigation->current_route);
		}
	}

	/**
	 * Check if is admin user
	 */

	public function _is_admin_user($params = array()){
		$restrict = isset($params['restrict']) ? $params['restrict'] : false;
		$password = isset($params['password']) ? $params['password'] : null;
		$can_write = isset($params['can_write']) ? $params['can_write'] : false;

		$error = false;
		if ((int) $this->user->user_group_id !== 1){
			$error = 'error_admin_user';
		}else if ($can_write !== false && (int) $this->user->can_write !== 2){
			$error = 'error_admin_user';
		}else if ($password !== null && do_hash($password . $this->config->item('password_encryption_key')) !== $this->config->item('master_password')){
			$error = 'error_admin_user_password';
		}

		if ($error !== false && $restrict === true){
			$this->alerts->alert($error, 'error');
			redirect($this->navigation->current_route);
		}else if ($error !== false){
			return $error;
		}

		return true;
	}

	/**
	 * Check if user can write
	 */

	public function _can_write(){
		if ((int) $this->user->can_write !== 2){
			$this->alerts->alert('error_not_allowed', 'error');
			redirect($this->navigation->current_route);
		}
	}

	/**
	 * Rows check
	 */

	public function _rows_check($ids, $limit = false){
		if ($ids === false || is_array($ids) === false){
			$this->alerts->alert_rows_not_found();
			redirect($this->navigation->current_route);
		}

		if ($limit !== false && sizeof($ids) > $limit){
			$this->alerts->alert(sprintf($this->lang->line('error_more_than'), $limit), 'error');
			redirect($this->navigation->current_route);
		}
	}

	/**
	 * Get admin emails
	 */

	public function _get_admin_emails(){
		$user_class = $this->config->item('admin_user_class') ? $this->config->item('admin_user_class') : 'user';
  	$users = new $user_class();
		$users
			->select('id, email')
			->where('user_group_id', 1)
			->get();

		return $users->all_to_single_array('email');
	}



}

/* End of file admin_controller.php */
/* Location: ./application/controllers/admin_controller.php */