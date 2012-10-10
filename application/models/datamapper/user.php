<?php

/**
 * User DataMapper Model
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class User extends DataMapper {

	public $model = 'user';
	public $table = 'users';
	public $default_order_by = array('last_login' => 'desc');
	public $details_field = 'email';
	public $id_visible = false;
	public $save_hook_on = true;

	public $has_one = array();
	public $has_many = array();

	public $validation = array(
		'email' => array(
			'type' => array('email' => 150),
			'rules' => array('required', 'unique'),
			'actions' => array('manage' => true, 'update_db' => false),
		),
		'password' => array(
			'type' => 'password',
			'input_params' => array('score' => true),
			'rules' => array('required', 'min_length' => 8, 'max_length' => 72, 'no_spaces', 'encrypt_password'),
			'actions' => array('details' => false, 'update' => false),
		),
		array(
			'field' => 'confirm_password',
			'type' => 'password',
			'rules' => array('matches_password'),
			'actions' => array('details' => false, 'insert' => false, 'update' => false),
		),
		'fullname' => array(
			'type' => array('str' => 150),
			'rules' => array('required', 'text_format'),
			'actions' => array('manage' => true),
		),
		/*
		'user_group_id' => array(
			'label' => 'lang:user_profile',
			'type' => array('int' => array(1, 3)),
			'values_array' => 'user_groups',
			'rules' => array(),
			'actions' => array('manage' => true, 'insert' => false, 'update' => false, 'export' => false, 'details' => false),
		),
		*/
		'last_ip' => array(
			'type' => array('str' => 40),
			'input_params' => array('class' => 'span2'),
			'rules' => array('valid_ip'),
			'actions' => array('manage' => true, 'update' => false, 'insert' => false),
		),
		'last_login' => array(
			'type' => 'datetime',
			'rules' => array(),
			'actions' => array('manage' => true, 'update' => false, 'insert' => false),
		),
		'status' => array(
			'label' => 'lang:active',
			'type' => 'bool',
			'output_type' => 'label',
			'output_params' => array('color_classes' => array(1 => 'label-important', 2 => 'label-success')),
			'rules' => array('required'),
			'actions' => array('manage' => true),
		),
	);

	public function __construct($id = null){
		parent::__construct($id);
	}

	public function post_model_init($from_cache = false){
		$this->init_field_type();
	}

	/**
	 * Save hook
	 */

	public function _save_hook($related){
		$success = $this->save($related);
		return $success;
	}

	/**
	 * Rules
	 */

	public function _matches_password($field){
		$ci =& get_instance();
		return $this->$field === $ci->input->post('password');
	}

	public function _encrypt_password($field){
		$ci =& get_instance();
		$ci->load->library('phpass');
		$this->$field = $ci->phpass->do_hash($this->$field);
	}

}

/* End of file user.php */
/* Location: ./application/models/datamapper/user.php */
