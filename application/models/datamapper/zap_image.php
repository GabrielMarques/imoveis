<?php

/**
 * Zap_image DataMapper Model
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Zap_image extends DataMapper {

	public $model = 'zap_image';
	public $table = 'zap_images';
	public $default_order_by = array('id' => 'desc');
	public $details_field = 'id';
	public $id_visible = true;

	public $has_one = array('apartment');
	public $has_many = array();

	public $validation = array(
		'url' => array(
			'type' => array('url' => 255),
			'rules' => array('required', 'unique'),
		),
	);

	public function __construct($id = NULL){
		parent::__construct($id);
	}

	public function post_model_init($from_cache = FALSE){
		$this->init_field_type();
	}

}

/* End of file zap_image.php */
/* Location: ./application/models/datamapper/zap_image.php */