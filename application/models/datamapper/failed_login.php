<?php

/**
 * Failed_login DataMapper Model
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Failed_login extends DataMapper {

	public $model = 'failed_login';
	public $table = 'failed_logins';
	public $default_order_by = array('last_attempt' => 'desc');
	public $details_field = 'email';
	public $id_visible = false;

	public $has_one = array();
	public $has_many = array();

	public $validation = array(
		'email' => array(
			'type' => array('str' => 150),
			'rules' => array('required'),
			'actions' => array('manage' => true, 'update_db' => false),
		),
		'current_attempts' => array(
			'type' => 'int',
			'rules' => array(),
			'actions' => array('manage' => true),
		),
		'attempts' => array(
			'type' => 'int',
			'rules' => array(),
			'actions' => array('manage' => true, 'update' => false),
		),
		'last_attempt' => array(
			'type' => 'datetime',
			'rules' => array('required'),
			'actions' => array('manage' => true, 'update' => false),
		),
		'last_ip' => array(
			'type' => 'str',
			'rules' => array('required'),
			'actions' => array('manage' => true, 'update' => false),
		),
	);

	public function __construct($id = null){
		parent::__construct($id);
	}

	public function post_model_init($from_cache = false){
		$this->init_field_type();
	}

}

/* End of file failed_login.php */
/* Location: ./application/models/datamapper/failed_login.php */