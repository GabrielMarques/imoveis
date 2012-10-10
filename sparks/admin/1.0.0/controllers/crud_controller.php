<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Crud_Controller Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'controllers/admin_controller.php';

class Crud_Controller extends Admin_Controller {

	public $sub_route;
	public $id_route;
	public $value_route;

	public function __construct(){
		parent::__construct();

    // set nav options
		$this->load->library('navigation');
    $route_found = $this->navigation->init($this->config->item('admin_menu'), $this->user->user_group_id);

		// restrict access
		if ($route_found === false){
			$this->_restrict_access(true);
		}else{
			$this->_restrict_access();
		}

		$this->load->model('crud_model', 'crud');
		$this->load->library('actions');
		$this->load->library('filters');

		if (MULTI_APP === true){
			$this->sub_route = $this->uri->segment(3);
			$this->id_route = $this->uri->segment(4);
			$this->value_route = $this->uri->segment(5);
		}else{
			$this->sub_route = $this->uri->segment(2);
			$this->id_route = $this->uri->segment(3);
			$this->value_route = $this->uri->segment(4);
		}

		// load crud library
		if (!isset($this->crud_class)){
			show_error($this->lang->line('error_config'));
			exit;
		}
		if (!isset($this->crud_actions)){
			$this->crud_actions = array();
		}
		if (!isset($this->crud_config)){
			$this->crud_config = array();
		}

		$params = array(
			'class' => $this->crud_class,
			'actions' => $this->crud_actions,
			'config' => $this->crud_config,
		);
		$this->crud->init($params);

		// can write
	  if (isset($this->user->can_write) && (int) $this->user->can_write !== 2){
    	$this->actions->unload('insert');
    	$this->actions->unload('update');
    	$this->actions->unload('delete');
    	$this->actions->unload('sort');
    }
	}

	/**
	 * Manage items
	 */

	public function _index($params = array()){
		$content = isset($params['content']) ? $params['content'] : 'admin/crud/manage';
		$fetch_url = isset($params['fetch_url']) ? $params['fetch_url'] : site_url($this->navigation->current_route . '/get_manage_rows');

		// reset table params
		$table_params = $this->crud->reset_table_params($this->session->flashdata('keep_table_params'));

		// filters
		$filters_values = $this->filters->reset($this->session->flashdata('keep_table_params'));
		$filters_fields = $this->filters->get_fields();
		if ($filters_fields !== false){
			$form_params = array(
				'fields' => $filters_fields,
				'values' => $filters_values,
				'client' => true,
			);
			$this->build_form->config($form_params);
			$filters_on = true;
		}else{
			$filters_on = false;
		}

		// get rows
		$table_data = isset($params['table_data_func']) ? call_user_func_array($params['table_data_func'][0], $params['table_data_func'][1]) : $this->crud->get_manage_rows('server');

		// output
		$data = array(
      'content' => $content,
      'fields' => $table_data['fields'],
			'totals' => isset($table_data['totals']) ? $table_data['totals'] : false,
			'table_params' => $table_params,
			'filters_on' => $filters_on,
			'show_filters_form' => $filters_values !== false ? true : false,
		);
		if (isset($params['data'])){
			$data = array_merge($data, $params['data']);
    }

		// lang
		$data['lang'] = array(
			'error',
			'success',
			'error_checked_items',
			'error_no_rows',
			'error_session',
			'error_default',
			'of',
			'to',
			'records',
		);
	  if (isset($params['lang'])){
			$data['lang'] = array_merge($data['lang'], $params['lang']);
    }

		// js vars
		$data['js_vars'] = array(
			'fetch_url' => $fetch_url,
			'table_params' => $table_params,
			'rows' => $table_data['rows'],
			'total_rows' => $table_data['total_rows'],
			'filters' => $this->filters->get_fields_js(),
		);
	  if (isset($params['js_vars'])){
			$data['js_vars'] = array_merge($data['js_vars'], $params['js_vars']);
    }

		$css_groups = array('datepicker', 'fancybox');
    $local_scripts = array('backbone', 'manage');
    if (isset($params['scripts'])){
			$local_scripts = $params['scripts'];
    }
    $this->load->view('admin/admin_tpl', $this->_get_admin_view_data($data, array('local_scripts' => $local_scripts, 'css_groups' => $css_groups)));
	}

	/**
	 * Get manage rows
	 */

	public function _get_manage_rows($params = array()){
		if ($this->input->is_ajax_request() === true){

			// set table params
			$this->crud->set_table_params();

			// set filters
			$success = $this->filters->set();
			if ($success === true){
				// get rows
				$table_data = isset($params['table_data_func']) ? call_user_func_array($params['table_data_func'][0], $params['table_data_func'][1]) : $this->crud->get_manage_rows('client');

				// output
				$this->output->set_content_type('application/json')->set_status_header(200);
				echo json_encode($table_data);
				exit;
			}else{
				// output
				$this->output->set_content_type('application/json')->set_status_header(400);
				echo json_encode($success);
				exit;
			}
		}
	}

	/**
	 * Item detais
	 */

	public function _details($params = array()){
		$content = isset($params['content']) ? $params['content'] : 'admin/crud/details';
		$id = isset($params['id']) ? $params['id'] : $this->id_route;

		// get data
		$details = $this->crud->get_details($id);
		if ($details === false){
			$this->alerts->alert_not_found();
			redirect($this->navigation->current_route);
		}

		// build form
		$form_params = array(
			'fields' => $details['fields'],
			'values' => $details['values'],
		);
		$this->build_form->config($form_params);

		// output
		$data = array(
      'content' => $content,
			'row' => $details['values'],
			'row_raw' => $details['raw_values'],
			'header_title' => $this->crud->details_field === 'id' ? $this->navigation->get_page_title() . ' #' . $details['values'][$this->crud->details_field] : $details['values'][$this->crud->details_field],
		);

		if (isset($params['data'])){
			$data = array_merge($data, $params['data']);
		}

		//$css_groups = array('fancybox', 'prettify');
		$css_groups = array('fancybox');
    $local_scripts = array('backbone', 'details');
	  if (isset($params['scripts'])){
			$local_scripts = $params['scripts'];
    }
    $this->load->view('admin/admin_tpl', $this->_get_admin_view_data($data, array('local_scripts' => $local_scripts, 'css_groups' => $css_groups)));
	}

	/**
	 * Export to excel
	 */

	public function _export($params = array()){
		$this->output->enable_profiler(false);
		if ($this->actions->allowed('export')){
			$this->load->helper('excel');

			// keep table params
			$unset_vars = array('limit' => '', 'page' => '');
			$this->session->unset_userdata($unset_vars);

			$table_params = $this->crud->reset_table_params(true);

			// keep filters
			$filters_values = $this->filters->reset(true);

			// get rows
			$table_data = isset($params['table_data_func']) ? call_user_func_array($params['table_data_func'][0], $params['table_data_func'][1]) : $this->crud->get_export_rows();

			// output
			$data = array(
        'content' => 'admin/export/excel_default',
				'title' => $this->navigation->get_page_title(),
				'fields' => $table_data['fields'],
				'rows' => $table_data['rows'],
				'info' => $this->filters->get_info_str(),
			);

			if (isset($params['data'])){
				$data = array_merge($data, $params['data']);
			}

			$this->load->view('admin/export/excel_tpl', $data);
		}else{
			$this->alerts->alert_error();
			redirect($this->navigation->current_route);
		}
	}

	/**
	 * Insert new item
	 */

	public function _insert($params = array()){
		if ($this->actions->allowed('insert')){
			$content = isset($params['content']) ? $params['content'] : 'admin/crud/insert';
			$errors = false;

			// process insert
			if ($this->input->post('process')){
				$success = $this->crud->insert();
				if (is_array($success) === false){
					$this->alerts->alert('success_insert', 'success');
					$redirect = $this->actions->allowed('details') ? $this->navigation->current_route . '/details/' . $success : $this->navigation->current_route;
					redirect($redirect);
				}else{
					$this->alerts->alert_now($success['message'], 'error');
					$errors = $success['errors'];
				}
			}

			// build form
			$form_params = array(
				'fields' => $this->crud->get_insert_fields('insert_form'),
				'errors' => $errors,
			);
			$this->build_form->config($form_params);

			// output insert form
			$data = array(
	      'content' => $content,
				'js_vars' => array(
					'current_route' => site_url($this->navigation->current_route),
				),
			);

			if (isset($params['data'])){
				$data = array_merge($data, $params['data']);
			}

			$css_groups = array('datepicker');
	    $local_scripts = array('backbone', 'insert_update');
		  if (isset($params['scripts'])){
				$local_scripts = $params['scripts'];
    	}
	    $this->load->view('admin/admin_tpl', $this->_get_admin_view_data($data, array('local_scripts' => $local_scripts, 'css_groups' => $css_groups)));
		}else{
			$this->alerts->alert_error();
			redirect($this->navigation->current_route);
		}
	}

	/**
	 * Update item
	 */

	public function _update($params = array()){
		$content = isset($params['content']) ? $params['content'] : 'admin/crud/update';
		$id = isset($params['id']) ? $params['id'] : $this->id_route;
		$errors = false;

		// get update form
		$update_form = $this->crud->get_update_form($id);
		if ($update_form === false){
			$this->alerts->alert_not_found();
			redirect($this->navigation->current_route);
		}

		// process update
		if ($this->input->post('process')){
			$success = $this->crud->update();
			if ($success === true){
				$this->alerts->alert('success_update', 'success');
				$redirect = $this->actions->allowed('details') ? $this->navigation->current_route . '/details/' . $id : $this->navigation->current_route;
				if (isset($params['redirect'])){
					$redirect = $params['redirect'];
				}
				redirect($redirect);
			}else{
				$this->alerts->alert_now($success['message'], 'error');
				$errors = $success['errors'];
			}
		}

		// build form
		$form_params = array(
			'fields' => $update_form['fields'],
			'values' => $update_form['values'],
			'errors' => $errors,
		);
		$this->build_form->config($form_params);

		// output update form
		$data = array(
      'content' => $content,
			'header_title' => $this->crud->details_field === 'id' ? $this->navigation->get_page_title() . ' #' . $update_form['values'][$this->crud->details_field] : $update_form['values'][$this->crud->details_field],
			'id' => $update_form['values']['id'],
			'js_vars' => array(
				'current_route' => site_url($this->navigation->current_route),
			),
		);

		if (isset($params['data'])){
			$data = array_merge($data, $params['data']);
		}

		$css_groups = array('datepicker');
    $local_scripts = array('backbone', 'insert_update');
	  if (isset($params['scripts'])){
			$local_scripts = $params['scripts'];
    }
    $this->load->view('admin/admin_tpl', $this->_get_admin_view_data($data, array('local_scripts' => $local_scripts, 'css_groups' => $css_groups)));
	}

	/**
	 * Delete rows
	 */

	public function _delete(){
		$rows = $this->input->post('rows');

		// delete
		$success = $this->crud->delete_rows($rows);

		// output results
		if ($success === true){
			$message = sizeof($rows) > 1 ? 'success_delete_many' : 'success_delete';
			$this->alerts->alert($message, 'success');
		}else if ($success === false){
			$this->alerts->alert_rows_not_found();
		}else{
			$this->alerts->alert($success, 'error');
		}

		$this->session->set_flashdata('keep_table_params', true);
		redirect($this->navigation->current_route);
	}

	/**
	 * Sort items
	 */

	public function _sort(){
		if ($this->actions->allowed('sort')){
			// process sort
			if ($this->input->post('process')){
				$rows = $this->input->post('rows');
				if ($rows && is_array($rows)){
					$this->crud->sort_rows(false, $rows);
					$this->alerts->alert('success_sort', 'success');
					redirect($this->navigation->current_route);
				}else{
					$this->alerts->alert_error_now();
				}
			}

			// get sort data
			$rows = $this->crud->get_sort_rows();
			if (sizeof($rows) === 0){
				$this->alerts->alert_error();
				redirect($this->navigation->current_route);
			}

			$fields = array('sort_id', $this->crud->details_field);

			// output
			$this->load->library('table');
			$data = array(
        'content' => 'admin/crud/sort',
        'fields' => $fields,
				'rows' => $rows,
			);

	    $local_scripts = array('backbone', 'sort');
		  if (isset($params['scripts'])){
				$local_scripts = $params['scripts'];
    	}
	    $this->load->view('admin/admin_tpl', $this->_get_admin_view_data($data, array('local_scripts' => $local_scripts)));
		}else{
			$this->alerts->alert_error();
			redirect($this->navigation->current_route);
		}
	}

}

/* End of file crud_controller.php */
/* Location: ./application/controllers/crud_controller.php */