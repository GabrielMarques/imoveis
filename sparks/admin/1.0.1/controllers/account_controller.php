<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Account Controller Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'controllers/admin_controller.php';

class Account_Controller extends Admin_Controller {

  public function __construct(){
    parent::__construct();

    // set nav options
		$this->load->library('navigation');
    $route_found = $this->navigation->init($this->config->item('admin_menu'), $this->user->user_group_id);

		// restrict access
		if ($route_found === false){
			$this->_restrict_access(true);
		}else{
			$this->_restrict_access();
		}

		// load account model
		$this->load->model('account_model');
  }

	/**
	 * Account
	 */

	public function _account(){
		// validate form
		$errors = false;
		if ($this->input->post('process')){
			// form submitted
    	$success = $this->account_model->change_password();
      if ($success === true){
		    $this->alerts->alert_now('success_account_password', 'success');
      }else{
      	$errors = $success;
      	$this->alerts->alert_now('error_form_default', 'error');
      }
    }

		// fields
  	$fields = array(
			'current_password' => array(
				'type' => 'password',
				'required' => true,
				'help' => 'current_password_help',
			),
			'password' => array(
				'label' => 'new_password',
				'type' => 'password',
				'required' => true,
				'help' => 'password_help',
				'score' => true,
			),
			'confirm_password' => array(
				'type' => 'password',
				'required' => true,
				'help' => 'confirm_password_help',
			),
		);

    // load build form library
		$params = array(
			'fields' => $fields,
			'errors' => $errors,
		);
		$this->build_form->config($params);

		// output
		$data = array(
      'content' => 'admin/content/account',
			'header' => $this->lang->line('change_password'),
		);

    $local_scripts = array('account');
    $this->load->view('admin/admin_tpl', $this->_get_admin_view_data($data, array('local_scripts' => $local_scripts)));
	}

}

/* End of file account_controller.php */
/* Location: ./application/controllers/account_controller.php */