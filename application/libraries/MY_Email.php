<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');

/**
 * MY_Email Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

class MY_Email extends CI_Email{

  function MY_Email(){
    parent::__construct();
    $this->ci =& get_instance();

		switch(ENVIRONMENT){
			case 'development':
				$params = array(
				  'protocol' => 'smtp',
				  'smtp_host' => 'ssl://smtp.googlemail.com',
				  'smtp_port' => 465,
				  'smtp_user' => 'development.test.email@gmail.com',
				  'smtp_pass' => 'mengomengo',
				  'mailtype' => 'html',
				  'charset' => 'utf-8',
				  'newline' => "\r\n",
				);
				break;
			case 'staging':
			case 'production':
			default:
				$params = array(
				  'protocol' => 'mail',
				  'mailtype' => 'html',
				  'charset' => 'utf-8',
				  'newline' => "\r\n",
				);
				break;
		}
    $this->initialize($params);
  }

  public function send_email($recipients, $subject, $message){
  	if(ENVIRONMENT === 'development'){
  		$recipients = array('gabrielmarques@dldbrasil.com.br');
  	}

  	$this->from($this->ci->config->item('site_email'), $this->ci->config->item('site_name_short'));
    $this->to($recipients);
    $this->subject($subject);
    $this->message($message);

    $success = $this->send();
    if ($success){
      return true;
    }else{
      log_message('error', 'Email send error: ' . $this->print_debugger_alt());
    }
  }

	public function print_debugger_alt(){
		$msg = '';

		if (count($this->_debug_msg) > 0){
			foreach ($this->_debug_msg as $val){
				$msg .= $val;
			}
		}

		$msg .= $this->_header_str." - ".htmlspecialchars($this->_subject)." - ".htmlspecialchars($this->_finalbody);
		return $msg;
	}

}

/* End of File: MY_Email.php */
/* Location: ./application/libraries/MY_Email.php */