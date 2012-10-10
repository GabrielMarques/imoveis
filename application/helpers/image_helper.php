<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Resize image
 */

function resize_image($source_img, $width = false, $height = false, $new_img = false, $master_dim = 'height', $maintain_ratio = true){
	$ci =& get_instance();

	$ci->load->library('image_lib');
	$config = array(
	  'image_library' => 'gd2',
	  'source_image' => $source_img,
	  'maintain_ratio' => $maintain_ratio,
	  'master_dim' => $master_dim,
	);

	if ($width !== false){
		$config['width'] = $width;
	}
	if ($height !== false){
		$config['height'] = $height;
	}
	if ($new_img !== false){
		$config['new_image'] = $new_img;
	}

	$ci->image_lib->initialize($config);
	$ci->image_lib->resize();
	$ci->image_lib->clear();
}

/**
 * Crop image
 */

function crop_image($source_img, $width, $height, $x_axis = 0, $y_axis = 0, $new_img = false, $maintain_ratio = false){
	$ci =& get_instance();

	$ci->load->library('image_lib');
	$config = array(
	  'image_library' => 'gd2',
	  'source_image' => $source_img,
		'maintain_ratio' => $maintain_ratio,
	  'width' => $width,
	  'height' => $height,
	  'x_axis' => $x_axis,
	  'y_axis' => $y_axis,
	);

	if ($new_img !== false){
		$config['new_image'] = $new_img;
	}

	$ci->image_lib->initialize($config);
	$ci->image_lib->crop();
	$ci->image_lib->clear();
}

/**
 * Watermark
 */

function watermark_image($source_img, $text, $font_path, $params = array()){
	$ci =& get_instance();

	$font_size = isset($params['font_size']) ? $params['font_size'] : 11;
	$font_color = isset($params['font_color']) ? $params['font_color'] : 'FFFFFF';
	$vrt_alignment = isset($params['vrt_alignment']) ? $params['vrt_alignment'] : 'bottom';
	$hor_alignment = isset($params['hor_alignment']) ? $params['hor_alignment'] : 'right';
	$vrt_offset = isset($params['vrt_offset']) ? $params['vrt_offset'] : -20;
	$hor_offset = isset($params['hor_offset']) ? $params['hor_offset'] : -20;

	$ci->load->library('image_lib');
	$config = array(
	  'image_library' => 'gd2',
	  'source_image' => $source_img,
	  'wm_text' => $text,
	  'type' => 'text',
		'wm_font_path' => $font_path,
		'wm_font_size' => $font_size,
		'wm_font_color' => $font_color,
		'wm_vrt_alignment' => $vrt_alignment,
		'wm_hor_alignment' => $hor_alignment,
		'wm_vrt_offset' => $vrt_offset,
		'wm_hor_offset' => $hor_offset,
	);

	$ci->image_lib->initialize($config);
	$ci->image_lib->watermark();
	$ci->image_lib->clear();
}

/**
 * Save resize versions
 */

function save_resize_versions($src, $dst_file_name, $dst_dir, $resize_dirs){
	list($width, $height) = getimagesize($src);
	foreach($resize_dirs as $dir => $params){
		create_dir($dst_dir . $dir);
		$dst = $dst_dir . $dir . '/' . $dst_file_name;
		if ($height/$width > .75){
			// if tall image
			resize_image($src, $params['width'], $params['height'], $dst, 'width');
			if (isset($params['crop']) === true && $params['crop'] === true){
				crop_image($dst, $params['width'], $params['height']);
			}
		}else if ($height/$width < .75) {
			// wide image
			if (isset($params['crop']) === true && $params['crop'] === true){
				$new_width = $height / .75;
				$x = -($new_width - $width) / 2;
				crop_image($src, $new_width, $height, $x, 0, $dst);
				resize_image($dst, $params['width'], $params['height'], false, 'width');
			}else{
				resize_image($src, $params['width'], $params['height'], $dst, 'width');
			}
		}else{
			// 4:3
			resize_image($src, $params['width'], $params['height'], $dst, 'width');
		}
	}
}

function is_image(){
	// IE will sometimes return odd mime-types during upload, so here we just standardize all
	// jpegs or pngs to the same file type.

	$png_mimes  = array('image/x-png');
	$jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

	if (in_array($this->file_type, $png_mimes))
	{
		$this->file_type = 'image/png';
	}

	if (in_array($this->file_type, $jpeg_mimes))
	{
		$this->file_type = 'image/jpeg';
	}

	$img_mimes = array(
		'image/gif',
		'image/jpeg',
		'image/png',
	);

	return (in_array($this->file_type, $img_mimes, TRUE)) ? TRUE : FALSE;
}

/* End of file image_helper.php */
/* Location: ./application/helpers/image_helper.php */