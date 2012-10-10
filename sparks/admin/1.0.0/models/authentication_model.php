<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authentication model Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Authentication_model extends CI_Model {

  public function __construct(){
	  parent::__construct();
  }

	/**
	 * Create user session
	 */

	private function create_user_session($user){
		$session_user = new stdClass();
		$session_user->id = $user->id;
		$session_user->fullname = $user->fullname;
		$session_user->email = $user->email;
		$session_user->user_group_id = $user->user_group_id;
		$session_user->can_write = isset($user->can_write) ? $user->can_write : 2;

	  // custom user fields
    $user_fields = $this->config->item('admin_user_fields');
		if (is_array($user_fields)){
			foreach($user_fields as $key => $value){
				$field = is_numeric($key) ? $value : $key . '_' . $value;
				$session_user->$field = isset($user->$field) ? $user->$field : null;
			}
		}

		$session_vars = array(
			'logged_in' => true,
			'user' => $session_user,
		);
		$this->session->set_userdata($session_vars);
	}

	/**
	 * Try to login
	 */

	public function login(){
		// main vars
		$errors = array();
		$can_login_now = true;
		$status_error = false;

		$pre_timeout_attempts = $this->config->item('pre_timeout_attempts') - 1;

		// get post vars
		$email = $this->input->post('email');
		$email = trim(substr($email, 0, 150));
		$password = $this->input->post('password');

		if (empty($email)){
			$errors['email'] = sprintf($this->lang->line('error_required'), $this->lang->line('email'));
			if (empty($password)){
				$errors['password'] = sprintf($this->lang->line('error_required'), $this->lang->line('password'));
			}
			return array('type' => 'validation', 'errors' => $errors);
		}

		if (empty($password)){
			$errors['password'] = sprintf($this->lang->line('error_required'), $this->lang->line('password'));
			$can_login_now = false;
		}

		// check failed logins
		$failed_login_class = $this->config->item('admin_failed_login_class') ? $this->config->item('admin_failed_login_class') : 'failed_login';
  	$failed_login = new $failed_login_class();
    $failed_login
    	->where('email', $email)
    	->limit(1)
    	->get();

		// check for previous failed login attempts
    if ($failed_login->exists() === true){
    	if ($failed_login->current_attempts > $pre_timeout_attempts){
		    // check timeout time
		    $timeout = pow($this->config->item('login_timeout'), $failed_login->current_attempts - $pre_timeout_attempts);
		    if (strtotime('-' . $timeout . ' seconds') <= strtotime($failed_login->last_attempt)){
		    	$can_login_now = false;
		    }
    	}
    }

    if ($can_login_now === true){
			// check if user exists and is active
			$user_class = $this->config->item('admin_user_class') ? $this->config->item('admin_user_class') : 'user';
	  	$user = new $user_class();

	    $user
	    	->where('email', $email)
	    	->limit(1);

	    // admin user restrictions
	    $restrictions = $this->config->item('admin_login_restrictions');
	    if (is_array($restrictions)){
				$user->where($restrictions['field'], $restrictions['value']);
	    }

	    // custom user fields
	    $user_fields = $this->config->item('admin_user_fields');
			if (is_array($user_fields)){
				foreach($user_fields as $key => $value){
					if (is_numeric($key) === false){
						$user->include_related($key, array($value));
					}
				}
			}
	    $user->get();

	    if ($user->exists() === true){
	    	// check password
	    	$this->load->library('phpass');
	    	$password = substr($password, 0, 72);
				if ($this->phpass->check_password($password, $user->password) === true){
					// check status status
					if ((int) $user->status !== 2){
						$status_error = true;
					}else{
		      	// save session and update user info
						$user->last_ip = $this->input->ip_address();
						$user->last_login = date('Y-m-d H:i:s');
						$success = $user->save();

						// save user session
						$this->create_user_session($user);

						// delete failed logins
						$this->clear_failed_logins($user->email);

						return true;
					}
	    	}
	    }
    }

    // save failed login attempt
    $failed_login->email = $email;
    $failed_login->current_attempts++;
    $failed_login->attempts++;
    $failed_login->last_attempt = date('Y-m-d H:i:s');
		$failed_login->last_ip = $this->input->ip_address();
		$failed_login->save();

		sleep(1);

		$next_timeout = $failed_login->current_attempts > $pre_timeout_attempts ? $next_timeout = pow($this->config->item('login_timeout'), $failed_login->current_attempts - $pre_timeout_attempts) : false;

		if ($next_timeout !== false){
			$message = sprintf($this->lang->line('error_login_attempts'), $next_timeout);
			return array('type' => 'timeout', 'message' => $message, 'errors' => $errors);
		}else if ($status_error === true){
			return array('type' => 'status', 'errors' => $errors);
		}else{
			return array('type' => 'login', 'errors' => $errors);
		}
	}

	/**
	 * Clear failed logins
	 */

	private function clear_failed_logins($email){
		// delete failed logins
		$failed_login_class = $this->config->item('admin_failed_login_class') ? $this->config->item('admin_failed_login_class') : 'failed_login';
  	$failed_login = new $failed_login_class();

		$failed_login
			->select('id')
			->where('email', $email)
			->get();

		if ($failed_login->exists() === true){
			$failed_login->where('id', $failed_login->id)->update('current_attempts', 0);
		}
	}

}

/* End of file authentication_model.php */
/* Location: ./application/models/authentication_model.php */