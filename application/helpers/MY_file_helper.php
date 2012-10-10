<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Check and create new dir
 */

function create_dir($dir, $permissions = false){
	if (!is_dir($dir)){
		$success = mkdir($dir);
		if ($permissions !== false){
			chmod($dir, $permissions);
		}
		return $success;
	}
	return false;
}

/**
 * Check and delete dir
 */

function delete_dir($dir){
	if (is_dir($dir)){
		delete_files($dir, true);
		rmdir($dir);
	}
}

/**
 * Get file ext
 */

function get_file_extension($value){
	return pathinfo($value, PATHINFO_EXTENSION);
}

/**
 * Delete file if exists
 */

function delete_file($file){
	if (is_file($file)){
		unlink($file);
	}
}

/**
 * Get filenames that match ext
 */

function get_filenames_by_extension($source_dir, $extensions, $include_path = FALSE, $recursion_on = false, $_recursion = FALSE){
  static $_filedata = array();

  if ($fp = @opendir($source_dir)){
    // reset the array and make sure $source_dir has a trailing slash on the initial call
    if ($_recursion === FALSE){
      $_filedata = array();
      $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    while (FALSE !== ($file = readdir($fp))){
      //if (@is_dir($source_dir.$file) && strncmp($file, '.', 1) !== 0)
      if (@is_dir($source_dir.$file) && strncmp($file, '.', 1) !== 0 && $recursion_on === true){
      	get_filenames_by_extension($source_dir.$file.DIRECTORY_SEPARATOR, $extensions, $include_path, $recursion_on, TRUE);
      }elseif (strncmp($file, '.', 1) !== 0){
        if(in_array(pathinfo($file, PATHINFO_EXTENSION), $extensions)){
          $_filedata[] = ($include_path == TRUE) ? $source_dir.$file : $file;
        }
      }
    }
    return $_filedata;
  }else{
    return FALSE;
  }
}

/**
 * Copy file
 */

function copy_file($src_file, $dst_file, $permissions = false){
	if (is_file($dst_file)){
		$src_info = get_file_info($src_file, array('date'));
		$dst_info = get_file_info($dst_file, array('date'));
		if ($src_info['date'] > $dst_info['date']){
			copy($src_file, $dst_file);
			if ($permissions !== false){
				chmod($dst_file, $permissions);
			}
		}
	}else{
		copy($src_file, $dst_file);
		if ($permissions !== false){
			chmod($dst_file, $permissions);
		}
	}
}

/* End of file dir_file_helper.php */
/* Location: ./application/helpers/dir_file_helper.php */