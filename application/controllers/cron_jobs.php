<?php

/**
 * Cron_jobs Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Cron_jobs extends CI_Controller{

	public function __construct(){
		parent::__construct();

		$this->load->model('apartments_model');
	}

	public function get_apartments($page = 1, $last_page = false, $debug = false){
		//if($this->input->is_cli_request()){
			$page = is_numeric($page) ? $page : 1;
			$last_page = empty($last_page) ? false : $last_page;
			$debug = $debug == 'true' ? true : false;

			$success = $this->apartments_model->get_zap_apartments($page, $last_page, $debug);

			//log_message('info', 'Apartamentos atualizados');
		//}
	}

}
/* End of file cron_jobs.php */
/* Location: ./application/controllers/cron_jobs.php */