<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Post to var
 */

function post_to_var($field, $full_check = false){
	$ci =& get_instance();
	$value = $ci->input->post($field);
	if ($full_check === true){
		if ($value == 0){
			return null;
		}
	}
	return $value !== false ? $value : null;
}

/**
 * String funcs
 */

function to_alphanum($value, $length = 100){
	$value = trim(convert_accented_characters($value));
	$value = str_replace(' ', '', $value);
	$value = preg_replace("/[^a-zA-Z0-9\s]/", "", $value);
	$value = substr($value, 0, $length);
	return $value;
}

function batch_replace($replace_array, $str){
	foreach($replace_array as $search => $replace){
		$str = str_replace($search, $replace, $str);
	}
	return $str;
}

function set_config_labels($item, $sub_item = false){
	$ci =& get_instance();
	$options = $ci->config->item($item);

	if (is_array($options)){
		foreach ($options as &$option) {
			if ($sub_item === false){
				$option = $ci->lang->line($option) !== false ? $ci->lang->line($option) : $option;
			}else{
				if (isset($option[$sub_item])){
					$option[$sub_item] = $ci->lang->line($option[$sub_item]) !== false ? $ci->lang->line($option[$sub_item]) : $option[$sub_item];
				}
			}
		}
		$ci->config->set_item($item, $options);
	}
}

function str_to_emails($str){
	$final_emails = array();
	$emails = explode(',', $str);
	foreach($emails as $email){
		$email = trim($email);
		if (valid_email($email)){
			$final_emails[] = $email;
		}
	}
	return $final_emails;
}

function special_trim($value){
	//$value = str_replace(array("\r\n", "\n", "\r"), '', $value);
	$value = str_replace('&nbsp;', '', $value);
	$value = trim($value);
	return $value;
}

/**
 * Array funcs
 */

function array_key_sort($a, $sub_key, $reverse) {
	foreach($a as $k => $v) {
		$b[$k] = $v[$sub_key];
	}
	if ($reverse){
		arsort($b);
	}else{
		asort($b);
	}
	foreach($b as $key => $val) {
		$c[] = $a[$key];
	}
	return $c;
}

function array_to_object($array = array()) {
	if (!empty($array)) {
		$data = false;
		foreach ($array as $key => $val) {
			$data->{$key} = $val;
		}
		return $data;
	}
	return false;
}

function array_add_elements($array, $new_elements, $offset) {
	return array_slice($array, 0, $offset, true) + $new_elements + array_slice($array, $offset, null, true);
}

function utf8_encode_array(&$item, $key){
	$item = utf8_encode($item);
}

/**
 * Format && validation funcs
 */

function format_currency($value, $add_symbol = true, $currency_symbol = 'default_currency_prefix'){
	$ci =& get_instance();
	if ($add_symbol === true){
		return $ci->config->item($currency_symbol) . ' ' . number_format($value, 2, ',', '.');
	}else{
		return number_format($value, 2, ',', '.');
	}
}

function format_decimal($value, $use_comma = true, $places = 2){
	if ($use_comma){
		return number_format($value, $places, ',', '');
	}else{
		return number_format($value, $places, '.', '');
	}
}

function is_valid_url($value){
	return filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
}

/**
 * Date funcs
 */

function format_date($value, $format, $convert_time = true, $add = false){
	$ci =& get_instance();
	if ($ci->config->item($format)){
		$format = $ci->config->item($format);
	}
	$value = $convert_time === true ? strtotime($value) : $value;
	if ($add === false){
		return strftime($format, $value);
	}else{
		return strftime($format, strtotime($add, $value));
	}
}


function get_locale_month($time = false){
	$month = $time === false ? strftime('%B') : strftime('%B', $time);
	return ucfirst(convert_accented_characters(utf8_encode($month)));
}

function get_utc_date($value){
	date_default_timezone_set('UTC');
	return (strtotime($value) * 1000) - (strtotime('01-01-1970 00:00:00') * 1000);
}

function is_valid_date($value){
	$date = date_parse($value);
	return checkdate($date['month'], $date['day'],$date['year']);
}

function is_valid_datetime($value){
	if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value, $matches)) {
		if (checkdate($matches[2], $matches[3], $matches[1])) {
			return true;
		}
	}
	return false;
}

function time_ago($date){
	$ci =& get_instance();

	$date = strtotime($date);
	$stf = 0;
	$cur_time = time();
	$diff = $cur_time - $date;
	$phrase = array('s', 'm', 'h', 'd', 'w', 'm', 'y');
	$length = array(1 ,60, 3600, 86400, 604800, 2630880, 31570560);

	for($i = sizeof($length)-1; ($i >= 0) && (($no = $diff/$length[$i]) <= 1); $i--){
		if ($i < 0) {
			$i = 0;
		}
	}
	$_time = $cur_time - ($diff % $length[$i]);
	$no = floor($no);
	if ($no <> 1) {
		$phrase[$i] .='';
	}
	$value = sprintf("%d %s ", $no, $phrase[$i]);

	if (($stf == 1) && ($i >= 1) && (($cur_tm - $_time) > 0)) {
		$value .= time_ago($_time);
	}

	return $value;
}

/**
 * Etc
 */

function is_valid_isbn($isbn_number){
  $isbn_digits = array_filter(preg_split('//', $isbn_number, -1, PREG_SPLIT_NO_EMPTY), '_is_numeric_or_x');
  $isbn_length = count($isbn_digits);
  $isbn_sum = 0;

  if((10 != $isbn_length) && (13 != $isbn_length)){
  	return false;
  }

  if(10 == $isbn_length){
    foreach(range(1, 9) as $weight){
    	$isbn_sum += $weight * array_shift($isbn_digits);
    }
    return (10 == ($isbn_mod = ($isbn_sum % 11))) ? ('x' == mb_strtolower(array_shift($isbn_digits), 'UTF-8')) : ($isbn_mod == array_shift($isbn_digits));
  }

  if(13 == $isbn_length){
    foreach(array(1, 3, 1, 3, 1, 3, 1, 3, 1, 3, 1, 3) as $weight){
    	$isbn_sum += $weight * array_shift($isbn_digits);
    }
    return (0 == ($isbn_mod = ($isbn_sum % 10))) ? (0 == array_shift($isbn_digits)) : ($isbn_mod == (10 - array_shift($isbn_digits)));
  }

  return false;
}

function _is_numeric_or_x($val){
	return ('x' == mb_strtolower($val, 'UTF-8')) ? true : is_numeric($val);
}

/* End of file global_helper.php */
/* Location: ./application/helpers/global_helper.php */