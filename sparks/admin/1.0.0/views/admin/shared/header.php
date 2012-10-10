<?php
// title
$page_title = $this->config->item('admin_site_name_short');
if (isset($title) && empty($title) === false){
	$title = $this->lang->line($title) ? $this->lang->line($title) : $title;
  $page_title .= ' / ' . $title;
}
echo '<title>' . $page_title . '</title>' . PHP_EOL;

// meta
$meta = array(
  array('name' => 'Content-type', 'content' => 'text/html; charset=utf-8', 'type' => 'equiv'),
  array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'),
  array('name' => 'robots', 'content' => 'noindex, nofollow'),
);

if (isset($custom_meta)){
	$meta = array_merge($meta, $custom_meta);
}

// link tags
$links = array(
	array('href' => 'favicon.ico', 'rel' => 'shortcut icon', 'type' => 'image/ico'),
);

if (isset($custom_links)){
	$links = array_merge($links, $custom_links);
}

// output headers
echo head_elements($meta, $links);

// output css
$this->carabiner->display('css');
?>