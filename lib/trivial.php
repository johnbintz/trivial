<?php

// PUT SITE CONFIGURATION IN config/trivial.inc

if (!isset($_SERVER['REDIRECT_URL'])) {
	header('HTTP/1.1 403 Forbidden');
	exit(1);
}

$root_dir = realpath(dirname(__FILE__) . '/../');

// All of these should be set within config/trivial.inc!

// The default layout.
$layout = 'application';

// Additional scripts and stylesheets can be loaded in this way. Blueprint does not get loaded this way.
$global_head = array('scripts' => array(), 'styles' => array());

// If the environment is anything but production, errors will be rendered in the page.
$trivial_env = 'development';

if (($config = fe_check('config/trivial.inc')) !== false) {
	include($config);
}

$trim = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath($root_dir));

$requested = preg_replace('#/$#', '/index.html', $_SERVER['REDIRECT_URL']);
$requested = preg_replace("#${trim}/(.*)\.[^\.]+\$#", '\1', $requested);

/**
 * Check the root path for the requested file.
 * @param string $path The path to look for.
 * @return string The path to the file on the filesystem, or false if not found.
 */
function fe_check($path) {
	global $root_dir;
	if (file_exists($full_path = ($root_dir . '/' . $path))) {
		return $full_path;
	} else {
		return false;
	}
}

/**
 * Load a partial file.
 * Partials live in the views directory and are prefixed with an underscore (ex: section/_a_partial.inc).
 * @param string $name The name of the partial without the leading underscore (ex: section/a_partial).
 * @param array $local A hash of variables to include into the local scope of the partial.
 * @return string The partial's result.
 */
function partial($name, $local = array()) {
	$name = preg_replace('#/([^/]+)$', '/_\1', $name);
	if (($path = fe_check('views/' . $name . '.inc')) !== false) {
		extract($local);
		ob_start();
		include($path);
		return ob_get_clean();
	} else {
		render_error("No partial named ${name} found!");
	}
}

/**
 * Handle a rendering error.
 * If the environment is 'production', the error is only logged. If it's anything else, it's printed
 * on the screen. Regardless, an HTTP 500 error is returned and processing stops.
 * @param string $error The error message to display.
 */
function render_error($error) {
  global $trivial_env;
  if ($trivial_env == 'production') {
    error_log($error);
  } else {
    echo "<div id='trivial-error'>${error}</div>";
  }
  header('HTTP/1.1 500 Internal Server Error');
  exit(1);
}

/**
 * Render style link tags.
 */
function styles() {
	return head_component('styles', func_get_args(), 'styles/%s.css',	'<link rel="stylesheet" href="styles/%s.css" type="text/css" />');
}

/**
 * Render script tags.
 */
function scripts() {
	return head_component('scripts', func_get_args(), 'scripts/%s.js',	'<script type="text/javascript" src="scripts/%s.js"></script>');
}

/**
 * Render head compoments.
 * @param string $what The type of component to render. $global_head is searched for a matching key and values for that key are merged into the list of styles.
 * @param array $additional An array of additional components to display.
 * @param string $search The search pattern to use for each component, run through sprintf() with the first %s being replaced with the component name.
 * @param string $format The output format of the HTML tag to bring in the content.
 * @return string The HTML for all found components.
 */
function head_component($what, $additional, $search, $format) {
	global $requested, $global_head;

	$output = array();

	$components = array_merge(array('application', $requested), $additional);
	if (is_array($global_head[$what])) {
		$components = array_merge($components, $global_head[$what]);
	}

	sort($components);

	foreach ($components as $file) {
		if (fe_check(sprintf($search, $file)) !== false) {
			$output[] = sprintf($format, $file);
		}
	}
	return implode("\n", $output);
}

/**
 * Get the code to embed the Blueprint CSS framework.
 * You should be starting with Blueprint and working your way from there, if only for the CSS reset & IE fixes alone.
 * @return string The HTML for Blueprint.
 */
function blueprint() {

}

// Search for files in the content directory, starting with .inc files (which will be include()d), then .html files (which will be file_get_content()sed)
$content = null;
if (($content_file = fe_check('content/' . $requested . '.inc')) !== false) {
	ob_start();
	include($content_file);
	$content = ob_get_clean();
} else {
	if (($content_file = fe_check('content/' . $requested . '.html')) !== false) {
		$content = file_get_contents($content_file);
	}
}

// Look for an action for the request. If it's found, execute it. Remember, $content contains the result of the above operation, if something was found.
foreach (array('application', $requested) as $action) {
	if (($action_file = fe_check('actions/' . $action . '.inc')) !== false) {
		include($action_file);
	}
}

// Look for a view with the same name as the request. If it's found, include() it, wrapping the include() in an output buffer block.
if (($view_file = fe_check('views/' . $requested . '.inc')) !== false) {
	ob_start();
	include($view_file);
	$content = ob_get_clean();
}

// We should have content by this point. If not, raise an error.
if (is_null($content)) {
	render_error("No content generated for ${requested}! Did you create a content, action, or view file for this request?");
}

// We should have a layout, too. If not, raise an error.
if (($layout_file = fe_check('views/' . $layout . '.inc')) !== false) {
	ob_start();
	include($layout_file);
	$content = ob_get_clean();
} else {
	render_error("Layout not found: ${layout}");
}

// We're done!
echo $content;
