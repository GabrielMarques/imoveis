<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * DB_Files_Controller Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'controllers/crud_controller.php';

class DB_Files_Controller extends Crud_Controller {

	public $crud_actions = array(
		'details' => false,
		'export' => false,
		'insert' => false,
		'update' => false,
		'delete' => true,
		'sort' => false,
	);

	public $crud_config = array();

  public function __construct(){
    parent::__construct();

		// load db files model
		$this->load->model('db_files_model', 'db_files');

		$params = array(
			'db_class' => $this->crud_class,
			'dir' => $this->config->item($this->crud_class, 'upload_dirs'),
			'ftp_dropbox' => $this->config->item($this->crud_class, 'dropbox_dirs'),
		);

		if (isset($this->db_files_params)){
			$params = array_merge($params, $this->db_files_params);
		}
		$this->db_files->init($params);

		// upload action
  	$params = array(
  		'html' => '<span class="btn btn-primary btn-file" rel="tooltip" data-placement="bottom" title="' . $this->db_files->get_upload_help() . '">' .
				'<span><i class="icon-upload icon-white"></i> ' . $this->lang->line('upload') . '</span>' .
				'<input id="upload-btn" type="file" name="userfile" accept="' . implode('/', $this->db_files->extensions) . '" multiple>' .
				'</span>',
  	);
		$this->actions->load('main', 'upload', null, $params);

    // enable display images
		if ($this->db_files->display_images === true){
  		$this->crud->set_fields('image', array('actions', 'manage'), true);
  		if ($this->db_files->resize === true){
	  		$this->crud->set_fields('image', array('output_params', 'thumb_dir'), $this->config->item($this->crud_class, 'upload_dirs') . 'small/');
				$this->crud->set_fields('image', array('output_params', 'large_dir'), $this->config->item($this->crud_class, 'upload_dirs') . 'large/');
  		}else{
  			$this->crud->set_fields('image', array('output_params', 'thumb_dir'), $this->config->item($this->crud_class, 'upload_dirs'));
  		}
		}

    $this->actions->load('row', 'download', null);

    if ($this->db_files->ftp_dropbox !== false){
	    $this->actions->load('direct', 'fetch', null, array('icon' => 'icon-refresh', 'tooltip' => $this->db_files->get_fetch_help()));
    }

    if (isset($this->user->can_write) && (int) $this->user->can_write !== 2){
    	$this->actions->unload('upload');
    	$this->actions->unload('fetch');
    }
  }

	/**
	 * Manage items
	 */

  public function _manage_files(){
  	$params = array(
  		'lang' => array(
				'error_file_format',
  			'error_file_size',
  			'error_no_response',
  			'error_upload',
  			'success_upload',

  		),
  		'js_vars' => array(
  			'page_url' => site_url($this->navigation->current_route),
				'max_size' => $this->db_files->max_size * 1024,
				'extensions' => $this->db_files->extensions,
  		),
  		'scripts' => array(
  			'backbone',
  			'upload',
  			'manage',
  		),
  	);

  	$this->_index($params);
  }

	/**
	 * Get manage rows
	 */

  public function _get_manage_files_rows(){
  	$this->_get_manage_rows();
  }

	/**
	 * Upload files
	 */

  public function _upload(){
  	if ($this->input->is_ajax_request() === true){
	  	$success = $this->db_files->process_upload();

	  	// output
  		if ($success === true){
				$this->output->set_content_type('application/json')->set_status_header(200);
			}else{
				$this->output->set_content_type('application/json')->set_status_header(400);
				echo $success;
			}
  	}
  }

	/**
	 * Delete files
	 */

	public function _delete_files(){
		// delete files
		$rows = $this->input->post('rows');
		$success = $this->crud->delete_rows($rows, true);

		if (is_array($success) === true){
			$files_success = $this->db_files->delete_files($success);

			$message = sizeof($success) > 1 ? 'success_file_delete_many' : 'success_file_delete';
			$this->alerts->alert($message, 'success');

		}else if ($success === false){
			$this->alerts->alert_error();
		}else{
			$this->alerts->alert($success, 'error');
		}

		$this->session->set_flashdata('keep_table_params', true);
		redirect($this->navigation->current_route);
	}

	/**
	 * Download file
	 */

  public function _download(){
  	if ($this->actions->allowed('download')){
	  	$this->load->helper('download');
	  	$id = $this->id_route;
	  	$file = $this->db_files->get_file($id);

	  	if ($file !== false){
	    	$data = file_get_contents($file['path']);
				force_download($file['title'], $data);
	  	}else{
	  		$this->alerts->alert_not_found();
	  		redirect($this->navigation->current_route);
	  	}
  	}else{
			$this->alerts->alert_error();
			redirect($this->navigation->current_route);
		}
  }

	/**
	 * Fetch from ftp
	 */

  public function _fetch(){
  	$success = $this->db_files->fetch_from_ftp();

  	// output
		if ($success === true){
			$this->alerts->alert('success_upload', 'success');
		}else{
			$this->alerts->alert($success, 'error');
		}

		$this->session->set_flashdata('keep_table_params', true);
		redirect($this->navigation->current_route);
  }
}

/* End of file db_files_controller.php */
/* Location: ./application/controllers/db_files_controller.php */