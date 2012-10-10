<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//$autoload['controllers'] = array('crud_controller');
//$autoload['model'] = array('crud_model');

$autoload['config'] = array(
	'admin',
	'admin_navigation',
);

$autoload['libraries'] = array(
	'alerts',
	'build_form',
);

$autoload['language'] = array(
	'admin',
	'labels',
	'alerts',
);