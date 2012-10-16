<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Render html output
 */

function get_html_output($value, $output_type =  'str', $params = null){
	$ci =& get_instance();

	if (is_array($value)){
		$key = $value['key'];
		$value = $value['value'];
	}else{
		$key = null;
	}

	$final_value = nbs();

	// value is null?
	if ($value === null){
		return $final_value;
	}

	switch($output_type){
		case 'str':
			$final_value = $value;
			break;
		case 'int':
			$final_value = is_numeric($value) ? $value : 0;
			break;
		case 'date':
			if (is_valid_date($value) || is_valid_datetime($value)){
				$final_value = format_date($value, 'default_date_format') . '<span data-value="' . strtotime($value) . '"></span>';
			}else{
				$final_value = '<span data-value="0"></span>';
			}
			break;
		case 'datetime':
			if (is_valid_datetime($value)){
				$final_value = format_date($value, 'default_datetime_format') . '<span data-value="' . strtotime($value) . '"></span>';
			}else{
				$final_value = '<span data-value="0"></span>';
			}
			break;
		case 'month':
			$final_value = ucfirst(format_date($value, 'default_month_format')) . '<span data-value="' . strtotime($value) . '"></span>';
			break;
		case 'week':
			$time = strtotime($value);
			$str = format_date($value, 'default_day_month_format', true, '-6 days') . ' - ' . format_date($value, 'default_date_format');
			$final_value = $str . '<span data-value="' . $time . '"></span>';
			break;
		case 'day_year':
			$final_value = format_date($value, 'default_day_year_format') . '<span data-value="' . strtotime($value) . '"></span>';
			break;
		case 'currency':
			$round = isset($params['round']) ? $params['round'] : 2;
			$currency_value = isset($params['symbol']) && $params['symbol'] === false ? format_currency($value, false, 'default_currency_prefix', $round) : format_currency($value, true, 'default_currency_prefix', $round);
			if (is_numeric($value)){
				$final_value = $currency_value . '<span data-value="' . $value . '"></span>';
			}else{
				$final_value = $currency_value . '<span data-value="0"></span>';
			}
			break;
		case 'decimal':
			$final_value = is_numeric($value) ? format_decimal($value) : format_decimal(0) . '<span data-value="0"></span>';
			break;
		case 'blob':
			if (isset($params['blob_limit'])){
				$blob_limit = $params['blob_limit'];
			}else{
				$blob_limit = isset($params['details']) && $params['details'] === true ? $ci->config->item('default_details_blob_limit') : $ci->config->item('default_blob_limit');
			}
			$value = clean_html_text($value);
			$final_value = character_limiter($value, $blob_limit);
			break;
		case 'html':
			$value = htmlspecialchars($value);
			$final_value = '<pre class="pre-scrollable prettyprint">' . $value . '</pre>';
			break;
		case 'list':
			if (isset($params['list_size'])){
				$list_size = $params['list_size'];
			}else{
				$list_size = isset($params['details']) && $params['details'] === true ? $ci->config->item('default_details_list_limit') : $ci->config->item('default_list_limit');
			}

			if ($value != ''){
				$list = explode($ci->config->item('list_separator'), $value);
				if (sizeof($list) <= 1){
					$final_value = $value;
				}else{
					if (isset($params['details']) && $params['details'] === true){
						$final_value = '<div class="long-blob">' . ul($list, array('class' => 'unstyled')) . '</div>';
					}else{
						if (sizeof($list) > $list_size){
							$list = array_slice($list, 0, $list_size);
							$final_value = implode(', ', $list) . ', ...';
						}else{
							$final_value = implode(', ', $list);
						}
					}
				}
			}
			break;
		case 'image':
			$thumb_dir = '';
			if (isset($params['thumb_dir']) === true){
				$thumb_dir = $ci->config->item($params['thumb_dir']) !== false ? $ci->config->item($params['thumb_dir']) : $params['thumb_dir'];
			}
			$large_dir = false;
			if (isset($params['large_dir']) === true){
				$large_dir = $ci->config->item($params['large_dir']) !== false ? $ci->config->item($params['large_dir']) : $params['large_dir'];
			}
			$src = $thumb_dir . $value;
			if (is_file($src) || preg_match('/^http:/', $src)){
				$img_params = array(
					'src' => $src,
					'alt' => $value,
					'class' => 'img-polaroid img-rounded',
					'width' =>  isset($params['width']) ? $params['width'] : $ci->config->item('default_image_width'),
				);
				$img = img($img_params);

				if ($large_dir !== false && is_file($large_dir . $value)){
					$enlarge_img = $large_dir . $value;
					$final_value = anchor($enlarge_img, $img, array('class' => 'img-link enlarge', 'rel' => 'gallery'));
				}else if (isset($params['enlarge_suffix'])){
					$enlarge_img = str_replace('.jpg', $params['enlarge_suffix'] . '.jpg', $value);
					$final_value = internal_anchor($enlarge_img, $img, array('class' => 'img-link enlarge', 'rel' => 'gallery'));
				}else{
					$final_value = $img;
				}
			}
			break;
		case 'label':
			$color_classes = isset($params['color_classes']) ? $params['color_classes'] : $ci->config->item('default_color_classes');
			$class = isset($color_classes[$key]) ? $color_classes[$key] : '';
			$final_value = '<span class="label ' . $class . '">' . $value . '</span>';
			break;
		case 'icon':
			$icon_classes = isset($params['icon_classes']) ? $params['icon_classes'] : $ci->config->item('default_icon_classes');
			$final_value = isset($icon_classes[$key]) ? '<i class="' . $icon_classes[$key] . '"></i>' : '';
			break;
		case 'kb':
			if (is_numeric($value)){
				$final_value = byte_format($value * 1024, 0);
			}
			break;
		case 'url':
			if (!isset($params['details']) || $params['details'] === false){
				$url_label = isset($params['url_limiter']) && strlen($value) > $params['url_limiter'] ? substr($value, 0, $params['url_limiter']) . '...' : $value;				
			}else{
				$url_label = $value;
			}
			$final_value = anchor($value, $url_label, array('target' => '_blank'));
			break;
	}

	if (isset($params['func'])){
		$funcs = is_array($params['func']) ? $params['func'] : array($params['func']);
		foreach($funcs as $func){
			$final_value = call_user_func($func, $final_value);
		}
	}

	return $final_value;
}

/**
 * Render btn dropdown
 */

function render_btn_dropdown($label, $items, $params = array()){
  $ci =& get_instance();

  $active = isset($params['active']) ? $params['active'] : null;
  $classes = isset($params['right']) && $params['right'] === true ? array('dropdown-menu', 'pull-right') : array('dropdown-menu');

  $html = '';
  $html .= '<div class="btn-group">';
  $dropdown_classes = array('btn', 'dropdown-toggle');
  if (isset($params['class'])){
  	$dropdown_classes[] = $params['class'];
  }
  $anchor_params = array('class' => implode(' ', $dropdown_classes), 'data-toggle' => 'dropdown');
  if (isset($params['id'])){
  	$anchor_params['id'] = $params['id'];
  }
  $html .= internal_anchor('#', $label . ' <span class="caret"></span>', $anchor_params);

  $list = array();
  foreach($items as $key => $value){
    if ($key === $active){
      $list[] = li_ext($value, array('class' => 'active'));
    }else{
      $list[] = li_ext($value);
    }
  }
  $html .= ul_ext($list, array('class' => implode(' ', $classes)));
  $html .= '</div>';

  return $html;
}

/**
 * Render list menu
 */

function render_list_menu($items, $active = null, $prefix = false, $main_params = array()){
	$ci =& get_instance();
	$output = '';
	foreach($items as $key => $params){
		if (is_numeric($key)){
			$key = $params;
			$params = array();
		}

		if ($key === 'divider'){
			$output .= li_ext('', array('class' => 'divider'));
		}else if ($key === 'divider-vertical'){
			$output .= li_ext('', array('class' => 'divider-vertical'));
		}else{
			$li_params = array();
			$item_params = array();

			// anchor class
			if (isset($params['class'])){
				$item_params['class'] = $params['class'];
			}

			// prefix
			if ($prefix !== false){
				$item_params['id'] = $prefix . '-' . $key;
				$item_params['data-key'] = $key;
			}

			// tabs
			if (isset($main_params['tabs']) && $main_params['tabs'] === true){
				$item_params['data-toggle'] = 'tab';
			}

			// set label
			if (isset($params['label'])){
				$label = $ci->lang->line($params['label']) ? $ci->lang->line($params['label']) : $params['label'];
			}else{
				$label = $ci->lang->line($key) ? $ci->lang->line($key) : $key;
			}

			// icons
			if (isset($params['icon'])){
				$label = '<i class="' . $params['icon'] . '"></i>' . nbs(2) . $label;
			}

			// li classes
			if ($key === $active){
				$li_params['class'] = 'active';
			}

			if (isset($main_params['internal']) && $main_params['internal'] === true){
				$output .= li_ext(internal_anchor('#' . $key, $label, $item_params), $li_params);
			}else{
				$route = isset($params['route']) ? $params['route'] : $key;
				$output .= li_ext(anchor($route, $label, $item_params), $li_params);
			}
		}
	}

	return $output;
}

/**
 * Render button checkbox toolbar
 */

function button_checkbox_group($btns, $active = array(), $prefix = '', $main_params = array()){
	$ci =& get_instance();

	$classes = isset($main_params['classes']) ? $main_params['classes'] : array();
	$classes[] = 'btn-group';
	$output = '<div class="' . implode(' ', $classes) . '" data-toggle="buttons-checkbox">';

	foreach($btns as $key => $params){
		$classes = isset($params['classes']) ? $params['classes'] : array();
		if (in_array($key, $active)){
			$classes[] = 'active';
		}
		$btn_params = array(
			'id' => $prefix . '-' . $key,
			'data-key' => $key,
			'class' => 'btn ' . implode(' ', $classes),
		);

		// set label
		if (isset($params['label'])){
			$label = $ci->lang->line($params['label']) ? $ci->lang->line($params['label']) : $params['label'];
		}else{
			$label = $ci->lang->line($key) ? $ci->lang->line($key) : $key;
		}

		if (isset($params['icon']) && isset($main_params['no_label']) && $main_params['no_label'] === true){
			$btn_params['rel'] = 'tooltip';
			$btn_params['title'] = $label;
			$btn_params['data-placement'] = 'bottom';
			$label = '<i class="' . $params['icon'] . '"></i>';
		}else if (isset($params['icon'])){
			if (isset($main_params['responsive']) && $main_params['responsive'] === true){
				$label = '<i class="' . $params['icon'] . '"></i><span class="hidden-phone">' . nbs() . $label . '</span>';
			}else{
				$label = '<i class="' . $params['icon'] . '"></i>' . nbs() . $label;
			}
		}

		$btn_params['content'] = $label;
		$output .= form_button($btn_params);
	}
	$output .= '</div>';
	return $output;
}

/**
 * Render button radio toolbar
 */

function button_radio_group($btns, $active = array(), $prefix = '', $main_params = array()){
	$ci =& get_instance();

	$classes = isset($main_params['classes']) ? $main_params['classes'] : array();
	$classes[] = 'btn-group';

	$output = '<div class="' . implode(' ', $classes) . '" data-toggle="buttons-radio">';
	foreach($btns as $key => $params){
		$classes = array();
		if ($key === $active){
			$classes[] = 'active';
		}
		$btn_params = array(
			'id' => $prefix . '-' . $key,
			'data-key' => $key,
			'class' => 'btn ' . implode(' ', $classes),
		);

		// set label
		if (isset($params['label'])){
			$label = $ci->lang->line($params['label']) ? $ci->lang->line($params['label']) : $params['label'];
		}else{
			$label = $ci->lang->line($key) ? $ci->lang->line($key) : $key;
		}

		if (isset($params['icon']) && isset($main_params['no_label']) && $main_params['no_label'] === true){
			$btn_params['rel'] = 'tooltip';
			$btn_params['title'] = $label;
			$btn_params['data-placement'] = 'bottom';
			$label = '<i class="' . $params['icon'] . '"></i>';
		}else if (isset($params['icon'])){
			if (isset($main_params['responsive']) && $main_params['responsive'] === true){
				$label = '<i class="' . $params['icon'] . '"></i><span class="hidden-phone">' . nbs() . $label . '</span>';
			}else{
				$label = '<i class="' . $params['icon'] . '"></i>' . nbs() . $label;
			}
		}

		$btn_params['content'] = $label;
		$output .= form_button($btn_params);
	}
	$output .= '</div>';
	return $output;
}

/**
 * Render button anchor toolbar
 */

function button_anchor_group($btns, $active = array(), $main_params = array()){
	$ci =& get_instance();

	$classes = isset($main_params['classes']) ? $main_params['classes'] : array();
	$classes[] = 'btn-group';
	$output = '<div class="' . implode(' ', $classes) . '">';

	foreach($btns as $key => $params){
		$classes = isset($params['classes']) ? $params['classes'] : array();
		if (in_array($key, $active)){
			$classes[] = 'active';
		}
		$btn_params = array(
			'class' => 'btn ' . implode(' ', $classes),
		);

		// set label
		if (isset($params['label'])){
			$label = $ci->lang->line($params['label']) ? $ci->lang->line($params['label']) : $params['label'];
		}else{
			$label = $ci->lang->line($key) ? $ci->lang->line($key) : $key;
		}

		if (isset($params['icon']) && isset($main_params['no_label']) && $main_params['no_label'] === true){
			$btn_params['rel'] = 'tooltip';
			$btn_params['title'] = $label;
			$btn_params['data-placement'] = 'bottom';
			$label = '<i class="' . $params['icon'] . '"></i>';
		}else if (isset($params['icon'])){
			$label = '<i class="' . $params['icon'] . '"></i>' . nbs() . $label;
		}

		if (isset($params['route'])){
			$output .= anchor($params['route'], $label, $btn_params);
		}else{
			$btn_params['class'] .= ' no-click';
			$output .= internal_anchor('', $label, $btn_params);
		}
	}
	$output .= '</div>';
	return $output;
}

/**
 * Show count
 */

function count_format($value, $badge = false){
	if ($badge === false){
		return '<span class="count pull-right">' . $value . '</span>';
	}else{
		return '<span class="badge badge-' . $badge . ' pull-right">' . $value . '</span>';
	}
}

/**
 * Render tab panes
 */

function tab_panes($tabs, $active = null, $params = array()){
	$output = '';
	foreach($tabs as $id => $tab){
		$class = $id === $active ? ' active' : '';
		$output .= '<div class="tab-pane fade in' . $class . '" id="' . $id . '">';
		$output .= $tab;
		$output .= '</div>';
	}
	return $output;
}

/**
 * Encode JSON alternative
 */

function json_encode_special($obj_array){
	$rows = array();
	foreach($obj_array as $key => $row){
		if (is_string($row) && strpos($row, '@') === 0){
			$row = substr($row, 1);
			$rows[] = '"' . $key . '":' . $row;
		}else{
			$rows[] = '"' . $key . '":' . json_encode($row);
		}
	}
	$str = '{' . implode(',', $rows) . '}';
	return $str;
}

/**
 * PHP vars to JS vars
 */

function php_to_js($vars, $key = 'server_vars', $encode = true){
	$vars = $encode === true ? json_encode($vars) : $vars;
	return 'var ' . $key . ' = ' . $vars . ';';
}

function php_to_js_special($key, $value, $encode = true){
	if ($encode){
		$value = json_encode($value);
	}
	return  'var ' . $key . ' = ' . $value . ';' . PHP_EOL;
}

/**
 * CI Lang items to JS
 */

function lang_to_js($vars){
	$ci =& get_instance();
	$key_values = array();
	foreach($vars as $key => $value){
		$key_values[$value] = $ci->lang->line($value);
	}
	return 'var lang = ' . json_encode($key_values) . ';';
}

/**
 * Header elements
 */

function head_elements($meta = array(), $links = array()){
	$output = '';

	$output .= meta($meta);

	foreach($links as $link){
		$output .= link_tag($link) . PHP_EOL;
	}

	return $output;
}

/**
 * Table head row
 */

function th($values, $attributes = ''){
	$output = '';
	if ($attributes != ''){
		$attributes = _parse_attributes($attributes);
	}
	foreach($values as $value){
		$output .= '<th' . $attributes . '>' . $value . '</th>';
	}
	return $output;
}

/**
 * Table row
 */

function td($values, $attributes = ''){
	$output = '';
	if ($attributes != ''){
		$attributes = _parse_attributes($attributes);
	}
	foreach($values as $value){
		$output .= '<td' . $attributes . '>' . $value . '</td>';
	}
	return $output;
}

/**
 * UL alt
 */

function ul_ext($values, $attributes = ''){
	if ($attributes != ''){
		$attributes = _parse_attributes($attributes);
	}
	return '<ul' . $attributes . '>' . implode(' ', $values) . '</ul>';
}

/**
 * LI alt
 */

function li_ext($value, $attributes = ''){
	if ($attributes != ''){
		$attributes = _parse_attributes($attributes);
	}
	return '<li' . $attributes . '>' . $value . '</li>';
}

/**
 * Internal anchor
 */

function internal_anchor($uri = '', $title = '', $attributes = ''){
	$title = (string) $title;
	$site_url = $uri;
	if ($title == ''){
		$title = $site_url;
	}
	if ($attributes != ''){
		$attributes = _parse_attributes($attributes);
	}
	return '<a href="' . $site_url . '"' . $attributes . '>' . $title . '</a>';
}

/**
 * Array to attributes
 */

function array_to_attributes($params = null){
	$attributes = array();
	if (is_array($params)){
		foreach($params as $key => $value){
			$attributes[] = $key . '="' . $value . '"';
		}
		return implode(' ', $attributes);
	}else{
		return '';
	}
}

/**
 * remove html from text
 */

function clean_html_text($value, $keep_br = true){
	$value = nl2br($value);
	if ($keep_br){
		$value = strip_tags($value, '<br><b><strong><i><del>');
	}else{
		$value = strip_tags($value);
	}
	$value = strip_quotes($value);
	return $value;
}

/* End of file MY_html_helper.php */
/* Location: ./application/helpers/MY_html_helper.php */