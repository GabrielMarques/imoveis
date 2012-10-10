<?php

/**
 * Account Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'controllers/account_controller.php';

class Account extends Account_Controller{

	public function __construct(){
		parent::__construct();
	}

	public function index(){
		$this->_account();
	}

}

/* End of file account.php */
/* Location: ./application/controllers/admin/account.php */