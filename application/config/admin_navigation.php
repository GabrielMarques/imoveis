<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['admin_menu'] = array(
  array('route' => 'apartments'),
	array('route' => 'users'),
  array('route' => 'failed_logins'),
  array('route' => 'account', 'label' => 'change_password', 'type' => 'secondary', 'icon' => 'icon-user'),
  array('route' => 'login', 'label' => 'logout', 'type' => 'secondary', 'icon' => 'icon-remove'),
);

/* End of file navigation.php */
/* Location: ./application/config/navigation.php */