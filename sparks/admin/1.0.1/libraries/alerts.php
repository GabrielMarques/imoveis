<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Alerts Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class Alerts{

	public $alert_now = false;
	private $ci;

	public function __construct(){
		$this->ci =& get_instance();
	}

	/**
	 * Render alerts
	 */

	public function render($custom_classes = array()){
		$alerts = $this->get_all();

		$output = '';
		foreach($alerts as $alert){
			$classes = array('alert', 'alert-block');

			if ($alert['type'] !== 'warning'){
				$classes[] = 'alert-' . $alert['type'];
			}
			if ($alert['temp'] === true){
				$classes[] = 'alert-temp';
			}

			$classes = array_merge($classes, $custom_classes);

			$output .= '<div class="' . implode(' ', $classes) . '">';
			$output .= '<button type="button" class="close" data-dismiss="alert">×</button>';
			if ($alert['heading'] === true){
				$output .= heading($this->ci->lang->line($alert['type']) . '!', 4, 'class="alert-heading"');
			}else{
				$output .= '<strong>' . $this->ci->lang->line($alert['type']) . '!</strong>' . nbs();
			}
			$output .= $alert['message'];
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Render alerts as text
	 */

	public function render_as_text(){
		$alerts = $this->get_all();

		$output = '';
		foreach($alerts as $alert){
			$class = 'alert-text-' . $alert['type'];
			$output .= heading($this->ci->lang->line($alert['type']) . '!', 3, 'class="' . $class . '"');
			$output .= '<p class="alert-text-message">' . $alert['message'] . '</p>';
		}

		return $output;
	}

	/**
	 * Get all
	 */

	public function get_all(){
		$alerts = array();
		if ($this->ci->session->flashdata('alert') !== false){
			$alerts[] = $this->ci->session->flashdata('alert');
		}
		if ($this->alert_now !== false){
			$alerts[] = $this->alert_now;
		}
		return $alerts;
	}

	/**
	 * Now alert
	 */

	public function alert_now($message, $type = 'error', $temp = false, $heading = false){
		if ($this->ci->lang->line($message)){
			$message = $this->ci->lang->line($message);
		}
		$this->alert_now = array(
			'message' => $message,
			'type' => $type,
			'temp' => $temp,
			'heading' => $heading,
		);
	}

	/**
	 * Now error alert
	 */

	public function alert_error_now($temp = false, $heading = false){
		$this->alert_now = array(
			'message' => $this->ci->lang->line('error_default'),
			'type' => 'error',
			'temp' => $temp,
			'heading' => $heading,
		);
	}

	/**
	 * Session alert generic
	 */

	public function alert($message, $type = 'error', $temp = false, $heading = false){
		$message = $this->ci->lang->line($message) !== false ? $this->ci->lang->line($message) : $message;

		$alert = array(
			'message' => $message,
			'type' => $type,
			'temp' => $temp,
			'heading' => $heading,
		);
		$this->ci->session->set_flashdata('alert', $alert);
	}

	/**
	 * Session error alert
	 */

	public function alert_error($temp = false, $heading = false){
		$alert = array(
			'message' => $this->ci->lang->line('error_default'),
			'type' => 'error',
			'temp' => $temp,
			'heading' => $heading,
		);
		$this->ci->session->set_flashdata('alert', $alert);
	}

	/**
	 * Session not found alert
	 */

	public function alert_not_found($temp = false, $heading = false){
		$alert = array(
			'message' => $this->ci->lang->line('error_not_found'),
			'type' => 'error',
			'temp' => $temp,
			'heading' => $heading,
		);
		$this->ci->session->set_flashdata('alert', $alert);
	}

	/**
	 * Session rows not found alert
	 */

	public function alert_rows_not_found($temp = false, $heading = false){
		$alert = array(
			'message' => $this->ci->lang->line('error_rows_not_found'),
			'type' => 'error',
			'temp' => $temp,
			'heading' => $heading,
		);
		$this->ci->session->set_flashdata('alert', $alert);
	}

}

/* End of file alerts.php */
/* Location: ./application/libraries/alerts.php */