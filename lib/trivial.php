<?php

// If you're using a different global default layout name, change it here
$layout = 'application';

if (!isset($_SERVER['REDIRECT_URL'])) {
	header('HTTP/1.1 403 Forbidden');
	exit(1);
}

// END OF USER-CONFIGURABLE SETTINGS

function fe_check($path) {
	global $root_dir;
	if (file_exists($full_path = ($root_dir . '/' . $path))) {
		return $full_path;
	} else {
		return false;
	}
}

function partial($name, $local = array()) {
	$name = preg_replace('#/([^/]+)$', '/_\1', $name);
	if (($path = fe_check('views/' . $name . '.inc')) !== false) {
		extract($local);
		ob_start();
		include($path);
		return ob_get_clean();
	} else {
		trigger_error("No partial named ${name} found!");
	}
}

$root_dir = realpath(dirname(__FILE__) . '/../');

$trim = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath($root_dir));

$requested = preg_replace('#/$#', '/index.html', $_SERVER['REDIRECT_URL']);
$requested = preg_replace("#${trim}/(.*)\.[^\.]+\$#", '\1', $requested);

function styles($additional = array()) {
	return head_component($additional, 'styles/%s.css',	'<link rel="stylesheet" href="%s" type="text/css" />');
}

function scripts($additional = array()) {
	return head_component($additional, 'scripts/%s.js',	'<script type="text/javascript" src="%s"></script>');
}

function head_component($additional, $search, $format) {
	global $requested;

	$output = array();
	foreach (array_merge(array('application', $requested), $additional) as $file) {
		if (fe_check(sprintf($search, $file)) !== false) {
			$output[] = sprintf($format, $file);
		}
	}
	return implode("\n", $output);
}

$content = null;
if (($content_file = fe_check('content/' . $requested . '.html')) !== false) {
	$content = file_get_contents($content_file);
}

foreach (array('application', $requested) as $action) {
	if (($action_file = fe_check('actions/' . $action . '.inc')) !== false) {
		include($action_file);
	}
}

if (($view_file = fe_check('views/' . $requested . '.inc')) !== false) {
	ob_start();
	include($view_file);
	$content = ob_get_clean();
}

if (is_null($content)) {
	trigger_error("No content generated for ${requested}! Did you create a content, action, or view file for this request?");
}

if (($layout_file = fe_check('views/' . $layout . '.inc')) !== false) {
	ob_start();
	include($layout_file);
	$content = ob_get_clean();
}

echo $content;
