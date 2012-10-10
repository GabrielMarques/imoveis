<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Build_form Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Build_form{

	private $ci;
	public $fields = false;
	public $errors = false;
	public $values = false;

	private $process = false;
	private $labels = true;
	private $display_oks = false;
	private $client = false;

	private $default_text_class = 'span4';
	private $default_select_class = 'span3';
	private $default_bool_class = 'span2';
	private $default_url_class = 'span4';
	private $default_currency_class = 'span2';
	private $default_textarea_rows = 6;
	private $default_textarea_class = 'span6';
	private $default_html_rows = 8;
	private $default_date_class = 'input-small';
	private $default_datetime_class = 'span2';

	public function __construct($params = false){
		$this->ci =& get_instance();

		if ($params !== false){
			$this->config($params);
		}
	}

	/**
	 * Config
	 */

	public function config($params){
		foreach($params as $key => $param){
			$this->{$key} = $param;
		}
	}

	/**
	 * Render multiple form elements
	 */

	public function render_elements($fields = false){
		if ($fields === false){
			$fields = $this->fields;
		}

		$output = '';
		foreach($fields as $field => $params){
			if ($params === 'divider'){
				$output .= '<hr />';
			}else{
				$output .= $this->render_element($field, $params);
			}
		}
		return $output;
	}

	/**
	 * Render details
	 */

	public function render_details($fields = false){
		if ($fields === false){
			$fields = $this->fields;
		}

		$output = '';
		foreach($fields as $field => $params){
			if ($params === 'divider'){
				$output .= '<hr />';
			}else{
				$output .= $this->render_detail($field, $params);
			}
		}
		return $output;
	}

	/**
	 * Render as fieldsets / tabs
	 */

	public function render_fieldsets($fieldsets, $active, $details = false){

		// merge fields
		$show = array();
		foreach($fieldsets as $key => $fieldset){
			$show[$key] = false;
			$fields = array();
			foreach($fieldset['fields'] as $field){
				if (isset($this->fields[$field])){
					$fields[$field] = $this->fields[$field];
					$show[$key] = true;
				}else if ($field === 'divider'){
					$fields[] = 'divider';
				}
			}
			unset($fieldsets[$key]['fields']);
			$fieldsets[$key]['fields'] = $fields;
		}

		$output = '';
		$output .= '<div class="tabbable tabs-left">';
		$output .= '<ul class="nav nav-tabs">';

		// tab menu
		$options = array();
		foreach($fieldsets as $key => $fieldset){
			if ($show[$key] === true){
				// load errors
				$errors = false;
				if ($this->errors !== false){
					foreach($fieldset['fields'] as $field_key => $field){
						if (isset($this->errors[$field_key]) && empty($this->errors[$field_key]) === false){
							$errors = true;
							break;
						}
					}
				}

				if ($errors === true){
					$options[$key] = array('label' => $fieldset['label'], 'icon' => 'icon-exclamation-sign', 'class' => 'alert-text-error');
				}else{
					$options[$key] = array('label' => $fieldset['label'], 'icon' => $fieldset['icon']);
				}
			}
		}

		$output .= render_list_menu($options, $active, false, array('internal' => true, 'tabs' => true));

		$output .= '</ul>';

		// tabs
		$output .= '<div class="tab-content">';
		$tabs = array();

		foreach($fieldsets as $key => $fieldset){
			if (sizeof($fieldset['fields']) > 0){
				$tabs[$key] = $details === true ? $this->render_details($fieldset['fields']) : $this->render_elements($fieldset['fields']);
			}
		}

		$output .=  tab_panes($tabs, $active);
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Render individual form element
	 */

	public function render_element($field, $params = array()){
		// vars
		$output = '';
		$field_classes = array('control-group');
		$error = false;

		// check for lang label
		$label = isset($params['label']) ? $params['label'] : $field;
		$label = $this->ci->lang->line($label) ? $this->ci->lang->line($label) : $label;

		// required
		if (isset($params['required']) && $params['required'] === true && $this->labels === true){
			$label = '* ' . $label;
		}

		// load errors
		if ($this->errors !== false && isset($this->errors[$field]) && empty($this->errors[$field]) === false){
			$error = $this->errors[$field];
			$params['error'] = true;
			$field_classes[] = 'error';
		}

		// output form elements
		$output .= '<div id="control-' . $field . '" class="' . implode(' ', $field_classes) . '">';
		if ($this->labels === true){
			$output .= form_label($label, $field, array('class' => 'control-label'));
		}
		$output .= '<div class="controls">';

		// input
		$output .= isset($params['html']) ? $params['html'] : $this->render_input($field, $label, $params);

		// display checks
		if ($error === false && $this->process !== false && $this->display_oks === true){
			if (isset($params['type']) && $params['type'] !== 'password'){
				$output .= '<span class="field-status"><i class="icon-ok"></i></span>';
			}
		}

		// output errors and help
		if ($this->client === false){
			if ($error !== false){
				$output .= '<p class="help-block">' . $error . '</p>';
			}else if (isset($params['help'])){
			//}else if ($this->errors === false && isset($params['help'])){
				$help = $this->ci->lang->line($params['help']) ? $this->ci->lang->line($params['help']) : $params['help'];
				$output .= '<p class="help-block">' . $help . '</p>';
			}
		}else{
			if ($error !== false){
				$output .= '<p class="help-block error-msg">' . $error . '</p>';
			}else{
				$output .= '<p class="help-block error-msg hide"></p>';
			}

			if (isset($params['help'])){
				$help = $this->ci->lang->line($params['help']) ? $this->ci->lang->line($params['help']) : $params['help'];
				if ($error !== false){
					$output .= '<p class="help-block help-msg hide">' . $help . '</p>';
				}else{
					$output .= '<p class="help-block help-msg">' . $help . '</p>';
				}
			}
		}

		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Render detail
	 */

	public function render_detail($field, $params = array()){
		// vars
		$output = '';
		$field_classes = array('control-group');

		// check for lang label
		$label = isset($params['label']) ? $params['label'] : $field;
		$label = $this->ci->lang->line($label) ? $this->ci->lang->line($label) : $label;

		// output form elements
		$output .= '<div id="control-' . $field . '" class="' . implode(' ', $field_classes) . '">';
		$output .= form_label($label, $field, array('class' => 'control-label'));
		$output .= '<div class="controls">';

		// input
		$output .= $this->values !== false && isset($this->values[$field]) ? $this->values[$field] : '';

		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Render input
	 */

	public function render_input($field, $label, $params = array()){
		// get db value or post value
		$value = isset($params['value']) ? $params['value'] : $this->get_input_value($field);

		// if password set value as null
		if (isset($params['type']) && $params['type'] === 'password'){
			$value = null;
		}

		// input params
		$params['type'] = isset($params['type']) ? $params['type'] : 'text';
		$params['values_array'] = isset($params['values_array']) ? $params['values_array'] : false;

		// set input attributes
		$default_class = $this->get_default_input_class($params['type']);
		$class = isset($params['class']) ? $params['class'] : $default_class;
		$class = is_array($class) ? $class : array($class);

		// check for errors
		if (isset($params['error'])){
			$class[] = 'error';
		}

		// default value
		if (isset($params['default_value']) && $value === null){
			$value = $params['default_value'];
		}

		// main attributes
		$attributes = array('name' => $field, 'value' => $value, 'class' => implode(' ', $class));
		if (isset($params['id'])){
			$attributes['id'] = $params['id'];
		}
		if (isset($params['disabled']) && $params['disabled'] === true){
			$attributes['disabled'] = true;
		}
		if (isset($params['maxlength'])){
			$attributes['maxlength'] = $params['maxlength'];
		}

		// placeholders
		$placeholder = isset($params['placeholder']) ? $params['placeholder'] : false;
		$placeholder = $placeholder === true ? $label : $placeholder;
		$placeholder = $this->ci->lang->line($placeholder) ? $this->ci->lang->line($placeholder) : $placeholder;
		if ($placeholder !== false){
			$attributes['placeholder'] = $placeholder;
		}

		// tooltips
		$tooltip = isset($params['tooltip']) ? $params['tooltip'] : false;
		$tooltip = $this->ci->lang->line($tooltip) ? $this->ci->lang->line($tooltip) : $tooltip;
		if ($tooltip !== false){
			$attributes['rel'] = 'tooltip';
			$attributes['title'] = $tooltip;
			$attributes['data-placement'] = 'right';
		}

		// finally, render input
		switch($params['type']){
			case 'text':
				// typeaheads
				if (isset($params['typeahead'])){
					$attributes['data-provide'] = 'typeahead';
					$attributes['data-items'] = 5;
					$attributes['autocomplete'] = 'off';
					$extra = "data-source='" .  json_encode($params['typeahead']) . "'";
				}else{
					$extra = null;
				}
				return form_input($attributes, null, $extra);
			case 'email':
				return '<div class="input-prepend"><span class="add-on"><i class="icon-envelope"></i></span>' . form_input($attributes) . '</div>';
			case 'currency':
				return '<div class="input-prepend"><span class="add-on">' . $this->ci->config->item('default_currency_prefix') . '</span>' . form_input($attributes) . '</div>';
			case 'percent':
				return '<div class="input-prepend"><span class="add-on">%</span>' . form_input($attributes) . '</div>';
			case 'url':
				return form_input($attributes);
			case 'password':
				$output = form_password($attributes);
				if (isset($params['score']) && $params['score']){
					$output .= '<span id="' . $field . '-score"></span>';
				}
				return $output;
			case 'file':
				return form_upload($attributes);
			case 'bool':
			case 'dropdown':
				$options_list = array();
				$values_as_keys = isset($params['values_as_keys']) ? $params['values_as_keys'] : false;
				if (!isset($params['required']) || $params['required'] === false) {
					$options_list[] = $this->ci->lang->line('none');
				}else{
					$options_list[] = $this->ci->lang->line('select');
				}
				if ($params['values_array']){
					foreach ($params['values_array'] as $opt_key => $opt_value) {
						if ($values_as_keys){
							$options_list[$opt_value] = $opt_value;
						}else{
							$options_list[$opt_key] = $opt_value;
						}
					}
				}
				if (isset($params['prepend_checkbox']) && $params['prepend_checkbox'] === true){
					if ($this->ci->input->post($field . '_check') == 1){
						$checked = true;
					}else{
						$attributes['disabled'] = true;
						$checked = false;
					}
					$attr_str = array_to_attributes($attributes);
					return '<div class="input-prepend">' .
						'<span class="add-on">' . form_checkbox(array('name' => $field . '_check', 'value' => 1, 'class' => 'input_check', 'data-target' => $field, 'checked' => $checked)) . '</span>' .
						form_dropdown($field, $options_list, $attributes['value'], $attr_str) .
						'</div>';
				}else{
					$attr_str = array_to_attributes($attributes);
					return form_dropdown($field, $options_list, $attributes['value'], $attr_str);
				}
			case 'multiselect':
				$options_list = array();
				$values_as_keys = isset($params['values_as_keys']) ? $params['values_as_keys'] : false;
				if ($params['values_array']){
					foreach ($params['values_array'] as $opt_key => $opt_value) {
						if ($values_as_keys){
							$options_list[$opt_value] = $opt_value;
						}else{
							$options_list[$opt_key] = $opt_value;
						}
					}
				}
				$attr_str = array_to_attributes($attributes);
				return form_multiselect($field, $options_list, $attributes['value'], $attr_str);
			case 'radio':
				$radio_list = array();
				if (!isset($params['required']) || $params['required'] === false) {
					$radio_list[] = $this->ci->lang->line('none');
				}
				if ($params['values_array']){
					foreach ($params['values_array'] as $rad_key => $rad_value) {
						$rad_label = $this->ci->lang->line($rad_value) ? $this->ci->lang->line($rad_value) : $rad_value;
						$radio_list[$rad_key] = $rad_label;
					}
				}
				$radio = '';
				foreach($radio_list as $rad_key => $rad_value){
					$rad_attr = array('name' => $field, 'value' => $rad_key);
					if ((int) $attributes['value'] == (int) $rad_key){
						$rad_attr['checked'] = true;
					}
					if (isset($params['disabled']) && $params['disabled'] === true){
						$rad_attr['disabled'] = true;
					}
					$radio .= '<label class="radio">' . form_radio($rad_attr) . $rad_value . '</label>';
				}
				return $radio;
			case 'textarea':
				$attributes['rows'] = isset($params['rows']) ? $params['rows'] : $this->default_textarea_rows;
				if (isset($params['maxlength'])){
					$attributes['class'] .= ' count-chars';
				}
				$output = form_textarea($attributes);
				$output .= '<span class="label hide"></span>';

				return $output;
			case 'html':
				$attributes['rows'] = isset($params['rows']) ? $params['rows'] : $this->default_html_rows;
				if (isset($params['maxlength'])){
					$attributes['class'] .= ' count-chars';
				}
				$attributes['class'] .= ' html-input';

				$output = form_textarea($attributes);
				$output .= '<span class="label hide"></span>';

				return $output;
			case 'date':
				$class[] = 'datepicker';
				$attributes['class'] = implode(' ', $class);
				$values_as_keys = isset($params['values_as_keys']) ? $params['values_as_keys'] : false;
				return '<div class="input-prepend"><span class="add-on"><i class="icon-calendar"></i></span>' . form_input($attributes) . '</div>';
			case 'datetime':
				return '<div class="input-prepend"><span class="add-on"><i class="icon-calendar"></i></span>' . form_input($attributes) . '</div>';
			case 'from_to':
				$from = $field . '_from';
				$to = $field . '_to';

				if (isset($params['number']) === false || $params['number'] === false){
					$class[] = 'datepicker';
				}

				$value_from = isset($params['default_value_from']) ? $params['default_value_from'] : null;
				$value_to = isset($params['default_value_to']) ? $params['default_value_to'] : null;
				$from_params = array('name' => $from, 'value' => set_value($from, $value_from), 'class' => implode(' ', $class), 'placeholder' => ucfirst($this->ci->lang->line('from')));
				$to_params = array('name' => $to, 'value' => set_value($to, $value_to), 'class' => implode(' ', $class), 'placeholder' => ucfirst($this->ci->lang->line('to')));
				$output = form_input($from_params) . form_input($to_params);
				return $output;
		}

		return '';
	}

	/**
	 * Render captcha
	 */

	public function render_captcha(){
		$output = '';

		if ($this->display_captcha === true){
			$output .= recaptcha_get_html($this->ci->config->item('recaptcha_public'));
			if (isset($this->errors['captcha'])){
				$output .= '<p class="error help-block">' . $this->errors['captcha'] . '</p>';
			}else{
				$output .= '<p class="error help-block">' . $this->ci->lang->line('captcha_help') . '</p>';
			}
			$output .= br();
		}

		return $output;
	}

	/**
	 * Get input post or db value
	 */

	public function get_input_value($field, $skip_post = false){
		// db values set?
		$value = $this->values !== false && isset($this->values[$field]) ? $this->values[$field] : null;

		// post value set?
		//if ($this->errors !== false && $skip_post === false && $this->ci->input->post($field) !== false){
		if ($skip_post === false && $this->ci->input->post($field) !== false){
			$value = $this->ci->input->post($field);
		}

		return $value;
	}

	/**
	 * Get default input class
	 */

	private function get_default_input_class($type){
		switch($type){
			case 'url':
				return $this->default_url_class;
				break;
			case 'currency':
				return $this->default_currency_class;
				break;
			case 'textarea':
			case 'html':
				return $this->default_textarea_class;
				break;
			case 'from_to':
			case 'date':
				return $this->default_date_class;
				break;
			case 'datetime':
				return $this->default_datetime_class;
				break;
			case 'bool':
				return $this->default_bool_class;
				break;
			case 'dropdown':
				return $this->default_select_class;
				break;
			default:
				return $this->default_text_class;
				break;
		}
	}

}

/* End of file build_form.php */
/* Location: ./application/libraries/build_form.php */