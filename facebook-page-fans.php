<?php
/*
 * Plugin Name: Facebook Page Fans
 * Plugin URI: https://shazzad.me
 * Description: Fetch Facebook Page Fans and update on post meta. Any post type can be used along with custom field name for page url.
 * Version: 2.2
 * Author: Shazzad Hossain Khan
 * Author URI: https://shazzad.me
*/


/* Define current file as plugin file */
if (! defined('FBPF_PLUGIN_FILE')) {
	define('FBPF_PLUGIN_FILE', __FILE__);
}

/* Plugin instance caller */
function fbpf() {
	/* Require the main plug class */
	if (! class_exists('Facebook_Page_Fans')) {
		require plugin_dir_path(__FILE__) . 'includes/class-facebook-page-fans.php';
	}

	return Facebook_Page_Fans::instance();
}

/* Initialize */
add_action('plugins_loaded', 'fbpf_init');
function fbpf_init() {
	fbpf();
}
