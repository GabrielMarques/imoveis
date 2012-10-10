<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * DB_Files Model Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class DB_files_model extends MY_Model {

  public $db_class;
	public $dir;

	public $ftp_dropbox = false;
  public $extensions = array('jpg');
  public $max_size = 1024;
  public $max_fetch_size = 1024;
  public $resize = false;
  public $resize_dirs = array();
  public $display_images = false;
	public $settings_on = false;
	public $min_resize = 'width';

  public function __construct($params = false){
  	parent::__construct();
  }

  /**
	 * Init
	 */

	public function init($params = false) {
		// config
		foreach($params as $key => $value){
			$this->$key = $value;
		}

		if ($this->resize === true){
			$this->resize_dirs = $this->config->item('resize_dirs');
		}

  }

	/**
	 * Upload files
	 */

  public function process_upload($ftp_file = false){
  	$this->load->helper('file');
  	$this->load->helper('image');

		// create dirs if it doesn´t exist
  	create_dir($this->dir);
  	$dir = $this->dir;

  	if ($this->resize === true){
  		$top_dir = key($this->resize_dirs);
  		$dir = $dir . $top_dir;
  		create_dir($dir);
    }

    if ($ftp_file === false){
    	// http upload
	  	$config = array(
	      'upload_path' => $this->dir,
	      'allowed_types' => implode('|', $this->extensions),
	      'max_size' => $this->max_size,
	    	'max_filename' => 255,
				//'remove_spaces' => true,
	    	'encrypt_name' => true,
	    );
	    if ($this->resize === true){
	  		$config['upload_path'] = $dir;
	  		$config['min_' . $this->min_resize] = $this->resize_dirs[$top_dir][$this->min_resize];
	    }

	    $this->load->library('upload', $config);

			if ($this->upload->do_upload('userfile') === false){
				return $this->upload->display_errors(null, null);
			}

			// save to db
			$data = $this->upload->data();

    }else{
    	//XXX check if is image
    	// ftp
    	$file_path = $this->ftp_dropbox . $ftp_file;
    	$file_info = get_file_info($file_path, array('size'));

    	if ($file_info === false){
    		return $this->lang->line('error_file_not_found');
    	}

    	// validate file size
    	if (!isset($file_info['size']) || $file_info['size'] > $this->fetch_max_size * 1024){
    		return $this->lang->line('error_file_size');
    	}

    	// validate file dimensions
    	if ($this->resize === true){
	    	list($width, $height) = getimagesize($file_path);
	    	if ($this->min_resize === 'width' && $width < $this->resize_dirs[$top_dir]['width']){
		    	return $this->lang->line('error_invalid_dimensions');
	    	}else if ($this->min_resize === 'height' && $height < $this->resize_dirs[$top_dir]['height']){
	    		return $this->lang->line('error_invalid_dimensions');
	    	}
    	}

    	// save file
    	$extension = get_file_extension($ftp_file);
    	$unique_file_name = random_string('unique') . '.' . $extension;
    	copy_file($file_path, $dir . '/' . $unique_file_name);

    	$data = array(
				'client_name' => $ftp_file,
				'file_name' => $unique_file_name,
				'file_ext' => $extension,
				'file_size' => round($file_info['size']/1024, 2),
    	);

    	if ($this->resize === true){
				$data['is_image'] = true;
				$data['image_width'] = $width;
				$data['image_height'] = $height;
    	}
    }

    // create obj
		$class = $this->db_class;
		$file = new $class();

		$file->title = $data['client_name'];
		$file->code = $data['file_name'];
		$file->extension = ltrim($data['file_ext'], '.');
		$file->size = (int) $data['file_size'];

  	// post save hook
		if (isset($file->save_hook_on) && $file->save_hook_on === true){
			$success = $file->_save_hook();
		}else{
			$success = $file->save();
		}

		if ($success === true){
			// resize images
			if ($this->resize === true && $data['is_image'] === true){
				$this->resize_image($data['file_name'], $data['image_width'], $data['image_height'], $this->min_resize);
			}
			return true;
		}else{
			// delete file
			delete_file($dir . $data['file_name']);
			return $file->errors->string;
		}

  }

	/**
	 * Fetch from FTP
	 */

	public function fetch_from_ftp(){
		$this->load->helper('file');
		$errors = array();

		if (is_dir($this->ftp_dropbox)){
			$ftp_files = get_filenames_by_extension($this->ftp_dropbox, $this->extensions);
			if (sizeof($ftp_files) === 0){
				return $this->lang->line('error_no_files_to_fetch');
			}

			foreach($ftp_files as $file){
				$success = $this->process_upload($file);
				if ($success !== true){
					$errors[] = '<strong>' . $file . '</strong>' . ' - ' . $success;
				}
			}

			if (sizeof($errors) > 0){
				return $this->lang->line('error_upload') . br() . implode(br(), $errors);
			}
			return true;
		}else{
			return $this->lang->line('error_ftp_not_found');
		}
	}

	/**
	 * Resize images
	 */

	public function resize_image($file_name, $orig_width, $orig_height, $min_resize){
		$this->load->helper('image');

		$key = key($this->resize_dirs);
		$src = $this->dir . $key . '/' . $file_name;

		// resize src
		resize_image($src, $this->resize_dirs[$key]['width'], $this->resize_dirs[$key]['height'], false, $min_resize);
		$resize_dirs = array_slice($this->resize_dirs, 1);

		// batch resize
		save_resize_versions($src, $file_name, $this->dir, $resize_dirs);

		if ($this->settings_on === true){
			$settings = new Setting();
			$settings->get_by_id(1);
			if ((int) $settings->watermark_on === 2){
				foreach($this->resize_dirs as $dir => $params){
					if (isset($params['watermark']) && $params['watermark'] === true){
						$dst = $this->dir . $dir . '/' . $file_name;
						watermark_image($dst, $settings->watermark_text, $this->config->item('watermark_font'));
					}
				}
			}
		}
	}

	/**
	 * Delete files
	 */

  public function delete_files($files){
  	$this->load->helper('file');

		foreach($files as $file){
			if ($this->resize === true){
	  		foreach($this->resize_dirs as $dir => $params){
					delete_file($this->dir . $dir . '/' . $file['code']);
	  		}
			}else{
				delete_file($this->dir . $file['code']);
			}
		}

		return true;
  }

	/**
	 * Get file path
	 */

  public function get_file($id){
  	// create obj
		$file =& $this->crud->object;

		// set restrictions
		$this->crud->set_restrictions();

		$file
			->where('id', $id)
			->get();

		if ($file->exists() === true){
			if ($this->resize === true){
				$key = key($this->resize_dirs);
				return array('title' => $file->title, 'path' => $this->dir . $key . '/' . $file->code);
			}else{
				return array('title' => $file->title, 'path' => $this->dir . $file->code);
			}
		}else{
			return false;
		}
  }

	/**
	 * Get upload help string
	 */

  public function get_upload_help(){
		$str = '';
		$str .= $this->lang->line('file_formats') . ': ' . implode(', ', $this->extensions) . br();
		$str .= $this->lang->line('file_max_size') . ': ' . byte_format($this->max_size * 1024) . br();

    if ($this->resize === true){
  		$key = key($this->resize_dirs);
			$str .= $this->lang->line('file_min_' . $this->min_resize) . ': ' . $this->resize_dirs[$key][$this->min_resize] . ' px' . br();
    }

		return $str;
	}

	/**
	 * Get fetch help string
	 */

  public function get_fetch_help(){
		$str = '';
		$str .= $this->lang->line('file_formats') . ': ' . implode(', ', $this->extensions) . br();
		$str .= $this->lang->line('file_max_size') . ': ' . byte_format($this->fetch_max_size * 1024) . br();

    if ($this->resize === true){
  		$key = key($this->resize_dirs);
			$str .= $this->lang->line('file_min_' . $this->min_resize) . ': ' . $this->resize_dirs[$key][$this->min_resize] . ' px' . br();
    }

		return $str;
	}

}

/* End of file db_files_model.php */
/* Location: ./application/models/db_files_model.php */