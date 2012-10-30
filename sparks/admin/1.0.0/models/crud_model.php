<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Crud Model Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Crud_model extends MY_Model {

	public $object;
	public $details_field;

	private $query_has_many = false;
	private $restrictions = array();

	private $default_actions = array(
		'details' => false,
		'export' => false,
		'insert' => false,
		'update' => false,
		'delete' => false,
		'sort' => false,
	);

	private $default_table_params = array(
		'order_by' => array('field' => 'id', 'direction' => 'asc'),
		'limit' => 10,
		'page' => 1,
		'search' => '',
	);

	public function __construct(){
		parent::__construct();
	}

	/***************************
	 * Config
	 ***************************/

	/**
	 * Init
	 */

	public function init($params = false) {
		// load datamapper model
		$class = $params['class'];
		$this->object = new $class();

		// config
		$field = isset($this->object->details_field) ? $this->object->details_field : 'title';
		$this->details_field = isset($this->object->validation[$field]) ? $field : 'id';
		$this->default_table_params['order_by'] = $this->get_default_order_by();

		// set allowed actions
		if (isset($params['actions'])){
			$this->default_actions = array_merge($this->default_actions, $params['actions']);
		}

		// config
		if (isset($params['config'])){
			foreach($params['config'] as $key => $value){
				$this->$key = $value;
			}
		}

		// load actions
		if ($this->default_actions['insert'] === true){
			$this->actions->load('main', 'insert', null, array('class' => 'btn-primary', 'icon' => 'icon-plus'));
		}
		if ($this->default_actions['delete'] === true){
			$this->actions->load('table', 'delete', null, array('id' => '', 'icon' => 'icon-trash', 'class' => 'btn-danger', 'modal' => true, 'modal_body' => $this->lang->line('confirm_delete')));
		}
		if ($this->default_actions['export'] === true){
			$this->actions->load('direct', 'export', null,  array('icon' => 'icon-file', 'modal' => true, 'modal_body' => $this->lang->line('confirm_long')));
		}
		if ($this->default_actions['sort'] === true){
			$this->actions->load('direct', 'sort', null, array('icon' => 'icon-resize-vertical'));
		}
		if ($this->default_actions['update'] === true){
			$this->actions->load('row', 'update', null);
		}
		if ($this->default_actions['details'] === true){
			$this->actions->allow->details = true;
		}
  }

	/**
	 * Reset session vars
	 */

	public function reset_table_params($keep_session = false){
		if ($keep_session === false){
			$this->session->set_userdata($this->default_table_params);
			return $this->default_table_params;
		}else{
			$table_params = array();
			foreach($this->default_table_params as $key => $value){
				$table_params[$key] = $this->session->userdata($key);
			}
			return $table_params;
		}
	}

	/**
	 * Set session vars based on client params
	 */

	public function set_table_params(){
		$params = array();

		// validate params
		$order_by = $this->input->get('order_by');
		if ($order_by !== false && is_array($order_by)){
			$params['order_by'] = $order_by;
		}

		$limit = $this->input->get('limit');
		if ($limit !== false && is_numeric($limit) && $limit < 101){
			$params['limit'] = $limit;
		}

		$page = $this->input->get('page');
		if ($page !== false && is_numeric($page)){
			$params['page'] = $page;
		}

		$params['search'] = $this->input->get('search');

		// merge params
		$params = array_merge($this->default_table_params, $params);
		$this->session->set_userdata($params);
	}

	/***************************
	 * Manage
	 ***************************/

	/**
	 * Get manage fields
	 */

	private function get_manage_fields(){
		$fields = array();

		if (isset($this->object->manage_fields)){
			foreach($this->object->manage_fields as $key => $size){
				$key = is_numeric($key) ? $size : $key;
				if (isset($this->object->validation[$key])){
					$fields[$key] = $this->object->validation[$key];
				}
			}
		}else{
			foreach($this->object->validation as $key => $field){
				if (isset($field['actions']) && $field['actions']['manage'] === true){
					if ($field['type'] === 'password'){
						continue;
					}
					$fields[$key] = $field;
				}
			}
		}

		return $fields;
	}

	/**
	 * Get manage data
	 */

	public function get_manage_rows($type = 'server'){
		$final_data = array();

		// get row actions
		$row_actions = $this->actions->get_actions('row');

		// get manage data
		$data =& $this->get_rows();

		// manage data hook
		if (isset($this->object->manage_hook_on) && $this->object->manage_hook_on === true){
			$data['rows'] =& $this->object->_manage_hook($data['rows']);
		}

		$final_data['total_rows'] = $data['total_rows'];
		if ($type === 'server'){
			$final_data['fields'] = $data['fields'];
		}

		$final_data['rows'] = array();
		foreach($data['rows'] as $row){
			$cols = array();

		  // checkboxes
		  if ($this->actions->checkboxes_on){
		    $cols['checkbox'] = form_checkbox(array('name' => 'rows[]', 'value' => $row->id));
		  }

		  // columns
		  foreach($data['fields'] as $key => $field){
	  		// get row value
		  	$row_value = $row->get_field_output($key);

		  	// get html output
		  	$params = isset($field['output_params']) ? $field['output_params'] : null;
		  	$row_value = get_html_output($row_value, $field['output_type'], $params);

		  	// details field
		    if ($this->actions->allowed('details') && $key === $this->details_field){
  				$row_value = anchor($this->navigation->current_route . '/details/' . $row->id, $row_value);
  			}

		  	$cols[$key] = $row_value;
		  }

		  // row actions
		  if ($row_actions !== false){
		    $row_options_list = array();
		    foreach($row_actions as $route => $action){
		    	$btn = $this->actions->render_btn($route, array('row' => $row->to_array(), 'as_btn' => false));
		    	if ($btn !== false){
		      	$row_options_list[] = $btn;
		    	}
		    }
		    $cols['actions'] = '<div class="row-actions pull-right">' . implode('<span class="action-divider">|</span>', $row_options_list) . '</div>';
		  }

		  $final_data['rows'][] = array(
		  	'id' => $row->id,
				'cols' => $cols,
			);
		}

		return $final_data;
	}

	/**
	 * Get manage rows
	 */

	public function get_rows($type = 'manage'){
		$results = array();

		// get fields
		$fields = $type === 'export' ? $this->get_export_fields() : $this->get_manage_fields();
		$results['fields'] = $fields;

		// set filters
		$this->object->start_cache();
		$this->set_user_filters();

		// set restrictions
		$this->set_restrictions();

		// search
		$search = $this->session->userdata('search');
		if (empty($search) === false){
			$this->object->group_start();
			foreach($fields as $key => $field){
				switch($field['type']){
					case 'has_one':
						if (is_array($field['label_field'])){
							foreach($field['label_field'] as $label){
								$this->object->or_like_related($key, $label, $search);
							}
						}else{
							$this->object->or_like_related($key, $field['label_field'], $search);
						}
						break;
					case 'has_one_related':
						$this->object->or_like_related($key, $field['label_field'], $search);
						break;
					case 'has_many':
						$this->object->or_like_related($field['class'], $field['label_field'], $search);
						break;
					case 'func':
						break;
					default:
						$this->object->or_like($key, $search);
						break;
				}
			}
			$this->object->group_end();
		}
		$this->object->stop_cache();


		// count rows
		//$count_obj = $this->object->get_clone(true);
		$results['total_rows'] = $this->object->count_distinct();

		// select fields
		if (!isset($fields['id'])){
			$select_fields = array('id');
		}
		foreach($fields as $key => $field){
			switch($field['type']){
				case 'has_one':
					$select_fields[] = $key . '_id';
					break;
				case 'has_one_related':
					break;
				case 'has_many':
					$this->group_has_many($key, $field);
					break;
				case 'func':
					call_user_func_array(array($this->object, 'select_func'), $field['select_function']);
					break;
				default:
					$select_fields[] = $key;
					break;
			}
		}

		$this->object->select(implode(', ', $select_fields));

		// join related
		$this->join_related_fields($fields);

		// order by
		$order_by = $this->session->userdata('order_by');
		if ($order_by !== false){
			$field = $order_by['field'];
			$direction = $order_by['direction'];

			switch($fields[$field]['type']){
				case 'has_one':
					$label = is_array($fields[$field]['label_field']) ? $fields[$field]['label_field'][0] : $fields[$field]['label_field'];
					$this->object->order_by_related($fields[$field]['field'], $label, $direction);
					break;
				case 'has_one_related':
					$this->object->order_by_related($fields[$field]['field'], $fields[$field]['label_field'], $direction);
					break;
				case 'has_many':
					$this->object->order_by_related($fields[$field]['class'], $fields[$field]['label_field'], $direction);
					break;
				default:
					$this->object->order_by($field, $direction);
					break;
			}
		}

		// limit
		$limit = $this->session->userdata('limit');
		$page = $this->session->userdata('page');
		if ($limit !== false && $page !== false){
			$this->object->limit($limit, ($page - 1) * $limit);
		}

		// group by
		$this->object->group_by('id');

//		echo $this->object->get_sql();
//		exit;

		// get
		//$results['rows'] = $this->object->get_iterated();
		$results['rows'] = $this->object->get();
		return $results;
	}

	/***************************
	 * Export
	 ***************************/

	/**
	 * Get export fields
	 */

	private function get_export_fields(){
		$fields = array();

		foreach($this->object->validation as $key => $field){
			if (isset($field['actions']) && $field['actions']['export'] === true){
				if (isset($field['type']) === false || $field['type'] === 'password'){
					continue;
				}
				$fields[$key] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Get export data
	 */

	public function get_export_rows(){
		$final_data = array();

		// get manage data
		$data =& $this->get_rows('export');
		$final_data['total_rows'] = $data['total_rows'];
		$final_data['fields'] = $data['fields'];

		$final_data['rows'] = array();
		foreach($data['rows'] as $row){
			$cols = array();

		  // columns
		  foreach($data['fields'] as $key => $field){
	  		// get row value
		  	$row_value = $row->get_field_output($key);
		  	$cols[$key] = $row_value;
		  }

		  $final_data['rows'][] = $cols;
		}
		unset($this->object);

		return $final_data;
	}

	/***************************
	 * Details
	 ***************************/

	/**
	 * Get details row
	 */

	public function get_details($id, $fields = false){
		if ($this->actions->allowed('details') === false || is_numeric($id) === false){
			return false;
		}

		if ($fields === false){
			$fields = $this->get_details_fields();
		}

		// select fields
		foreach($fields as $key => $field){
			switch($field['type']){
				case 'has_many':
					//$this->group_has_many($key, $field);
					break;
				case 'func':
					call_user_func_array(array($this->object, 'select_func'), $field['select_function']);
					break;
			}
		}

		// join related
		$this->join_related_fields($fields);

		// set restrictions
		$this->set_restrictions();

		// get object
		$this->object
			->select('*')
			->where('id', $id)
			->get();

		if ($this->object->exists() === false){
			return false;
		}

		$values = array();
		foreach($fields as $key => $field){
			$row_value = $this->object->get_field_output($key, true);
			$params = isset($field['output_params']) ? $field['output_params'] : null;
			$params['details'] = true;
			$values[$key] = get_html_output($row_value, $field['output_type'], $params);
		}

		$values['id'] = $this->object->id;
		return array('fields' => $fields, 'values' => $values, 'raw_values' => $this->object->to_array());
	}

	/**
	 * Get details fields
	 */

	private function get_details_fields(){
		$fields = array();

		foreach($this->object->validation as $key => $field){
			if (isset($field['actions']) && $field['actions']['details'] === true){
				if ($field['type'] === 'password'){
					continue;
				}
				$fields[$key] = $field;
			}
		}

		return $fields;
	}

	/***************************
	 * Insert
	 ***************************/

	/**
	 * Get insert fields
	 */

	public function get_insert_fields($type = 'insert_form'){
		$fields = array();
		foreach($this->object->validation as $key => $field){
			if (isset($field['actions']) && $field['actions'][$type] === true){

				if (($field['type'] === 'has_many' && !isset($field['input_params']['html']) && !isset($field['has_many_fields'])) || $field['type'] === 'func' || $field['type'] === 'has_one_related'){
					continue;
				}

				// populate insert form values array
				if ($type === 'insert_form'){
					$this->set_dropdown_values($key, $field);
					$this->set_custom_form($key, $field, false);

					$fields[$key] = array(
						'type' => $field['input_type'],
						'label' => $field['label'],
						'values_array' => $field['values_array'],
					);
					$input_params = isset($field['input_params']) ? $field['input_params'] : array();
					$fields[$key] = array_merge($fields[$key], $input_params);
				}else{
					$fields[$key] = $field;
				}
			}
		}

		return $fields;
	}

	/**
	 * Insert row
	 */

	public function insert($fields = false){
		if ($fields === false){
			$fields = $this->get_insert_fields('insert_db');
		}

		// get post vars
		$values = $this->get_post_vars($fields);

		// update order if sort on
		if ($this->object->sort_on){
			if ($this->object->new_on_top){
				$this->object->select_min('sort_id')->get();
				$values['sort_id'] = empty($this->object->sort_id) ? 1 : $this->object->sort_id - 1;
			}else{
				$this->object->select_max('sort_id')->get();
				$values['sort_id'] = $this->object->sort_id + 1;
			}
		}

		// save to db
		$success = $this->save_object($fields, $values);
		//die($this->object->error->string);

		// update order for all rows if new_on_top = true
		if ($success && $this->object->sort_on && $this->object->new_on_top){
			$this->object->update('sort_id', 'sort_id + 1', false);
		}

		if ($success){
			return $this->object->id;
		}else{
			return array('message' => 'error_form_default', 'errors' => $this->get_errors_array($fields));
		}
	}

	/***************************
	 * Update
	 ***************************/

	/**
	 * Get update fields
	 */

	public function get_update_fields($type = 'update_form'){
		$fields = array();
		foreach($this->object->validation as $key => $field){
			if (isset($field['actions']) && $field['actions'][$type] === true){

				if (($field['type'] === 'has_many' && !isset($field['input_params']['html']) && !isset($field['has_many_fields'])) || $field['type'] === 'func' || $field['type'] === 'has_one_related'){
					continue;
				}

				if ($type === 'update_form'){
					$this->set_dropdown_values($key, $field);

					// disable if update_db = false
					if ($field['actions']['update_db'] === false){
						$field['input_params']['disabled'] = true;
					}
				}

				$fields[$key] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Get update row
	 */

	public function get_update_form($id, $fields = false){
		if ($this->actions->allowed('update') === false){
			return false;
		}

		if ($fields === false){
			$fields = $this->get_update_fields('update_form');
		}

		// join related
		$this->join_related_fields($fields);

		// set restrictions
		$this->set_restrictions();

		// get row
		$this->object->get_by_id($id);

		if ($this->object->exists() === false){
			return false;
		}

		// get values
		$values = array();
		foreach($fields as $key => $field){
			$type = isset($field['type']) ? $field['type'] : null;
			switch($type){
				case 'has_one':
					$values[$key] = $this->object->$key->id;
					break;
				case 'has_many':
					break;
				default:
					$values[$key] = $this->object->$key;
					break;
			}
		}
		$values['id'] = $this->object->id;

		// set form fields array
		$final_fields = array();
		foreach($fields as $key => $field){

			// set custom html
			$this->set_custom_form($key, $field, $values);

			$final_fields[$key] = array(
				'type' => $field['input_type'],
				'label' => $field['label'],
				'values_array' => $field['values_array'],
			);
			$input_params = isset($field['input_params']) ? $field['input_params'] : array();
			$final_fields[$key] = array_merge($final_fields[$key], $input_params);
		}

		return array('fields' => $final_fields, 'values' => $values);
	}

	/**
	 * Update row
	 */

	public function update($get = false, $fields = false, $values = false){
		if ($fields === false){
			$fields = $this->get_update_fields('update_db');
		}

		// get post vars
		if ($values === false){
			$values = $this->get_post_vars($fields);
		}

		// get object
		if ($get !== false){
			$this->object->get_by_id($get);
		}

		// save object
		$success = $this->save_object($fields, $values);

		if ($success){
			return true;
		}else{
			return array('message' => 'error_form_default', 'errors' => $this->get_errors_array($fields));
		}
	}

	/***************************
	 * Save
	 ***************************/

  /**
	 * Save to db
	 */

	private function save_object($fields, $values){
		$related = array();
		$has_many_fields = array();
		$errors = array();

		// pre validation
		foreach($fields as $key => $field){
			$type = isset($field['type']) ? $field['type'] : null;
			switch($type){
				case 'has_one':
					$this->object->add_rules($key, 'has_one');

					if (empty($values[$key])){
						// empty post var

						if (in_array('required', $field['rules']) === false){
							$related[] = new $key();
						}
					}else{
						// not empty

						$obj = new $key($values[$key]);
						if ($obj->exists() === true){
							$related[] = $obj;
						}
					}
					break;
				case 'has_many':
					$has_many_fields[$key] = $field;
					break;
				default:
					if ($values[$key] !== false){
						if (($field['input_type'] === 'dropdown' || $field['input_type'] === 'bool' || $field['input_type'] === 'radio') && $values[$key] == 0){
							$this->object->$key = null;
						}else if ($values[$key] === null || $values[$key] === ''){
							$this->object->$key = null;
						}else{
							$this->object->$key = $values[$key];
						}
					}
					break;
			}
		}

		// post save hook
		if (isset($this->object->save_hook_on) && $this->object->save_hook_on === true){
			$success = $this->object->_save_hook($related, $has_many_fields);
		}else if (sizeof($has_many_fields) > 0){
			$success = $this->save_has_many($related, $has_many_fields);
		}else{
			$success = $this->object->save($related);
		}

		return $success;
	}

  /**
	 * Save has many fields to db (ufa!)
	 */

	public function save_has_many($related, $fields, $rollback = false, $errors = array()){
		$new_objs = array();
		$preserve_objs = array();
		$preserve_ids = array();

		// start trans
		$this->object->trans_begin();

		foreach($fields as $field => $params){
			$class = $params['class'];
			$new_objs[$field] = array();
			$preserve_ids[$field] = array();
			$preserve_objs[$field] = array();
			$rows = array();

			// get post rows
			foreach($params['has_many_fields'] as $sub_field => $sub_params){
				$rows[$sub_field] = $this->input->post($class . '_' . $sub_field);
			}

			// validate rows
			$duplicate_keys = array();
			$id_field = key($rows);
			$rows_cnt = sizeof($rows[$id_field]);
			$order_cnt = 1;
			for($cnt = 0; $cnt < $rows_cnt; $cnt++){
				switch($params['has_many_type']){

					case 'no_join_table':
						if (empty($rows[$id_field][$cnt]) === false && in_array($rows[$id_field][$cnt], $duplicate_keys) === false){
							$duplicate_keys[] = $rows[$id_field][$cnt];

							// check if related obj already exists
							$objs = $this->object->{$class}->get();
							$not_related = true;
							if ($objs->exists() === true){
								foreach($objs as $obj){
									if ($obj->$id_field == $rows[$id_field][$cnt]){
										$not_related = false;
										$preserve_ids[$field][] = $obj->id;
										foreach($params['has_many_fields'] as $sub_field => $sub_params){
											$obj->$sub_field = $rows[$sub_field][$cnt];
										}
										if (isset($params['sort_on']) && $params['sort_on'] === true){
											$obj->sort_id = $order_cnt;
											$order_cnt++;
										}
										$preserve_objs[$field][] = $obj;
										break;
									}
								}
							}

							if ($not_related === true){
								$obj = new $class();
								foreach($params['has_many_fields'] as $sub_field => $sub_params){
									$obj->$sub_field = $rows[$sub_field][$cnt];
								}
								if (isset($params['sort_on']) && $params['sort_on'] === true){
									$obj->sort_id = $order_cnt;
									$order_cnt++;
								}
								$new_objs[$field][] = $obj;
							}

							// validate obj
							$success = $obj->validate_special();
							if ($success === false){
								$errors[] = array('key' => $field, 'message' => sprintf($this->lang->line('error_related'), $params['label']));
								$rollback = true;
								break 2;
							}
						}
						break;

					case 'default_join':
						if (empty($rows[$id_field][$cnt]) === false){
							$obj = new $class();
							$obj
								->where('id', $rows[$id_field][$cnt])
								->get();

							if ($obj->exists()){
								if ($this->object->is_related_to($obj) === false){
									// new relationship

									/*
									// save join fields (for later)
									foreach($params['has_many_fields'] as $sub_field => $sub_params){
										if (isset($sub_params['join_field']) && $sub_params['join_field'] === true){
											$obj->$sub_field = $rows[$sub_field][$cnt];
										}
									}*/

									$new_objs[$field][] = $obj;
									$related[] = $obj;
								}else{
									// existing relationship

									$preserve_ids[$field][] = $obj->id;

									/*
									// save join fields
									foreach($params['has_many_fields'] as $sub_field => $sub_params){
										if (isset($sub_params['join_field']) && $sub_params['join_field'] === true){
											if ($rollback === false){
												$this->object->set_join_field($obj, $sub_field, $rows[$sub_field][$cnt]);
											}
										}
									}
									*/

								}
							}else{
								$errors[] = array('key' => $field, 'message' => sprintf($this->lang->line('error_related'), $params['label']));
								$rollback = true;
								break 2;
							}
						}
						break;

					case 'has_join_fields':
						if (empty($rows[$id_field][$cnt]) === false){
							$obj = new $class();
							$obj
								->where('id', $rows[$id_field][$cnt])
								->get();

							if ($obj->exists()){
								// save join fields (for later)
								foreach($params['has_many_fields'] as $sub_field => $sub_params){
									if (isset($sub_params['join_field']) && $sub_params['join_field'] === true){
										$obj->$sub_field = $rows[$sub_field][$cnt];
									}
								}

								$new_objs[$field][] = $obj;
							}else{
								$errors[] = array('key' => $field, 'message' => sprintf($this->lang->line('error_related'), $params['label']));
								$rollback = true;
								break 2;
							}
						}
						break;
				}
			}

			// at least one
			if (in_array('required', $params['rules']) === true && sizeof($new_objs[$field]) === 0 && sizeof($preserve_ids[$field]) === 0){
				$errors[] = array('key' => $field, 'message' => sprintf($this->lang->line('error_related_required'), $params['label']));
				$rollback = true;
			}
		}

		// delete old relationships
		foreach($fields as $field => $params){
			if ($params['has_many_type'] === 'default_join' || $params['has_many_type'] === 'has_join_fields'){
				$class = $params['class'];
				$objs = $this->object->$class->get();
				if ($objs->exists() === true){
					foreach($objs as $obj){
						if (in_array($obj->id, $preserve_ids[$field]) === false || $params['has_many_type'] === 'has_join_fields'){
							$this->object->delete($obj);
						}
					}
				}
			}
		}

		// save
		$success = $this->object->save($related);
		if ($rollback === true || $success === false || $this->object->trans_status() === false){

			// rollback
			$this->object->trans_rollback();

			foreach($errors as $error){
				$this->object->error_message($error['key'], $error['message']);
			}

			return false;
		}else{
			// commit
			$this->object->trans_commit();

			foreach($fields as $field => $params){
				switch($params['has_many_type']){

					case 'no_join_table':
						// delete
						$class = $params['class'];
						$objs = $this->object->$class->get();
						if ($objs->exists() === true){
							foreach($objs as $obj){
								if (in_array($obj->id, $preserve_ids[$field]) === false){
									$obj->delete();
								}
							}
						}

						//save
						foreach($new_objs[$field] as $obj){
							$obj->save($this->object);
						}

						//save
						foreach($preserve_objs[$field] as $obj){
							$obj->save();
						}
						break;

					case 'has_join_fields':
						foreach($new_objs[$field] as $obj){
							$join_class = $params['has_many_join_class'];
							$join_obj = new $join_class();
							$join_obj->{$this->object->model . '_id'} = $this->object->id;
							$join_obj->{$params['class'] . '_id'} = $obj->id;

							// save join fields
							foreach($params['has_many_fields'] as $sub_field => $sub_params){
								if (isset($sub_params['join_field']) && $sub_params['join_field'] === true){
									$join_obj->$sub_field = $obj->$sub_field;
								}
							}

							$join_obj->save();
							//die($join_obj->error->string);
						}
						break;
				}
			}
			return true;
		}
	}

	/***************************
	 * Delete
	 ***************************/

	/**
	 * Delete row
	 */

	public function delete_rows($ids, $return_array = false){
		if ($this->actions->allowed('delete') === false || is_array($ids) === false){
			return false;
		}

		// set restrictions
		$this->set_restrictions();

		// get objects
		$this->object
			->where_in('id', $ids)
			->get();

		if ($this->object->exists() === false){
			return false;
		}

		$success = true;
		$errors = array();
		$rows_array = array();

		foreach($this->object as $row){
			$label = $row->{$this->details_field};
			$rows_array[] = $row->to_array();

			if ($row->delete() === false){
				$errors[] = '<strong>' . $label . '</strong>';
				$success = false;
			}
		}

		// sort
		if ($this->object->sort_on){
			$this->sort_rows(true);
		}

		if ($success === false){
			return sprintf($this->lang->line('error_delete_many'), ul($errors));
		}else{
			return $return_array === false ? true : $rows_array;
		}
	}


	/***************************
	 * Sort
	 ***************************/

	/**
	 * Get sort rows
	 */

	public function get_sort_rows(){
		$rows = array();

		$fields = array('id', 'sort_id', $this->crud->details_field);
		$this->object->select(implode(', ', $fields));
		$this->object->order_by('sort_id', 'asc');
		$this->object->get_iterated();

		if ($this->object->exists() === true){
			foreach($this->object as $row){
				$rows[] = $row->to_array();
			}
		}

		return $rows;
	}

	/**
	 * Process sort rows
	 */

	public function sort_rows($update = false, $rows = array()){
		if ($update === true){
			$rows =& $this->object->select('id')->order_by('sort_id', 'asc')->get_iterated();
		}

		$cnt = 1;
		foreach($rows as $row){
			$id = isset($row->id) ? $row->id : $row;
			$this->object->where('id', $id)->update('sort_id', $cnt);
			$cnt++;
		}
	}

	/***************************
	 * Restrictions / filters
	 ***************************/

	/**
	 * Add permanent filter
	 */

	public function load_restriction($field, $value, $params = array()){
		$this->restrictions[] = array(
			'field' => $field,
			'value' => $value,
			'or' => isset($params['or']) ? $params['or'] : false,
			'in' => isset($params['in']) ? $params['in'] : false,
			'related' => isset($params['related']) ? $params['related'] : false,
		);
	}

	/**
	 * Set permanent filter on db object
	 */

	public function set_restrictions(){
		foreach($this->restrictions as $restrict){
  		if ($restrict['related'] !== false){
  			if ($restrict['or'] === true){
  				if ($restrict['in'] === true){
						$this->object->or_where_in_related($restrict['related'], $restrict['field'], $restrict['value']);
  				}else{
						$this->object->or_where_related($restrict['related'], $restrict['field'], $restrict['value']);
  				}
  			}else{
  				if ($restrict['in'] === true){
						$this->object->where_in_related($restrict['related'], $restrict['field'], $restrict['value']);
  				}else{
						$this->object->where_related($restrict['related'], $restrict['field'], $restrict['value']);
  				}
  			}
  		}else{
  			if ($restrict['or'] === true){
  				if ($restrict['in'] === true){
						$this->object->or_where($restrict['field'], $restrict['value']);
  				}else{
						$this->object->or_where_in($restrict['field'], $restrict['value']);
  				}
  			}else{
  				if ($restrict['in'] === true){
						$this->object->where_in($restrict['field'], $restrict['value']);
  				}else{
						$this->object->where($restrict['field'], $restrict['value']);
  				}
  			}
  		}
		}
	}

	/**
	 * Set user filters on db object
	 */

	public function set_user_filters(){
		$session_filters = $this->session->userdata('filters');
		foreach($this->filters->filters as $key => $filter){
			if (isset($this->restrictions[$key]) === false && isset($session_filters[$key])){
				switch($filter['type']){
					case 'dropdown':
						if ($session_filters[$key] > 0){
							if ($filter['related'] === false){
								$this->object->where($key, $session_filters[$key]);
							}else{
								$this->object->where_related($filter['related'], $filter['related_field'], $session_filters[$key]);
							}
						}
						break;
					case 'multiselect':
						if ($session_filters[$key] > 0){
							if ($filter['related'] === false){
								if (is_array($session_filters[$key])){
									$this->object->where_in($key, $session_filters[$key]);
								}else{
									$this->object->where($key, $session_filters[$key]);
								}
							}else{
								if (is_array($session_filters[$key])){
									$this->object->where_in_related($filter['related'], $filter['related_field'], $session_filters[$key]);
								}else{
									$this->object->where_related($filter['related'], $filter['related_field'], $session_filters[$key]);
								}
							}
						}
						break;
					case 'search_area':
						$strings = explode("\n", trim($session_filters[$key]));
						$this->object->group_start();
						foreach($strings as $str){
							$this->object->or_where($key, trim($str));
						}
						$this->object->group_end();
						break;
					case 'search':
						if ($filter['related'] === false){
							$this->object->where($key, $session_filters[$key]);
						}else{
							$this->object->where_related($filter['related'], $filter['related_field'], $session_filters[$key]);
						}
						break;
					case 'from_to':
						$from_value = $session_filters[$key][0];
						$to_value = $session_filters[$key][1];
						if (($filter['from_to_type'] === 'number' && $from_value > 0) || is_valid_date($from_value)){
							if ($filter['from_to_type'] === 'datetime'){
								$from_value .= ' 00:00:00';
							}
							$this->object->where($key . ' >=', $from_value);
						}
						if (($filter['from_to_type'] === 'number' && $to_value > 0) || is_valid_date($to_value)){
							if ($filter['from_to_type'] === 'datetime'){
								$to_value .= '23:59:59';
							}
							$this->object->where($key . ' <=', $to_value);
						}
						break;
				}
			}
		}
	}

	/***************************
	 * Etc
	 ***************************/

	/**
	 * Override field settings
	 */

	public function set_fields($fields, $settings, $value){
		$fields = is_array($fields) ? $fields : array($fields);
		foreach($fields as $key){
			$tree = is_array($settings) ? $settings : array($settings);
			$field =& $this->object->validation[$key];
			foreach ($tree as $branch) {
			  $field =& $field[$branch];
			}
			$field = $value;
		}
	}

	/**
	 * Get post vars
	 */

	private function get_post_vars($fields){
		$values = array();
		foreach($fields as $key => $field){
			$values[$key] = post_to_var($key);
		}
		return $values;
	}

	/**
	 * Join related fields
	 */

	private function join_related_fields($fields){
		foreach($fields as $key => $field){
			$type = isset($field['type']) ? $field['type'] : null;
			switch($type){
				case 'has_one':
					$labels = is_array($field['label_field']) ? array_merge(array('id'), $field['label_field']) : array('id', $field['label_field']);
					$this->object->include_related($key, $labels, true, true);
					break;
				case 'has_one_related':
					$this->object->include_related($key, array('id', $field['label_field']));
					break;
			}
		}
	}

	/**
	 * Group has many field
	 */

	private function group_has_many($key, $field){
		if ($this->query_has_many === false){
			$this->db->query('SET SESSION group_concat_max_len = 10000');
			$this->query_has_many = true;
		}
		$f = '@' . $field['class'] . '/' . $field['label_field'];
		$this->object->select_func('GROUP_CONCAT', array('[DISTINCT]', $f, '[ORDER BY]', $f, '[SEPARATOR]', $this->config->item('list_separator')), $key);
		$this->object->group_by('id');
	}

	/**
	 * Get default order by
	 */

	private function get_default_order_by($obj = false){
		$order_by = 'id';
		$order_dir = 'asc';

		$order_by = $obj === false ? $this->object->default_order_by : $obj->default_order_by;

		foreach($order_by as $key => $value){
			$order_by = $key;
			$order_dir = $value;
			break;
		}
		return array('field' => $order_by, 'direction' => $order_dir);
	}

	/**
	 * Populate values array
	 */

	private function set_dropdown_values($key, &$field){
		if (is_string($field['values_array']) && method_exists($this->object, $field['values_array'])){
			$field['values_array'] = call_user_func(array($this->object, $field['values_array']));
		}else if($field['type'] === 'has_one'){
			$labels = is_array($field['label_field']) ? $field['label_field'] : array($field['label_field']);
			$params = isset($field['values_array_params']) ? $field['values_array_params'] : array();
			$field['values_array'] = $this->get_values_array($key, false, $labels, $params);
		}
	}

	/**
	 * Get field html
	 */

	private function set_custom_form($key, &$field, $values){
		if($field['type'] === 'has_many' && isset($field['has_many_fields'])){
			$field['input_params']['html'] = call_user_func_array(array($this, 'get_has_many_form'), array($field, $field['has_many_fields']));
		}

		if (isset($field['input_params']['html'])){
			if (is_array($field['input_params']['html'])){
				//$field['input_params']['html'] = call_user_func_array(array($this, 'get_has_many_form'), array($field['class'], $field['input_params']['html']));
			}else if(method_exists($this->object, $field['input_params']['html'])){
				$field['input_params']['html'] = call_user_func_array(array($this->object, $field['input_params']['html']), array($key, $values));
			}
		}
	}

	/**
	 * Get has many form
	 */

	private function get_has_many_form($parent_field, $has_many_fields){
		$class = $parent_field['class'];

		// build has many arrays
		$rows = array();
		$input_params = array();
		$post_sent = false;

		$fields = array();
		foreach($has_many_fields as $field => $params){
			if (is_numeric($field)){
				$fields[$params] = array();
			}else{
				$fields[$field] = $params;
			}
		}

		foreach($fields as $field => $params){
			if ($this->input->post($class . '_' . $field) !== false){
				$post_sent = true;
			}

			// set params
			$input_params[$field] = array(
				'type' => 'text',
				'required' => false,
				'placeholder' => $this->lang->line($field),
				'class' => isset($params['class']) ? $params['class'] : 'span3',
			);

			if (isset($params['values_array'])){
				if (is_array($params['values_array'])){
					$params['values_array'] = call_user_func_array(array($this, 'get_values_array'), $params['values_array']);
				}else if (method_exists($this->object, $params['values_array'])){
					$params['values_array'] = call_user_func(array($this->object, $params['values_array']));
				}else if ($this->config->item($params['values_array']) !== false){
					$params['values_array'] = $this->config->item($params['values_array']);
				}
			}

			foreach($params as $key => $value){
				$input_params[$field][$key] = $value;
			}
		}

		if ($post_sent === true){
			// populate arrays from sent post form vars
			foreach($fields as $field => $params){
				$rows[$field] = $this->input->post($class . '_' . $field);
			}
		}else if (isset($this->object->id)){
			// no post vars? get from db
			$this->object->get_by_id($this->object->id);

			reset($fields);
			$order_by = isset($parent_field['has_many_order_by']) ? $parent_field['has_many_order_by'] : key($fields) . ' asc';

			$objs = $this->object->{$class}
				->include_join_fields()
				->order_by($order_by)
				->get();

			if ($objs->exists()){
				foreach($objs as $obj){
					foreach($fields as $field => $params){
						if (isset($params['join_field']) && $params['join_field'] === true){
							$rows[$field][] = $obj->{'join_' . $field};
						}else{
							$rows[$field][] = $obj->{$field};
						}
					}
				}
			}else{
				// no db? set one element as null
				foreach($fields as $field => $params){
					$rows[$field][] = null;
				}
			}
		}else{
			// no id yet? set set one element as null
			foreach($fields as $field => $params){
				$rows[$field][] = null;
			}
		}

		// build html rows
		$rows_cnt = sizeof($rows[key($rows)]);

		$html = '';
		for($cnt = 0; $cnt < $rows_cnt; $cnt++){
			$classes = array('has-many');

			$disabled = $cnt > 0 ? true : false;
			foreach($fields as $field => $params){
				if ($rows[$field][$cnt] !== null){
					$disabled = false;
				}
			}

			if ($disabled === true){
				$classes[] = 'hide';
			}

			//$html .= '<div class="' . implode(' ', $classes) . '" data-index="' . $cnt . '">';
			$html .= '<div class="' . implode(' ', $classes) . '">';
			foreach($fields as $field => $params){
				//$html .= $this->build_form->render_input($class . '_' . $field . '[]', $input_params[$field]['type'], $rows[$field][$cnt], $input_params[$field]);
				$input_params[$field]['value'] = $rows[$field][$cnt];
				$html .= $this->build_form->render_input($class . '_' . $field . '[]', '', $input_params[$field]);
			}

			if ($cnt === 0){
				$html .= form_button(array('content' => '<i class="icon-plus icon-white"></i>', 'class' => 'btn btn-primary btn-small has-many-add'));
			}else{
				$html .= form_button(array('content' => '<i class="icon-minus icon-white"></i>', 'class' => 'btn btn-danger btn-small has-many-remove'));
			}
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Get errors array
	 */

	private function get_errors_array($fields){
		$errors = array();
		foreach($fields as $key => $field){
			$errors[$key] = $this->object->error->$key;
		}
		return $errors;
	}

	/**
	 * Get dropdown values array
	 */

  public function get_values_array($class, $restrict = false, $values = array('title'), $params = array()){
  	$obj = new $class();

  	$key = isset($params['key']) ? $params['key'] : 'id';
	  $select = $key === false ? $values : array_merge(array($key), $values);
  	$order_by = isset($params['order_by']) ? $params['order_by'] : $this->get_default_order_by($obj);

	  $obj
	  	->distinct()
	  	->select(implode(', ', $select));

  	if ($restrict !== false){
  		if (isset($restrict['class']) && empty($restrict['value']) === false){
				$obj->where_related($restrict['class'], $restrict['field'], $restrict['value']);
  		}else if (isset($restrict['field']) && isset($restrict['value']) && empty($restrict['value']) === false){
				$obj->where($restrict['field'], $restrict['value']);
  		}else if (isset($restrict['field_value'])){
				$obj->where($restrict['field_value']);
  		}
  	}

  	if (is_array($order_by)){
			$obj->order_by($order_by['field'], $order_by['direction']);
  	}else{
			$obj->order_by($order_by);
  	}
		//die($obj->get_sql());
  	$obj->get();

		$results = array();
		foreach($obj as $row){
			$final_values = array();
			foreach($values as $value){
				$final_values[] = $row->$value;
			}
			if ($key === false){
				$results[] = implode(' - ', $final_values);
			}else{
				$results[$row->$key] = implode(' - ', $final_values);
			}
		}
		return $results;
  }

}

/* End of file crud_model.php */
/* Location: ./application/models/crud_model.php */