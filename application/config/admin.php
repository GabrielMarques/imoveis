<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Main settings

$config['admin_site_name'] = 'Imóveis';
$config['admin_site_name_short'] = $config['admin_site_name'];
$config['admin_site_email'] = 'gabriel@gabrielmarques.com.br';
$config['admin_site_contact'] = '';

$config['multi_app'] = false;
$config['admin_logo_on'] = false;
$config['admin_prefix'] = '';
$config['max_rows'] = 200;
$config['enable_profiler'] = false;

// Login

$config['pre_timeout_attempts'] = 3;
$config['login_timeout'] = 5;
//$config['admin_user_class'] = 'user';
//$config['admin_failed_login_class'] = 'failed_login';
//$config['admin_login_restrictions'] = array('field' => 'user_group_id', 'value' => 1);
//$config['admin_user_fields'] = array('publisher_group_id', 'publisher_group' => 'code');

$config['password_encryption_key'] = '';
$config['master_password'] = '';

// Values arrays

$config['bool_options'] = array(1 => 'no', 2 => 'yes');
$config['user_groups'] = array(1 => 'Administrador');
$config['alert_types'] = array(1 => 'success', 2 => 'error', 3 => 'info', 4 => 'warning');

// Formats

$config['default_date_format'] = '%e %b, %Y';
$config['default_datetime_format'] = '%e %b, %Y %H:%M';
$config['default_month_format'] = '%b, %Y';
$config['default_day_month_format'] = '%e %b';
$config['default_day_year_format'] = '%b, %Y';
$config['mysql_date_format'] = '%Y-%m-%d';
$config['mysql_datetime_format'] = '%Y-%m-%d %H:%M:%S';
$config['default_currency_prefix'] = 'R$';
$config['excel_format'] = 'xlsx';

// Html output

$config['list_separator'] = '%%';
$config['default_blob_limit'] = 150;
$config['default_details_blob_limit'] = 1000;
$config['default_list_limit'] = 4;
$config['default_details_list_limit'] = 20;
$config['default_color_classes'] = array(1 => 'label-success', 2 => 'label-warning', 3 => 'label-important', 4 => 'label-info');
$config['default_icon_classes'] = array(2 => 'icon-ok');
$config['default_image_width'] = 75;

/* End of file app.php */
/* Location: ./application/config/app.php */