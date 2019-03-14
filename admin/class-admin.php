<?php
/**
 * Admin Environment
 * @package WordPress
 * @subpackage Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class FBPF_Admin
{
	function __construct()
	{
		add_action('admin_menu'								, [$this, 'admin_menu']);
		add_filter('plugin_action_links_' . FBPF_BASENAME	, [$this, 'plugin_action_links']);
	}

	public function admin_menu()
	{
		// access capability
		$access_cap = apply_filters('fbpf/admin_page/access_cap', 'manage_options');

		// menu position
		$menu_position = 22.7;

		// register a parent menu
		$admin_page = add_menu_page(
			__('Facebook Page Fans', 'fbpf'),
			__('Facebook Page Fans', 'fbpf'),
			$access_cap,
			FBPF_SLUG,
			'__return_false',
			'dashicons-admin-home',
			$menu_position
		);
	}

	public function plugin_action_links($links)
	{
		$links['settings'] = '<a href="'. admin_url('admin.php?page=fbpf') .'">' . __('Settings', 'fbpf'). '</a>';
		return $links;
	}
}
