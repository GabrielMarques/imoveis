<?php

/**
 * Past_price DataMapper Model
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Past_price extends DataMapper {

	public $model = 'past_price';
	public $table = 'past_prices';
	public $default_order_by = array('id' => 'desc');
	public $details_field = 'id';
	public $id_visible = true;

	public $has_one = array('apartment');
	public $has_many = array();

	public $validation = array(
		'price' => array(
			'type' => 'currency',
			'rules' => array('required', 'greater_than' => 0),
		),
	);

	public function __construct($id = NULL){
		parent::__construct($id);
	}

	public function post_model_init($from_cache = FALSE){
		$this->init_field_type();
	}

}

/* End of file past_price.php */
/* Location: ./application/models/datamapper/past_price.php */