<?php

/**
 * Apartment DataMapper Model
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Apartment extends DataMapper {

	public $model = 'apartment';
	public $table = 'apartments';
	public $default_order_by = array('flagged' => 'desc');
	public $details_field = 'zap_id';
	public $id_visible = false;

	public $has_one = array();
	public $has_many = array('past_price', 'zap_image');

	public $validation = array(
		'image' => array(
			'type' => array('str' => 255),
			'rules' => array('valid_url'),
			'output_type' => 'image',
			'output_params' => array('enlarge_suffix' => '_grande'),
			'sort' => false,
			'actions' => array('manage' => true, 'update' => false),
		),
		'zap_images' => array(
			'type' => 'has_many',
			'class' => 'zap_image',
			'label_field' => 'url',
			'has_many_type' => 'no_join_table',
			'has_many_fields' => array(
				'url' => array(
				),
			),
			'output_params' => array('details_func' => '_get_images_details', 'details_fields' => array('url')),
			'rules' => array(),
			'actions' => array('insert' => false, 'update' => false, 'export' => false),
		),
		'zap_id' => array(
			'type' => 'int',
			'rules' => array('required', 'unique'),
			'actions' => array('manage' => true, 'update_db' => false),
		),
		'url' => array(
			'type' => array('url' => 255),
			'output_params' => array('url_limiter' => 20),
			'rules' => array('required', 'unique'),
			'actions' => array('manage' => true, 'update' => false),
		),
		'neighborhood' => array(
			'type' => array('str' => 255),
			'rules' => array('required'),
			'actions' => array('manage' => true, 'update' => false),
		),
		'street' => array(
			'type' => array('str' => 255),
			'output_params'=> array('manage_func' => '_get_street_url'),
			'rules' => array(),
			'actions' => array('manage' => true, 'update' => false),
		),
		'rooms' => array(
			'type' => 'int',
			'rules' => array('required', 'greater_than' => 0),
			'actions' => array('manage' => true, 'update' => false),
		),
		'area' => array(
			'type' => 'int',
			'rules' => array('required', 'greater_than' => 0),
			'actions' => array('manage' => true, 'update' => false),
		),
		'price' => array(
			'type' => 'currency',
			'rules' => array('required', 'greater_than' => 0),
			'actions' => array('manage' => true, 'update' => false),
		),
		'm2_price' => array(
			'type' => 'func',
			'select_function' => array('', array('@price', '[/]', '@area'), 'm2_price'),
			'output_type' => 'currency',
			'export_type' => 'currency',
			'actions' => array('manage' => true),
		),
		'past_prices' => array(
			'type' => 'has_many',
			'class' => 'past_price',
			'label_field' => 'price',
			'has_many_type' => 'no_join_table',
			'has_many_fields' => array(
				'price' => array(
				),
				'created' => array(
				),
			),
			'output_params' => array('details_func' => '_get_past_prices_details', 'details_fields' => array('price', 'modified')),
			'rules' => array(),
			'actions' => array('insert' => false, 'update' => false, 'export' => false),
		),
		'realtor' => array(
			'type' => array('str' => 255),
			'rules' => array(),
			'actions' => array('update' => false),
		),
		/*
		'realtor_url' => array(
			'type' => array('url' => 255),
			'rules' => array(),
			'actions' => array(),
		),
		*/
		'realtor_phone' => array(
			'type' => array('str' => 255),
			'rules' => array(),
			'actions' => array(),
		),
		'description' => array(
			'type' => array('blob' => 2000),
			'rules' => array(),
			'actions' => array(),
		),
		'zap_date' => array(
			'type' => 'str',
			'rules' => array('required'),
			'actions' => array('manage' => true, 'update' => false),
		),
		'modified' => array(
			'type' => 'date',
			'rules' => array(),
			'actions' => array('update' => false),
		),
		'type' => array(
			'type' => array('int' => array(1, 3)),
			'values_array' => 'apartment_types',
			'output_type' => 'label',
			'output_params' => array('color_classes' => array(1 => 'label-important', 2 => 'label-success')),
			'rules' => array('required'),
			'actions' => array('update' => false),
		),
		'flagged' => array(
			'type' => 'bool',
			'output_type' => 'icon',
			'rules' => array('required'),
			'actions' => array('manage' => true),
		),
		'active' => array(
			'type' => 'bool',
			'output_type' => 'label',
			'output_params' => array('color_classes' => array(1 => 'label-important', 2 => 'label-success')),
			'rules' => array('required'),
			'actions' => array('manage' => true),
		),
	);

	public function __construct($id = NULL){
		parent::__construct($id);
	}

	public function post_model_init($from_cache = FALSE){
		$this->init_field_type();
	}

	/**
	 * Get street url
	 */

  public function _get_street_url(){
  	if (empty($this->street) === false){
	  	return anchor('http://maps.google.com/maps?q=' . urlencode($this->street . ' ,' . $this->neighborhood . ', Rio de Janeiro'), $this->street, array('target' => '_blank'));
  	}else{
  		return '';
  	}
  }

	/**
	 * Get past prices details
	 */

  public function _get_past_prices_details(){
  	$past_prices = $this
			->past_price
			->select('id, price, created')
			->order_by('created', 'desc')
			->get();

		$values = array();
		foreach($past_prices as $past_price){
			$values[] = format_currency($past_price->price) . ' - ' . format_date($past_price->created, 'default_date_format');
		}
		return ul($values);
  }

	/**
	 * Get images details
	 */

  public function _get_images_details(){
		$images = $this
			->zap_image
			->select('id, url')
			->get();

		$values = array();
		foreach($images as $image){
			$img = img(array('src' => $image->url, 'width' => 90));
			$anchor = str_replace('.jpg', '_grande.jpg', $image->url);
			$values[] = anchor(
				$anchor,
				$img,
				array('class' => 'thumbnail enlarge', 'rel' => 'gallery')
			);
		}
		return ul($values, array('class' => 'thumbnails'));
  }

}

/* End of file apartment.php */
/* Location: ./application/models/datamapper/apartment.php */