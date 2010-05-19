<?php

define('TRIVIAL_VERSION', '0.0.6');

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
	$args = func_get_args();
	return head_component('style', $args);
}

/**
 * Render script tags.
 */
function scripts() {
	$args = func_get_args();
	return head_component('script', $args);
}

/**
 * Render a style tag.
 */
function style($name, $additional = '') {
	return asset($name, 'styles/%s.css', '<link rel="stylesheet" href="styles/%s.css%s" type="text/css" %s/>', true, $additional);
}

/**
 * Render a script tag.
 */
function script($name) {
	return asset($name, 'scripts/%s.js', '<script type="text/javascript" src="scripts/%s.js%s"></script>');
}

/**
 * Render an asset tag, busting the cache if necessary.
 */
function asset($name, $search, $format, $bust_cache = true, $additional = '') {
	if (($file = fe_check(sprintf($search, $name))) !== false) {
		return sprintf($format, $name, $bust_cache ? cachebuster($file) : '', $additional);
	}
	return '';
}

/**
 * Get a cachebuster string for a file.
 */
function cachebuster($file) {
	if (file_exists($file)) {
		return '?' . filemtime($file);
	} else {
		return '';
	}
}

/**
 * Render head compoments.
 * @param string $what The type of component to render. $global_head is searched for a matching key and values for that key are merged into the list of styles.
 * @param array $additional An array of additional components to display.
 * @param string $search The search pattern to use for each component, run through sprintf() with the first %s being replaced with the component name.
 * @param string $format The output format of the HTML tag to bring in the content.
 * @return string The HTML for all found components.
 */
function head_component($what, $additional = array()) {
	global $requested, $global_head;

	$output = array();

	$components = $additional;

	if (isset($global_head[$what]) && is_array($global_head[$what])) {
		$components = array_merge($components, $global_head[$what]);
	}

	$components = array_merge($components, array('application', $requested));

	foreach ($components as $name) { $output[] = call_user_func($what, $name); }
	return implode("\n", $output);
}

/**
 * Get the code to embed the Blueprint CSS framework.
 * You should be starting with Blueprint and working your way from there, if only for the CSS reset & IE fixes alone.
 * @return string The HTML for Blueprint.
 */
function blueprint() {
  $output = array();
  $output[] = style('blueprint/screen', 'media="screen, projection"');
  $output[] = style('blueprint/print', 'media="print"');
  $output[] = '<!--[if lte IE 8]>';
  $output[] = style('blueprint/ie', 'media="screen, projection"');
  $output[] = '<![endif]-->';
  return implode('', $output);
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
