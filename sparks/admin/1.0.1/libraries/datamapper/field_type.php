<?php

/**
 * Field_type Datamapper extension Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Field_type {

	private $default_actions = array(
	  'manage' => false,
	  'export' => true,
	  'details' => true,
	  'insert_form' => true,
		'insert_db' => true,
	  'update_form' => true,
		'update_db' => true,
	);

	private $ci = null;

	public function __construct(){
		$this->ci =& get_instance();
	}

	/**
	 * Field type init
	 */

	public function init_field_type($object){
		// id field visible?
		if (isset($object->id_visible) && $object->id_visible && !isset($object->validation['id']['type'])){
			$id = array(
				'id' => array(
					'label' => 'lang:id',
					'type' => 'int',
					'actions' => array('manage' => true, 'update_db' => false, 'insert' => false),
					'col_width' => 20,
					'input_params' => array('class' => 'span2'),
				),
			);
			$object->validation = array_merge_recursive($id, $object->validation);
		}

		// sort on
		if (!isset($object->sort_on)){
			$object->sort_on = false;
		}
		if (!isset($object->new_on_top)){
			$object->new_on_top = false;
		}
		if ($object->sort_on && !isset($object->validation['sort_id']['type'])){
			$sort_id = array(
				'sort_id' => array(
					'label' => 'lang:sort_id',
					'type' => 'int',
					'col_width' => 60,
					'actions' => array('manage' => true, 'details' => false, 'update' => false, 'insert_form' => false),
				),
			);
			$object->validation = array_merge_recursive($sort_id, $object->validation);
		}

		foreach($object->validation as $key => &$field){
			if (isset($field['type'])){
				// set labels
				$field['label'] = isset($field['label']) ? $field['label'] : 'lang:' . $key;

				// set actions
				$this->set_actions($field);

				// set manage fields
				if (isset($object->manage_fields)){
					foreach($object->manage_fields as $k => $v){
						if ($key === $v || $key === $k){
							$field['actions']['manage'] = true;
						}
						if ($key === $k){
							$field['col_width'] = $v;
						}
					}
				}

				// set rules
				$this->set_rules($field);

				// check for required fields
				if (in_array('required', $field['rules'])){
					$field['input_params']['required'] = true;
				}

				// set input values
				if (isset($field['values_array'])){
					if (is_array($field['values_array']) === false && $this->ci->config->item($field['values_array'])){
						$field['values_array'] = $this->ci->config->item($field['values_array']);
					}
				}else if ($field['type'] === 'bool'){
					$field['values_array'] = $this->ci->config->item('bool_options');
				}else{
					$field['values_array'] = null;
				}

				// set input type
				$this->set_input_type($field);

				// set output type
				$this->set_output_type($field);

				// set export type
				$this->set_export_type($field);

				// sort type
				if (!isset($field['sort'])){
					$field['sort'] = true;
				}

				// if has one, set label field
				if ($field['type'] === 'has_one' || $field['type'] === 'has_one_related'){
					$field['label_field'] = isset($field['label_field']) ? $field['label_field'] : 'title';
				}

				//$object->validation[$key] = $field;
			}
		}
	}

	/**
	 * Set actions
	 */

	private function set_actions(&$field){
		$actions = isset($field['actions']) ? $field['actions'] : array();
		$actions = array_merge($this->default_actions, $actions);

		if (isset($actions['insert'])){
			$actions['insert_form'] = $actions['insert'];
			$actions['insert_db'] = $actions['insert'];
		}
		if (isset($actions['update'])){
			$actions['update_form'] = $actions['update'];
			$actions['update_db'] = $actions['update'];
		}
		$field['actions'] = $actions;
	}

	/**
	 * Set rules
	 */

	private function set_rules(&$field){
		$rules = isset($field['rules']) ? $field['rules'] : array();
		$default_rules = array('trim');

		if (in_array('required', $rules)){
			$default_rules[] = 'required';
		}

		if (is_array($field['type'])){
			$field_type = key($field['type']);
			if (is_array($field['type'][$field_type])){
				$min = $field['type'][$field_type][0];
				$max = $field['type'][$field_type][1];
			}else{
				$size = $field['type'][$field_type];
			}
			$field['type'] = $field_type;
		}

		switch($field['type']){
			case 'str':
				$default_rules = array_merge($default_rules, array('strip_tags', 'max_length' => 255));
				break;
			case 'email':
				$default_rules = array_merge($default_rules, array('strip_tags', 'max_length' => 255, 'valid_email'));
				break;
			case 'url':
				$default_rules = array_merge($default_rules, array('strip_tags', 'prep_url', 'max_length' => 255, 'valid_url')); //'prep_url',
				break;
			case 'password':
				$default_rules = array_merge($default_rules, array());
				break;
			case 'blob':
				$default_rules = array_merge($default_rules, array('strip_tags', 'max_length' => 2000));
				break;
			case 'html':
				$default_rules = array_merge($default_rules, array('max_length' => 2000));
				break;
			case 'int':
				$default_rules = array_merge($default_rules, array('numeric', 'is_natural'));
				break;
			case 'currency':
			case 'decimal':
				$default_rules = array_merge($default_rules, array('comma_to_point', 'numeric', 'to_decimal' => 2));
				break;
			case 'date':
				$default_rules = array_merge($default_rules, array('valid_date', 'min_date' => '1950-01-01', 'to_date'));
				break;
			case 'datetime':
				$default_rules = array_merge($default_rules, array('valid_datetime', 'min_date' => '1950-01-01', 'to_datetime'));
				break;
			case 'bool':
				$default_rules = array_merge($default_rules, array('numeric', 'is_natural', 'min_size' => 1, 'max_size' => 2));
				break;
			case 'has_one':
			case 'has_one_related':
			case 'has_many':
			default:
				break;
		}

		switch($field['type']){
			case 'str':
			case 'email':
			case 'url':
			case 'password':
			case 'blob':
			case 'html':
				if (isset($size)){
					$default_rules['max_length'] = $size;
				}else if(isset($min)){
					$default_rules['min_length'] = $min;
					$default_rules['max_length'] = $max;
				}
				break;
			case 'int':
			case 'currency':
			case 'decimal':
				if (isset($size)){
					$default_rules['max_size'] = $size;
				}else if(isset($min)){
					$default_rules['min_size'] = $min;
					$default_rules['max_size'] = $max;
				}
				break;
			case 'date':
			case 'datetime':
				if (isset($size)){
					$default_rules['max_date'] = $size;
				}else if(isset($min)){
					$default_rules['min_date'] = $min;
					$default_rules['max_date'] = $max;
				}
				break;
			default:
				break;
		}

		$rules = array_merge($default_rules, $rules);
		$rules[] = 'xss_clean';
		$field['rules'] = $rules;
	}

	/**
	 * Set input type
	 */

	private function set_input_type(&$field){
		if (isset($field['input_type'])){
			return;
		}

		switch($field['type']){
			case 'str':
				$input_type = 'text';
				break;
			case 'email':
				$input_type = 'email';
				break;
			case 'url':
				$input_type = 'url';
				break;
			case 'password':
				$input_type = 'password';
				break;
			case 'blob':
				$input_type = 'textarea';
				break;
			case 'html':
				$input_type = 'html';
				break;
			case 'int':
				if ($field['values_array'] !== null){
					$input_type = 'dropdown';
					break;
				}
				$input_type = 'text';
				break;
			case 'currency':
				$input_type = 'currency';
				break;
			case 'decimal':
				$input_type = 'text';
				break;
			case 'date':
				$input_type = 'date';
				break;
			case 'datetime':
				$input_type = 'datetime';
				break;
			case 'bool':
				$input_type = 'bool';
				break;
			case 'has_one':
			case 'has_one_related':
				$input_type = 'dropdown';
				break;
			case 'has_many':
			default:
				$input_type = 'text';
				break;
		}

		$field['input_type'] = $input_type;
	}

	/**
	 * Set output type
	 */

	private function set_output_type(&$field){
		if (isset($field['output_type'])){
			return;
		}

		switch($field['type']){
			case 'str':
			case 'email':
				$output_type = 'str';
				break;
			case 'url':
				$output_type = 'url';
				break;
			case 'password':
				$output_type = 'str';
				break;
			case 'blob':
				$output_type = 'blob';
				break;
			case 'html':
				$output_type = 'blob';
				break;
			case 'int':
				if ($field['values_array'] !== null){
					$output_type = 'str';
					break;
				}
				$output_type = 'int';
				break;
			case 'currency':
				$output_type = 'currency';
				break;
			case 'decimal':
				$output_type = 'decimal';
				break;
			case 'date':
				$output_type = 'date';
				break;
			case 'datetime':
				$output_type = 'datetime';
				break;
			case 'bool':
				$output_type = 'str';
				break;
			case 'has_one':
			case 'has_one_related':
				$output_type = 'str';
				break;
			case 'has_many':
				$output_type = 'list';
				break;
			default:
				$output_type = 'str';
				break;
		}

		$field['output_type'] = $output_type;
	}

	/**
	 * Set export type
	 */

	private function set_export_type(&$field){
		if (isset($field['export_type'])){
			return;
		}

		switch($field['type']){
			case 'str':
			case 'email':
			case 'url':
			case 'password':
				$export_type = 'str';
				break;
			case 'blob':
			case 'html':
				$export_type = 'blob';
				break;
			case 'int':
				if ($field['values_array'] !== null){
					$export_type = 'str';
					break;
				}
				$export_type = 'int';
				break;
			case 'currency':
				$export_type = 'currency';
				break;
			case 'decimal':
				$export_type = 'decimal';
				break;
			case 'date':
				$export_type = 'date';
				break;
			case 'datetime':
				$export_type = 'datetime';
				break;
			case 'bool':
				$export_type = 'str';
				break;
			case 'has_one':
			case 'has_one_related':
				$export_type = 'str';
				break;
			case 'has_many':
				$export_type = 'list';
				break;
			default:
				$export_type = 'str';
				break;
		}

		$field['export_type'] = $export_type;
	}

	/**
	 * Data to object
	 */

	public function data_to_object($object, $fields, $type = 'post', $params = array()){
		foreach($fields as $field){
			switch($type){
				case 'post':
					$value = $this->ci->input->post($field);
					break;
				case 'get':
					$value = $this->ci->input->get($field);
					break;
				case 'array':
					$value = isset($params['data'][$field]) ? $params['data'][$field] : false;
					break;
			}
			if (!empty($value)){
      	$object->{$field} = $value;
      }else if (isset($params['force']) && $params['force'] === true){
      	$object->{$field} = null;
      }
		}
	}

	/**
	 * Get errors array
	 */

	public function errors_to_array($object, $fields){
		$errors = array();
		foreach($fields as $field){
			if (isset($object->error->$field)){
				$errors[$field] = $object->error->$field;
			}
		}
		return $errors;
	}

	/**
	 * Get field output
	 */

	public function get_field_output($object, $key, $details = false){
		$field = $object->validation[$key];

		if ($details === true && isset($field['output_params']['details_func']) && method_exists($object, $field['output_params']['details_func'])){
			return call_user_func(array($object, $field['output_params']['details_func']));
		}

		if ($details === false && isset($field['output_params']['manage_func']) && method_exists($object, $field['output_params']['manage_func'])){
			return call_user_func(array($object, $field['output_params']['manage_func']));
		}

		// get value
		switch($field['type']){
			case 'has_many':
				if ($details){
					$details_fields = isset($field['output_params']['details_fields']) ? $field['output_params']['details_fields'] : array('title');
					$class = $field['class'];
					$obj = $object
						->{$class}
						->select(implode(', ', $details_fields))
						->get();
					$values = array();
					foreach($obj as $o){
						$row_values = array();
						foreach($details_fields as $f){
							$row_values[] = $o->{$f};
						}
						$values[] = implode(' - ', $row_values);
					}
					$final_value = implode($this->ci->config->item('list_separator'), $values);
				}else{
					$final_value = $object->$key;
				}
				break;
			case 'has_one':
				if (is_array($field['label_field'])){
					$values = array();
					foreach($field['label_field'] as $label){
						$values[] = $object->$key->$label;
					}
					$value = implode(' - ', $values);
				}else{
					$value = $object->$key->$field['label_field'];
				}

				$final_value = array('key' => $object->$key->id, 'value' => $value);
				break;
			case 'has_one_related':
				$key_str = str_replace('/', '_', $key) . '_';
				$final_value = array('key' => $object->{$key_str . 'id'}, 'value' => $object->{$key_str . $field['label_field']});
				break;
			default:
				$value = $object->$key;
				// get value if array is set
				if (isset($field['values_array'][$value])){
					if (isset($field['input_params']['values_as_keys']) && $field['input_params']['values_as_keys'] === true){
						$final_value = $value;
					}else{
						$final_value = array('key' => $value, 'value' => $field['values_array'][$value]);
					}
				}else{
					$final_value = $value;
				}
				break;
		}

		return $final_value;
	}

	/**
	 * Validate except relationships
	 */

	public function validate_special($object){
		$valid = true;
		$clone = $object->get_clone();
		$clone->validate();
		if ($clone->valid){
			return true;
		}else{
			foreach($clone->validation as $key => $field){
				if (isset($field['type']) && $field['type'] !== 'has_one'){
					if ($clone->error->$key){
						$object->error_message($key, $clone->error->$key);
						$valid = false;
					}
				}
			}
			return $valid;
		}
	}

	/**
	 * Remove rules
	 */

	public function remove_rules($object, $field, $remove){
		$remove = is_array($remove) ? $remove : array($remove);
		$rules =& $object->validation[$field]['rules'];
		foreach($rules as $key => $rule){
			if (in_array($key, $remove) === true || in_array($rule, $remove) === true){
				unset($rules[$key]);
			}
		}
	}

	/**
	 * Remove rules
	 */

	public function add_rules($object, $field, $add){
		$add = is_array($add) ? $add : array($add);
		$rules =& $object->validation[$field]['rules'];
		$new_rules = array();
		foreach($add as $add_field){
			$new_rules[] = $add_field;
		}
		$rules = array_merge($new_rules, $rules);
	}

	/**
	 * Custom rules
	 */

	public function rule_related_has_one($object, $related_objects, $field){
		if (in_array('required', $object->validation[$field]['rules']) === true){
			$found = false;
			foreach($related_objects as $obj){
				if (strtolower(get_class($obj)) == $field){
					$found = true;
					break;
				}
			}
			return $found;
		}
		return true;
	}

	public function rule_to_date($object, $field){
		if (!empty($object->{$field})){
			$object->{$field} = format_date($object->{$field}, 'mysql_date_format');
		}
	}

	public function rule_to_datetime($object, $field){
		if (!empty($object->{$field})){
			$object->{$field} = format_date($object->{$field}, 'mysql_datetime_format');
		}
	}

	public function rule_to_decimal($object, $field, $param){
		if (!empty($object->{$field})){
			$object->{$field} = format_decimal($object->{$field}, false, $param);
		}
	}

	public function rule_to_code($object, $field, $param){
  	if (!is_array($param)){
			$param = array($param);
  	}
		if (!empty($object->{$field})){
  		foreach($param as $other_field){
				$object->{$other_field} = substr(url_title(convert_accented_characters($object->{$field}), 'dash', true), 0, 50);
  		}
		}
	}

  public function rule_conditional_required($object, $field, $param){
  	if (!is_array($param)){
			$param = array($param);
  	}
	  if (!empty($object->{$field})){
  		foreach($param as $other_field){
  			if (empty($object->{$other_field})){
  				return false;
  			}
  		}
	  }
  	return true;
  }

	public function rule_comma_to_point($object, $field, $param){
		if (!empty($object->{$field})){
			$object->{$field} = str_replace(',', '.', $object->{$field});
		}
	}

	public function rule_trim_emails($object, $field, $param){
		if (!empty($object->{$field})){
			$emails = explode(',', $object->{$field});
			foreach($emails as $key => $email){
				$emails[$key] = trim($email);
			}
			$object->{$field} = implode(', ', $emails);
		}
	}

	public function rule_trim_lines($object, $field, $param){
		if (!empty($object->{$field})){
			$lines = explode("\n", $object->{$field});
			$lines = array_map('trim', $lines);
			$object->{$field} = implode("\n", $lines);
		}
	}


	public function rule_lines_no_spaces($object, $field, $param){
		if (!empty($object->{$field})){
			$lines = explode("\n", $object->{$field});
			foreach($lines as $line){
				if (preg_match("/[\s]/", $line)){
					return false;
				}
			}
		}
		return true;
	}

}

/* End of file field_type.php */