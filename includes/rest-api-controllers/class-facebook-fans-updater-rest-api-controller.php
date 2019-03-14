<?php
/**
 * Settings Rest API
**/


class FBPF_Facebook_Page_Fans_Updater_Rest_Api_Controller extends WP_REST_Controller
{
	public function __construct()
	{
		$this->namespace = 'fbpf/v2';
		$this->rest_base = 'facebook-page-fans-updater';
	}

	public function register_routes()
	{
		register_rest_route($this->namespace, '/' . $this->rest_base . '/start', [
			[
				'methods'				=> WP_REST_Server::EDITABLE,
				'callback'				=> [$this, 'start_updater'],
				'permission_callback' 	=> [$this, 'permissions_check']
			]
		]);
		register_rest_route($this->namespace, '/' . $this->rest_base . '/cancel', [
			[
				'methods'				=> WP_REST_Server::EDITABLE,
				'callback'				=> [$this, 'cancel_updater'],
				'permission_callback' 	=> [$this, 'permissions_check']
			]
		]);
	}

	public function __call($func, $args)
	{
		$settingsApi = new FBPF_Facebook_Page_Fans_Updater_Api();
		$params = $args[0]->get_params();

		if (is_callable([$settingsApi, $func])) {
			$handle = call_user_func([$settingsApi, $func], $params);
			wp_send_json($handle);
		} else {
			wp_send_json([
				'success' => false,
				'message' => 'Invalud Request'
			]);
		}
	}

	public function permissions_check($request)
	{
		FBPF_Utils::validate_cookie_user();

		if (! is_user_logged_in()) {
			return new WP_Error('rest_forbidden_context', __('Please login first..', 'fbpf'), array('status' => rest_authorization_required_code()));
		} elseif (! current_user_can('manage_options')) {
			return new WP_Error('rest_forbidden_context', __('Unauthorized request..', 'fbpf'), array('status' => rest_authorization_required_code()));
		}

		return true;
	}
}
