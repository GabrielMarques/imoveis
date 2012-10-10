<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MY_Controller Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class MY_Controller extends CI_Controller {


  public function __construct(){
    parent::__construct();
  }

	/**
	 * Page error
	 */

	public function _show_page_error($message, $btn = false){
		$heading = $this->lang->line('error');
		$message = $this->lang->line($message) ? $this->lang->line($message) : $message;

		$data = array(
			'heading' => $heading,
			'message' => $message,
		);

		if ($btn !== false){
			$data['btn'] = $btn;
		}

		$this->load->view('error_tpl', $data);
		echo $this->output->get_output();
		exit;
	}

}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */