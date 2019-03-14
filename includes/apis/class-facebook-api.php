<?php
/**
 * Facebook API
 * @package WordPress
 * @subpackage Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class FBPF_Facebook_Api
{
	protected $appId = null;
	protected $appSecret = null;
	protected $apiEndpoint = 'https://graph.facebook.com';
	protected $testMode = false;
	protected $testAccessToken;
	protected $testPage;

	public function __construct($appId = '', $appSecret = '', $testMode = false, $testAccessToken = '', $testPage = '')
	{
		$this->appId = $appId;
		$this->appSecret = $appSecret;
		$this->testMode = $testMode;
		$this->testAccessToken = $testAccessToken;
		$this->testPage = $testPage;
	}

	public function isReady()
	{
		return ! empty($this->appId) && ! empty($this->appSecret);
	}

	public function getAccessToken()
	{
		if ($this->testMode) {
			return $this->testAccessToken;
		} else {
			return $this->appId.'|'.$this->appSecret;
		}
	}

	public function getTestCount()
	{
		if ($this->testMode) {
			return $this->getFanCount($this->testPage);
		} else {
			return $this->getFanCount('facebook');
		}
	}

	public function getFanCount($page, $accessToken = '')
	{
		if (! $accessToken) {
			$accessToken = $this->getAccessToken();
		}

		$url = '/'. $page .'?fields=fan_count&access_token='. $accessToken;

		$data = $this->get($url);

		if (is_wp_error($data)) {
			return $data;
		}

		return $data['fan_count'];
	}

	public function getAppPermissions()
	{
		$permissions = $this->get('/'. $this->appId .'/permissions?access_token='. $this->getAccessToken(), [], 0);
		if (is_wp_error($permissions)) {
			return $permissions;
		}
		return $permissions['data'];
	}

	public function get($path = '/', $args = [], $cached = 0)
	{
		return $this->request('GET', $path, $args, $cached);
	}
	private function request($method, $path = '/', $args = array(), $cached = 0)
	{
		$args = wp_parse_args($args, array(
			'method' => $method,
			'headers' => array('Content-type' => 'application/json')
		));
		if (! empty($args['body'])) {
			$args['body'] = json_encode($args['body']);
		}
		$url = $this->apiEndpoint . $path;

		$response = false;
		if ($cached > 0) {
			$cache_key = 'fbpf_api_'. md5($method . $url . serialize($args));
			$response = get_transient($cache_key);
		}

		if (! $cached || false === $response) {
			// FBPF_Utils::log('Facebook Api - ' . $method . ' '. $path);
			$request = wp_remote_get($url, $args);

			if (is_wp_error($request)) {
				return $request;
			}

			$response = json_decode(wp_remote_retrieve_body($request), true);
			if ($cached > 0) {
				set_transient($cache_key, $response, $cached);
			}
		}

		if (isset($response['error']) && isset($response['error']['message'])) {
			return new WP_Error('fbAPiError', $response['error']['message'], $response['error']);
		}

		return $response;
	}
}
