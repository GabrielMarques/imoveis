<?php

// output lang vars
if (isset($lang) && sizeof($lang) > 0){
	echo '<script type="text/javascript">' . lang_to_js($lang) . '</script>';
}

// output js vars
if (isset($js_vars) && sizeof($js_vars) > 0){
	echo '<script type="text/javascript">' . php_to_js($js_vars) . '</script>';
}

// output js vars no encode
if (isset($js_vars_special) && sizeof($js_vars_special) > 0){
	echo '<script type="text/javascript">';
	foreach($js_vars_special as $key => $value){
		echo php_to_js_special($key, $value, false);
	}
	echo '</script>';
}

// output jquery
echo '<script type="text/javascript" src="' . $jquery . '"></script>' . PHP_EOL;

// output js
if (isset($local_scripts)){
	foreach($local_scripts as $script){
		$this->carabiner->display($script);
	}
}

// external js
if (isset($external_scripts)){
	foreach($external_scripts as $script){
		echo '<script type="text/javascript" src="' . $script . '"></script>' . PHP_EOL;
	}
}
?>