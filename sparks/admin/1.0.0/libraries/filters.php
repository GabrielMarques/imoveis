<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filters Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Filters{

	public $filters = array();

	public function __construct() {
		$this->ci =& get_instance();
  }

	/**
	 * Reset session vars
	 */

	public function reset($keep_session = false){
		if ($keep_session === false){
			$this->ci->session->unset_userdata('filters');
			return false;
		}else{
			return $this->ci->session->userdata('filters');
		}
	}

	/**
	 * Set filters based on client params
	 */

	public function set(){
		$this->ci->load->library('form_validation');

		$set_filters = array();

		foreach($this->filters as $key => $filter){
			switch($filter['type']){
				case 'from_to':
					$from = $key . '_from';
					$to = $key . '_to';
					$from_value = $this->ci->input->get($from);
					$to_value = $this->ci->input->get($to);

					if (empty($from_value) === false || empty($to_value) === false){
						// set post
						$_POST[$from] = $from_value;
						$_POST[$to] = $to_value;

						// set validation rules
						$this->ci->form_validation->set_rules($from, $filter['label'], $filter['rules']);
						$this->ci->form_validation->set_rules($to, $filter['label'], $filter['rules']);

						$set_filters[$key] = array($from_value, $to_value);
					}
					break;
				case 'multiselect':
					$form_values = $this->ci->input->get($key);
					if (empty($form_values) === false && is_array($form_values)){
						$_POST[$key] = $form_values;
						$this->ci->form_validation->set_rules($key . '[]', $filter['label'], $filter['rules']);
						$set_filters[$key] = $form_values;
					}
					break;
				default:
					$form_value = $this->ci->input->get($key);
					if (empty($form_value) === false){
						$_POST[$key] = $form_value;
						$this->ci->form_validation->set_rules($key, $filter['label'], $filter['rules']);
						$set_filters[$key] = $form_value;
					}
					break;
			}
		}

		// validate
		if (sizeof($set_filters) > 0){
			if ($this->ci->form_validation->run() === false){
				$errors = array();
				foreach($set_filters as $key => $filter){
					switch($this->filters[$key]['type']){
						case 'from_to':
							if (form_error($key . '_from') || form_error($key . '_to')){
								$message = form_error($key . '_from') ? form_error($key . '_from') : form_error($key . '_to');
								$errors[] = array('field' => $key, 'message' => $message);
							}
							break;
						case 'multiselect':
							if (form_error($key . '[]')){
								$errors[] = array('field' => $key, 'message' => form_error($key . '[]'));
							}
							break;
						default:
							if (form_error($key)){
								$errors[] = array('field' => $key, 'message' => form_error($key));
							}
							break;
					}
				}
				return $errors;
			}

			// save in session
			$this->ci->session->set_userdata('filters', $set_filters);
		}else{
			$this->ci->session->unset_userdata('filters');
		}

//		print_r($set_filters);
//		exit;

		return true;
	}

	/**
	 * Load filter to page
	 */

	public function load($field, $type, $label = null, $params = null){
		// check values array
		switch($type){
			case 'menu':
			case 'dropdown':
			case 'multiselect':
				if (!isset($params['values_array'])){
					return false;
				}
				break;
		}

		$input_params = array();
		$from_to_type = isset($params['from_to_type']) ? $params['from_to_type'] : 'date';

		// filter types
		switch($type){
			case 'dropdown':
				$values_as_keys = isset($params['values_as_keys']) ? $params['values_as_keys'] : false;
				$rules = $values_as_keys === true ? 'trim|xss_clean' : 'trim|integer|max_length[100]|xss_clean';
				$input_type = 'dropdown';
				break;
			case 'multiselect':
				$values_as_keys = isset($params['values_as_keys']) ? $params['values_as_keys'] : false;
				$rules = $values_as_keys === true ? 'trim|xss_clean' : 'trim|integer|max_length[100]|xss_clean';
				$input_type = 'multiselect';
				break;
			case 'menu':
				$rules = 'required|trim|integer|max_length[100]|xss_clean';
				$input_type = 'menu';
				break;
			case 'search_area':
				$rules = 'trim|max_length[200]|xss_clean';
				$input_type = 'textarea';
				$input_params['rows'] = 2;
				break;
			case 'search':
				$rules = 'trim|max_length[100]|xss_clean';
				$input_type = 'text';
				if (isset($params['typeahead'])){
					$input_params['typeahead'] = $params['typeahead'];
				}
				break;
			case 'from_to':
				switch($from_to_type){
					case 'datetime':
						$rules = 'trim|valid_datetime|xss_clean';
						break;
					case 'number':
						$rules = 'trim|xss_clean';
						$input_params['number'] = true;
						break;
					case 'date':
						$rules = 'trim|valid_date|xss_clean';
						break;
				}
				$input_type = 'from_to';
				break;
		}

		// rules
		$rules = isset($params['rules']) ? $params['rules'] : $rules;

		// filter class
		$input_params['class'] = isset($params['class']) ? $params['class'] : 'input-block-level';

		// values
		$values_array = isset($params['values_array']) ? $params['values_array'] : false;

		// related
		$related = isset($params['related']) ? $params['related'] : false;
		$related_field = isset($params['related_field']) ? $params['related_field'] : 'id';

		// labels
		if ($label === null){
			$label = $this->ci->lang->line($field);
		}else{
			$label = $this->ci->lang->line($label) ? $this->ci->lang->line($label) : $label;
		}

		// array ready
		$array_ready = isset($params['array_ready']) ? $params['array_ready'] : false;

		$this->filters[$field] = array(
			'type' => $type,
			'label' => $label,
			'input_type' => $input_type,
			'input_params' => $input_params,
			'rules' => $rules,
			'related' => $related,
			'related_field' => $related_field,
			'values_array' => $values_array,
			'from_to_type' => $from_to_type,
			'array_ready' => $array_ready,
		);
	}

	/**
	 * Get filters and populate dropdowns
	 */

	public function get_fields(){
		$final_filters = array();

		foreach($this->filters as $key => &$filter){
			if ($filter['values_array'] !== false){
				$values = $this->populate_filter($key);

				if (sizeof($values) > 1){
					$filter['values_array'] = $values;
				}else{
					unset($filter);
					continue;
				}
			}
			$final_filters[$key] = array(
				'label' => $filter['label'],
				'type' => $filter['input_type'],
				'values_array' => $filter['values_array'],
			);
			$final_filters[$key] = array_merge($final_filters[$key], $filter['input_params']);
		}

		if (sizeof($final_filters) > 0){
			return $final_filters;
		}else{
			return false;
		}
	}

	/**
	 * Populate dropdowns
	 */

	private function populate_filter($key){
		$filter = $this->filters[$key];
		$values_array = $filter['values_array'];

		if ($filter['array_ready'] == true){
			return $values_array;
		}else	if (is_array($values_array)){
			return call_user_func_array(array($this->ci->crud, 'get_values_array'), $values_array);
		}else{
			return $this->ci->config->item($values_array);
		}
	}

	/**
	 * Get filters fields
	 */

	public function get_fields_js(){
		$final_filters = array();
		$session_filters = $this->ci->session->userdata('filters');

		foreach($this->filters as $key => $filter){
			switch($filter['type']){
	      case 'from_to':
					$from = $key . '_from';
					$to = $key . '_to';
					$final_filters[$from] = isset($session_filters[$from]) ? $session_filters[$from] : '';
					$final_filters[$to] = isset($session_filters[$to]) ? $session_filters[$to] : '';
	      	break;
	      default:
					$final_filters[$key] = isset($session_filters[$key]) ? $session_filters[$key] : '';
					break;
			}
		}

		return $final_filters;
	}

	/**
	 * Get info str
	 */

	public function get_info_str(){
		$final_filters = array();
		$session_filters = $this->ci->session->userdata('filters');

		foreach($this->filters as $key => $filter){
			if (isset($session_filters[$key])){
				switch($filter['type']){
		      case 'dropdown':
		      case 'menu':
						if ($filter['values_array'] !== false){
							$values = $this->populate_filter($key);
		          $final_filters[] = $filter['label'] . ' =  \'' . $values[$session_filters[$key]] . '\'';
						}
		        break;
		      case 'multiselect':
						if ($filter['values_array'] !== false){
							$values = $this->populate_filter($key);
							$filter_values = array();

							foreach($session_filters[$key] as $session_filter){
								$filter_values[] = $values[$session_filter];
							}

		          $final_filters[] = $filter['label'] . ' =  \'' . implode(', ', $filter_values) . '\'';
						}
		        break;
		      case 'from_to':
						$final_filters[] = $filter['label'] . ' =  \'' . $session_filters[$key][0] . ' - ' . $session_filters[$key][1] . '\'';
		      	break;
					default:
						$final_filters[] = $filter['label'] . ' =  \'' . $session_filters[$key] . '\'';
						break;
				}
			}
		}

		$search = $this->ci->session->userdata('search');
		if (empty($search) === false){
			$final_filters[] = $this->ci->lang->line('search') . ' =  \'' . $search . '\'';
		}

		if (sizeof($final_filters) > 0){
			return $this->ci->lang->line('filters') . ': ' . implode(', ', $final_filters);
		}else{
			return false;
		}
	}
}

/* End of file filters.php */
/* Location: ./application/libraries/filters.php */