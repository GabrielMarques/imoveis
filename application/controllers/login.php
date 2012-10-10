<?php

/**
 * Login Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'controllers/login_controller.php';

class Login extends Login_Controller{

  public function __construct(){
    parent::__construct();
  }

  public function index(){
  	$this->_login();
  }

}

/* End of file login.php */
/* Location: ./application/controllers/login.php */