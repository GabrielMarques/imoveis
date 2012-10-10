<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ASSETS GROUPS

$config['jquery_dev'] = 'http://localhost/active/thiago-diz/assets/js/jquery/jquery.min.js';
$config['jquery_production'] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';

$config['css_groups'] = array(

	'bootstrap' => array(
		'bootstrap/_admin-bootstrap.less',
	),

	'bootstrap_login' => array(
		'bootstrap/_login-bootstrap.less',
	),

	'bootstrap_front' => array(
		'bootstrap/_front-bootstrap.less',
	),

	'datepicker' => array(
		'datepicker/datepicker_bootstrap.css',
	),

	'fancybox' => array(
		'fancybox/jquery.fancybox.css?v=2.1.0',
	),

	'prettify' => array(
		'etc/prettify.css',
	),

);

$config['local_scripts'] = array(

	'cache' => array(
	  array('spin/jquery.spin.min.js'),
	  array('spin/spin.min.js'),
	  array('bootstrap/bootstrap.min.js'),
	),

	'no_cache' => array(
		array('admin/app.js'),
	),

	'backbone' => array(
	  array('backbone/underscore.js'),
	  array('backbone/underscore.string.js'),
	  array('backbone/backbone.js'),
	),

	'login' => array(
	  array('bootstrap/bootstrap.min.js'),
	),

	'manage' => array(
	  array('etc/jquery.simplePagination.js'),
		array('jquery/jquery-ui-1.8.17.custom.min.js'),
		array('etc/jquery.ui.datepicker.english.js'),
		array('fancybox/jquery.fancybox.pack.js?v=2.1.0'),
		array('admin/manage.js'),
	),

	'upload' => array(
		array('upload/jquery.ui.widget.js'),
		array('upload/jquery.iframe-transport.js'),
		array('upload/jquery.fileupload.js'),
	),

	'details' => array(
		//array('etc/prettify.js'),
		array('fancybox/jquery.fancybox.pack.js?v=2.1.0'),
		array('admin/details.js'),
	),

	'insert_update' => array(
		array('etc/password_strength.min.english.js'),
		array('jquery/jquery-ui-1.8.17.custom.min.js'),
		array('etc/jquery.ui.datepicker.pt_BR.js'),
		array('markitup/jquery.markitup.js'),
		array('markitup/sets/html/set.pt_BR.js'),
		array('admin/insert_update.js'),
	),

	'sort' => array(
		array('jquery/jquery-ui-1.8.17.custom.min.js'),
		array('admin/sort.js'),
	),

	'account' => array(
		array('etc/password_strength.min.english.js'),
	),

	'charts' => array(
		array('highcharts/theme.js'),
		array('highcharts/highcharts.js'),
		array('highcharts/exporting.js'),
		array('etc/accounting.min.js'),
	),

);

$config['external_scripts'] = array(

);

/* End of file assets.php */
/* Location: ./application/config/assets.php */