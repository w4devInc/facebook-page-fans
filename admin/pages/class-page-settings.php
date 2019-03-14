<?php
/**
 * Settings Page
 * @package WordPress
 * @subpackage Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/

class FBPF_Admin_Page_Settings implements FBPF_Interface_Admin_Page
{
	public function __construct()
	{
		add_action( 'admin_menu'										, [$this, 'admin_menu']				, 120 );
	}

	public function handle_actions()
	{
		do_action( 'fbpf/admin_page/settings/handle_actions' );
	}

	public function load_page()
	{
		do_action( 'fbpf/admin_page/settings/load' );
	}
	public function render_notices()
	{
		do_action( 'fbpf/admin_page/settings/notices' );
		do_action( 'fbpf/admin_page_notices' );
	}
	public function render_page()
	{
		?><div class="wrap fbpf_wrap">
			<h1><?php _e( 'Settings', 'fbpf' ); ?></h1>
			<br /><?php

			do_action('fbpf/admin_page_notices');
			$this->settings_notices();

			?><div class="fbpf-admin-sidebar">
				<div class="fbpf-box">
					<div class="fbpf-box-content"><?php
						$jobRecurrence = fbpf()->settings->get('job_recurrence');
						$processRecurrence = fbpf()->settings->get('process_recurrence');

						include_once(FBPF_DIR . 'admin/views/settings-sidebar.php');
					?></div>
				</div>
			</div>
			<div class="fbpf-admin-content">
            	<div class="fbpf-box"><?php
					$settings = new FBPF_Plugin_Settings();
					include_once(FBPF_DIR . 'admin/views/form-settings.php');
				?></div>
			</div><?php

			do_action( 'fbpf/admin_page/template_after/' );

		?></div><?php
	}
	public function settings_notices()
	{
		if ($message = get_option('fbpf_settings_error')) {
			printf(
				'<div class="error settings-error notice is-dismissible">
					<p><strong>%s</strong> %s</p>
				</div>',
				__('Facebook Error:'),
				$message
			);
		}
	}

	public function admin_menu()
	{
		// access capability
		$access_cap = apply_filters( 'fbpf/access_cap/settings', 'manage_options' );

		// register menu
		$admin_page = add_submenu_page(
			FBPF_SLUG,
			sprintf( '%s - %s', __('Settings', 'fbpf'), FBPF_NAME ),
			__('Settings', 'fbpf'),
			$access_cap,
			'fbpf',
			[$this, 'render_page']
		);

		add_action( "admin_print_styles-{$admin_page}"	, [$this, 'print_scripts']);
		add_action( "load-{$admin_page}"				, [$this, 'load_page']);
		add_action( "load-{$admin_page}"				, [$this, 'handle_actions']);
	}

	public function print_scripts()
	{
		wp_localize_script('fbpf_admin', 'fbpf', [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'settingsUrl' => admin_url('admin.php?page=fbpf')
		]);

		wp_enqueue_style(['fbpf_admin']);
		wp_enqueue_script(['fbpf_admin']);

		do_action( 'fbpf/admin_page/print_styles/settings' );
	}
}
