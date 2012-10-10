<?php

/**
 * Users Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'controllers/crud_controller.php';

class Users extends Crud_Controller{

	public $crud_class = 'user';

	public $crud_actions = array(
		'details' => true,
		'export' => true,
		'insert' => true,
		'update' => true,
		'delete' => true,
	);

	public $crud_config = array();

	public function __construct(){
		parent::__construct();

		// filters
		//$this->filters->load('publisher_group_id', 'dropdown', null, array('values_array' => array('publisher_group')));
		//$this->filters->load('user_group_id', 'dropdown', 'user_profile', array('values_array' => 'user_groups'));

		// actions
		$this->actions->set_btn('insert', 'icon', 'icon-user');
		$this->actions->set_btn('update', 'when_not', array('field' => 'id', 'value' => 1));

		if ((int) $this->user->can_write === 2){
			$this->actions->load('row', 'reset_password', null, array('modal' => true, 'when_not' => array('field' => 'id', 'value' => 1)));
		}
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
		$this->_is_super_user();
    $this->_update();
  }

  public function delete(){
  	$this->_is_super_user(true);
    $this->_delete();
  }

  public function reset_password($id){
  	$this->_is_super_user();
		$this->_can_write();

		$this->load->model('account_model');

    $success = $this->account_model->reset_password($id);
    if ($success === false){
    	$this->alerts->alert_error();
    }else{
    	$this->alerts->alert($success, 'success');
    }

    $this->session->set_flashdata('keep_table_params', true);
    redirect($this->navigation->current_route);
  }

}

/* End of file users.php */
/* Location: ./application/controllers/admin/users.php */