<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');

/**
 * MY_Form_Validation Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class MY_Form_validation extends CI_Form_validation{

  function MY_Form_validation(){
    parent::__construct();
    $this->_error_prefix = '';
    $this->_error_suffix = '';
  }

  /**
   * Alpha-numeric with underscores, dashes and spaces and extra characters
   *
   * @access    public
   * @param    string
   * @return    bool
   */

 	function text_format($str){
    return (!preg_match("/^([-a-z0-9_-ãÃáÁàÀâÂêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛüÜçÇñÑ,&@\(\)\.\':!?\s])+$/i", $str)) ? FALSE : TRUE;
  }

  function alpha_dash_space($str){
  	return (!preg_match("/^([-a-z0-9_-ãÃáÁàÀâÂêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛüÜçÇñÑ\s])+$/i", $str)) ? FALSE : TRUE;
  }

  function no_spaces($str){
  	return (preg_match("/[\s]/", $str)) ? FALSE : TRUE;
  }

  function valid_url($value){
  	return is_valid_url($value);
  }

  function valid_date($value) {
  	return is_valid_date($value);
  }

  function valid_datetime($value) {
  	return is_valid_date($value);
  }

}

/* End of File: MY_Form_validation.php */
/* Location: ./application/libraries/MY_Form_validation.php */