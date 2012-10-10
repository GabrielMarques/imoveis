<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MY_Upload Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */
 
class MY_Upload extends CI_Upload {

  public $min_width = 0;
  public $min_height = 0;

	// --------------------------------------------------------------------

	/**
	 * Initialize preferences
	 *
	 * @param	array
	 * @return	void
	 */
	public function initialize($config = array())
	{
		$defaults = array(
              'min_width'     => 0,
              'min_height'    => 0,
							'max_size'			=> 0,
							'max_width'			=> 0,
							'max_height'		=> 0,
							'max_filename'		=> 0,
							'allowed_types'		=> "",
							'file_temp'			=> "",
							'file_name'			=> "",
							'orig_name'			=> "",
							'file_type'			=> "",
							'file_size'			=> "",
							'file_ext'			=> "",
							'upload_path'		=> "",
							'overwrite'			=> FALSE,
							'encrypt_name'		=> FALSE,
							'is_image'			=> FALSE,
							'image_width'		=> '',
							'image_height'		=> '',
							'image_type'		=> '',
							'image_size_str'	=> '',
							'error_msg'			=> array(),
							'mimes'				=> array(),
							'remove_spaces'		=> TRUE,
							'xss_clean'			=> FALSE,
							'temp_prefix'		=> "temp_file_",
							'client_name'		=> ''
						);


		foreach ($defaults as $key => $val)
		{
			if (isset($config[$key]))
			{
				$method = 'set_'.$key;
				if (method_exists($this, $method))
				{
					$this->$method($config[$key]);
				}
				else
				{
					$this->$key = $config[$key];
				}
			}
			else
			{
				$this->$key = $val;
			}
		}

		// if a file_name was provided in the config, use it instead of the user input
		// supplied file name for all uploads until initialized again
		$this->_file_name_override = $this->file_name;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate tmp file but do not upload
	 *
	 * @return	bool
	 */
	public function validate_upload($field = 'userfile'){
		// Is $_FILES[$field] set? If not, no reason to continue.
		if (!isset($_FILES[$field])){
			$this->set_error('error_upload');
			return false;
		}
	
		// Was the file able to be uploaded? If not, determine the reason why.
		if (!is_uploaded_file($_FILES[$field]['tmp_name'])){
			$error = (!isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];
	
			switch($error){
				case 1:	// UPLOAD_ERR_INI_SIZE
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->set_error('error_upload_size_limit');		
					break;
				case 3: // UPLOAD_ERR_PARTIAL
				case 4: // UPLOAD_ERR_NO_FILE
				case 6: // UPLOAD_ERR_NO_TMP_DIR
				case 7: // UPLOAD_ERR_CANT_WRITE
				case 8: // UPLOAD_ERR_EXTENSION
				default:
					$this->set_error('error_upload');
					break;
			}
			
			return false;
		}
	
		// Set the uploaded data as class variables
		$this->file_temp = $_FILES[$field]['tmp_name'];
		$this->file_size = $_FILES[$field]['size'];
		$this->_file_mime_type($_FILES[$field]);
		$this->file_type = preg_replace("/^(.+?);.*$/", "\\1", $this->file_type);
		$this->file_type = strtolower(trim(stripslashes($this->file_type), '"'));
		$this->file_ext	 = $this->get_extension($_FILES[$field]['name']);
	
		// Is the file type allowed to be uploaded?
		if (!$this->is_allowed_filetype()){
			$this->set_error('error_upload_filetype');
			return false;
		}
	
		// Convert the file size to kilobytes
		if ($this->file_size > 0){
			$this->file_size = round($this->file_size/1024, 2);
		}
	
		// Is the file size within the allowed maximum?
		if (!$this->is_allowed_filesize()){
			$this->set_error('error_upload_filesize');
			return false;
		}
		
		// Are the image dimensions within the allowed size?
		// Note: This can fail if the server has an open_basdir restriction.
		if (!$this->is_allowed_dimensions()){
			$this->set_error('error_upload_dimensions');
			return false;
		}
		
		/*
		 * Run the file through the XSS hacking filter
		 * This helps prevent malicious code from being
		 * embedded within a file.  Scripts can easily
		 * be disguised as images or other file types.
		 */
		if ($this->xss_clean){
			if ($this->do_xss_clean() === FALSE){
				$this->set_error('error_upload');
				return false;
			}
		}
				
		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Finalized Custom Data Array
	 *
	 * Returns an associative array containing all of the information
	 * related to the upload, allowing the developer easy access in one array.
	 *
	 * @return	array
	 */
	public function custom_data()
	{
		return array (
			'full_path' => $this->file_temp,
			'image_width' => $this->image_width,
			'image_height' => $this->image_height,
			'image_type' => $this->image_type,
		);
	}	

	// --------------------------------------------------------------------

	/**
	 * Set Minimum Image Height
	 *
	 * @access    public
	 * @param    integer
	 * @return    void
	 */
	function set_min_height($n)
	{
		$this->min_height = ((int) $n < 0) ? 0: (int) $n;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Minimum Image Width
	 *
	 * @access    public
	 * @param    integer
	 * @return    void
	 */
	function set_min_width($n)
	{
		$this->min_width = ((int) $n < 0) ? 0: (int) $n;
	}

	// --------------------------------------------------------------------

	/**
	 * Verify that the image is within the allowed width/height
	 *
	 * @return	bool
	 */
	public function is_allowed_dimensions()
	{
		if ( ! $this->is_image())
		{
			return TRUE;
		}

		if (function_exists('getimagesize'))
		{
			$D = @getimagesize($this->file_temp);

			if ($this->max_width > 0 AND $D['0'] > $this->max_width)
			{
				return FALSE;
			}

			if ($this->max_height > 0 AND $D['1'] > $this->max_height)
			{
				return FALSE;
			}

			if ($this->min_width > 0 AND $D['0'] < $this->min_width)
			{
				return FALSE;
			}
			if ($this->min_height > 0 AND $D['1'] < $this->min_height)
			{
				return FALSE;
			}

			$types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');
			$this->image_width		= $D['0'];
			$this->image_height		= $D['1'];
			$this->image_type		= ( ! isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
			$this->image_size_str	= $D['3'];  // string containing height and width


			return TRUE;
		}

		return TRUE;
	}

}
// END Upload Class

/* End of file MY_Upload.php */
/* Location: ./application/libraries/MY_Upload.php */