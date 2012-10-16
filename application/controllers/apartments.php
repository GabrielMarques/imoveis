<?php

/**
 * Apartments
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'controllers/crud_controller.php';

class Apartments extends Crud_Controller{

	public $crud_class = 'apartment';

	public $crud_actions = array(
		'details' => true,
		'export' => true,
		'insert' => false,
		'update' => true,
		'delete' => true,
		'sort' => false,
	);

	public $crud_config = array();

	public function __construct(){
		parent::__construct();

		$this->load->model('apartments_model');

		// get neighborhoods
		$neighborhoods = array();

		$apartments = new Apartment();
		$apartments
			->distinct()
			->select('neighborhood')
			->where('status', 2)
			->get();

		if ($apartments->exists() === true){
			foreach($apartments as $apartment){
				$neighborhoods[$apartment->neighborhood] = $apartment->neighborhood;
			}
		}

		// filters
		$this->filters->load('neighborhood', 'multiselect', null, array('array_ready' => true, 'values_array' => $neighborhoods, 'values_as_keys' => true));
		$this->filters->load('flagged', 'dropdown', null, array('values_array' => 'bool_options'));
		$this->filters->load('status', 'dropdown', null, array('values_array' => 'status_types'));
		$this->filters->load('price', 'from_to', null, array('from_to_type' => 'number'));
		$this->filters->load('area', 'from_to', null, array('from_to_type' => 'number'));
		$this->filters->load('rooms', 'multiselect', null, array('array_ready' => true, 'values_array' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4)));

		// actions
		$fields = array(
			'debug' => array(
				'type' => 'dropdown',
				'values_array' => $this->config->item('bool_options'),
				'required' => true,
				'default_value' => 1,
			),
			'page' => array(
				'type' => 'text',
				'required' => true,
				'class' => 'span1',
				'default_value' => 1,
			),
			'last_page' => array(
				'type' => 'text',
				'class' => 'span1',
			),
		);
		$params = array('icon' => 'icon-home', 'modal' => true, 'modal_body' => $this->build_form->render_elements($fields), 'modal_footer' => '<em>* ' . $this->lang->line('mandatory') . '</em>');
		$this->actions->load('direct', 'get_apartments', null, $params);


		$fields = array(
			'debug' => array(
				'type' => 'dropdown',
				'values_array' => $this->config->item('bool_options'),
				'required' => true,
				'default_value' => 1,
			),
		);
		$params = array('icon' => 'icon-home', 'modal' => true, 'modal_body' => $this->build_form->render_elements($fields), 'modal_footer' => '<em>* ' . $this->lang->line('mandatory') . '</em>');
		$this->actions->load('table', 'update_apartments', null, $params);

		$this->actions->load('table', 'highlight', null, array('icon' => 'icon-ok'));
		$this->actions->load('table', 'remove_highlight', null, array('icon' => 'icon-remove'));

		$fields = array(
			'status' => array(
				'type' => 'dropdown',
				'values_array' => $this->config->item('status_types'),
				'required' => true,
			),
		);
		$params = array('icon' => 'icon-resize-horizontal', 'modal' => true, 'modal_body' => $this->build_form->render_elements($fields), 'modal_footer' => '<em>* ' . $this->lang->line('mandatory') . '</em>');
		$this->actions->load('table', 'update_status', null, $params);

	}

	public function index(){
		$this->_index();
	}

	public function get_manage_rows(){
		$this->_get_manage_rows();
	}

	public function details(){
		$this->_details();
	}

	public function export(){
		$this->_export();
	}

	public function update(){
		$this->_update();
	}

  public function delete(){
  	$this->_delete();
  }

	/**
	 * Get zap apartments
	 */

	public function get_apartments(){
		$debug = $this->input->post('debug') == 2 ? true : false;
		$page = $this->input->post('page');
		$page = is_numeric($page) ? $page : 1;
		$last_page = $this->input->post('last_page');
		$last_page = empty($last_page) ? false : $last_page;

		$success = $this->apartments_model->get_zap_apartments((int) $page, $last_page, $debug);

		// output results
		if ($success === true){
			$this->alerts->alert('success_update_many', 'success');
		}else{
			$this->alerts->alert($this->lang->line('error_apartments') . br() . ul($success), 'error');
		}

		redirect($this->navigation->current_route);
	}

	/**
	 * Update zap apartments
	 */

	public function update_apartments(){
		$this->_rows_check($this->input->post('rows'));

		$debug = $this->input->post('debug') == 2 ? true : false;
		$success = $this->apartments_model->update_zap_apartments($this->input->post('rows'), $debug);

		// output results
		if ($success === true){
			$this->alerts->alert('success_update_many', 'success');
		}else{
			$this->alerts->alert($this->lang->line('error_apartments') . br() . ul($success), 'error');
		}

		$this->session->set_flashdata('keep_table_params', true);
		redirect($this->navigation->current_route);
	}

	/**
	 * Highlight
	 */

	public function highlight(){
		$this->_rows_check($this->input->post('rows'));

		// highlight
		$success = $this->apartments_model->flag($this->input->post('rows'), 2);

		// output results
		if ($success === true){
			$this->alerts->alert('success_update_many', 'success');
		}else{
			$this->alerts->alert_error();
		}

		$this->session->set_flashdata('keep_table_params', true);
		redirect($this->navigation->current_route);
	}

	/**
	 * Remove highlight
	 */

	public function remove_highlight(){
		$this->_rows_check($this->input->post('rows'));

		// highlight
		$success = $this->apartments_model->flag($this->input->post('rows'), 1);

		// output results
		if ($success === true){
			$this->alerts->alert('success_update_many', 'success');
		}else{
			$this->alerts->alert_error();
		}

		$this->session->set_flashdata('keep_table_params', true);
		redirect($this->navigation->current_route);
	}

	/**
	 * Change status
	 */

	public function update_status(){
		$this->_rows_check($this->input->post('rows'));

		// activate
		$success = $this->apartments_model->update_status($this->input->post('rows'), $this->input->post('status'));

		// output results
		if ($success === true){
			$this->alerts->alert('success_update_many', 'success');
		}else{
			$this->alerts->alert_error();
		}

		$this->session->set_flashdata('keep_table_params', true);
		redirect($this->navigation->current_route);
	}

}

/* End of file apartments.php */
/* Location: ./application/controllers/apartments.php */