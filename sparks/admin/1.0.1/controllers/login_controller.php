<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Login Controller Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'controllers/admin_controller.php';

class Login_Controller extends Admin_Controller {

  public function __construct(){
    parent::__construct();
  }

	/**
	 * Login
	 */

	public function _login(){
  	$this->load->model('authentication_model');

    $this->_logout();

		// validate form
		$errors = false;
		if ($this->input->post('process')){
			// form submitted
    	$success = $this->authentication_model->login();
      if ($success === true){
		    redirect(ADMIN_PREFIX);
      }else {
      	$errors = $success['errors'];
      	switch($success['type']){
	      	case 'validation':
		      	$this->alerts->alert_now('error_form_default', 'error');
	      		break;
	      	case 'timeout':
		      	$this->alerts->alert_now($success['message'], 'error');
	      		break;
	      	case 'status':
		      	$this->alerts->alert_now('error_status', 'error');
	      		break;
	      	case 'login':
		      	$this->alerts->alert_now('error_login', 'error');
	      		break;
      	}
      }
    }

		$fields = array(
			'email' => array(
				'type' => 'text',
				'class' => 'input-block-level',
			),
			'password' => array(
				'type' => 'password',
				'class' => 'input-block-level',
			),
		);

    // load build form library
		$params = array(
			'fields' => $fields,
			'errors' => $errors,
			'process' => $this->input->post('process'),
		);
		$this->build_form->config($params);

    $data = array();

    $css_groups = array('bootstrap_login');
    $local_scripts = array('login');
    $this->load->view('admin/login_tpl', $this->_get_admin_view_data($data, array('override_local' => true, 'local_scripts' => $local_scripts, 'override_css' => true, 'css_groups' => $css_groups)));
	}

	/**
	 * Logout
	 */

	public function _logout(){
		$this->session->sess_destroy();
		$this->session->sess_create();
	}

}

/* End of file login_controller.php */
/* Location: ./application/controllers/login_controller.php */