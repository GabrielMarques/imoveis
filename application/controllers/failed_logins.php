<?php

/**
 * Failed_logins Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'controllers/crud_controller.php';

class Failed_logins extends Crud_Controller{

	public $crud_class = 'failed_login';

	public $crud_actions = array(
		'details' => true,
		'export' => true,
		'insert' => false,
		'update' => true,
		'delete' => true,
		'sort' => false,
	);

	public $crud_config = array();

	public function __construct(){
		parent::__construct();
	}

	public function index(){
		$this->_index();
	}

	public function get_manage_rows(){
		$this->_get_manage_rows();
	}

	public function details(){
		$this->_details();
	}

	public function export(){
		$this->_export();
	}

	public function insert(){
		$this->_insert();
	}

	public function update(){
		$this->_update();
	}

  public function delete(){
  	$this->_delete();
  }

	public function sort(){
		$this->_sort();
	}

}

/* End of file failed_logins.php */
/* Location: ./application/controllers/admin/failed_logins.php */