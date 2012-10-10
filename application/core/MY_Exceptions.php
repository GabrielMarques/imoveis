<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MY_Exceptions Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class MY_Exceptions extends CI_Exceptions {

  function MY_Exceptions(){
    parent::__construct();
  }

	function show_404($page = '', $log_error = true){
		$heading = '404 Page Not Found';
		$message = 'The page you requested was not found.';

		// By default we log this, but allow a dev to skip it
		if ($log_error){
			log_message('error', '404 Page Not Found --> '.$page);
		}

		echo $this->show_error($heading, $message, 'error_general', 404);
		exit;
	}

	function show_error($heading, $message, $template = 'error_general', $status_code = 500){
		set_status_header($status_code);

		$message = implode('<br />', ( ! is_array($message)) ? array($message) : $message);

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include(APPPATH.'errors/'.$template.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

}

/* End of file MY_Exceptions.php */
/* Location: ./application/libraries/MY_Exceptions.php */