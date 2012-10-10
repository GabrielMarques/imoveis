<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Account model Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Account_model extends CI_Model {

  public function __construct(){
	  parent::__construct();
  }

	/**
	 * Change password
	 */

	public function change_password(){
		$errors = array();

		$user_class = $this->config->item('admin_user_class') ? $this->config->item('admin_user_class') : 'user';
  	$user = new $user_class();
		$user
			->where('id', $this->user->id)
			->limit(1)
			->get();

		if ($user->exists() === false){
			return false;
		}

		// check current password
		$current_password = post_to_var('current_password');

		$this->load->library('phpass');
		if ($this->phpass->check_password($current_password, $user->password) === false){
			return array('current_password' => $this->lang->line('error_current_password'));
		}

		// post vars
		$user->add_rules('confirm_password', array('required'));

		$fields = array('password', 'confirm_password');
		$user->data_to_object($fields, 'post', array('force' => true));

    $success = $user->save();
    //die($user->error->string);
		if ($success === true){
			return true;
		}else{
			// get errors
			$errors = $user->errors_to_array($fields);
			return $errors;
		}
	}

	/**
	 * Reset password
	 */

	public function reset_password($id){
    // get user
		$user_class = $this->config->item('admin_user_class') ? $this->config->item('admin_user_class') : 'user';
  	$user = new $user_class();

		$user
			->where('id', $id)
			->limit(1)
			->get();

		if ($user->exists() === false){
			return false;
		}

		$new_password = random_string('alnum', 16);
    $user->password = $new_password;
    $success = $user->save();
    if ($success === false){
    	return false;
    }else{
    	return sprintf($this->lang->line('success_reset_password'), $new_password);
    }
	}

}

/* End of file account_model.php */
/* Location: ./application/models/account_model.php */