<?php
/**
 * Handles facebook account communication
 * @package WordPress
 * @subpackage Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class FBPF_Facebook_Page_Fan
{
	public $id = 0;
	public $data = [
		'page_url'		=> '',
		'fan_count' 	=> '',
		'time_updated' 	=> ''
	];
	protected $pageUrlKey;
	protected $fanCountKey;
	protected $timeUpdatedKey = '_fbpf_time_updated';

	function __construct($postId, $pageUrlKey, $fanCountKey)
	{
		$this->id = $postId;
		$this->pageUrlKey = $pageUrlKey;
		$this->fanCountKey = $fanCountKey;

		$this->read($postId);
	}

	public function get_page_url()
	{
		return $this->data['page_url'];
	}
	public function get_fan_count()
	{
		return $this->data['fan_count'];
	}
	public function get_time_updated()
	{
		return $this->data['time_updated'];
	}

	public function set_page_url($val)
	{
		return $this->data['page_url'] = (string) $val;
	}
	public function set_fan_count($val)
	{
		return $this->data['fan_count'] = (int) $val;
	}
	public function set_time_updated($val)
	{
		return $this->data['time_updated'] = (int) $val;
	}

	public function read($id)
	{
		$this->id = $id;
		$this->data = [
			'page_url' 		=> get_post_meta($this->id, $this->pageUrlKey, true),
			'fan_count' 	=> get_post_meta($this->id, $this->fanCountKey, true),
			'time_updated' 	=> get_post_meta($this->id, $this->timeUpdatedKey, true)
		];
	}

	public function update()
	{
		$this->data['time_updated'] = time();

		update_post_meta($this->id, $this->pageUrlKey, $this->data['page_url']);
		update_post_meta($this->id, $this->fanCountKey, $this->data['fan_count']);
		update_post_meta($this->id, $this->timeUpdatedKey, $this->data['time_updated']);
	}

	public function fetch_fan_count()
	{
		if (! $this->get_page_name()) {
			return new WP_Error('fetchFanCountError', __('Post # %d doesnt have facebook page url assigned'));
		}

		// $pageName = $this->sanitize_page_name($this->get_page_url());
		$fanCount = fbpf()->facebook_api->getFanCount($this->get_page_name());

		if (is_wp_error($fanCount)) {
			$message = $fanCount->get_error_message();
			if (false !== strpos($message, 'Error validating application')) {
				$message = __('Wrong app credentials. Please check your app id and secret', 'fbpf');
			} else if(false !== strpos($message, 'Invalid OAuth access token signature')) {
				$message = __('Wrong app secret.', 'fbpf');
			} else if(false !== strpos($message, 'Page Public Content Access')) {
				$message = __('It looks like you app doesn\'t have "Page Public Content Access" permission, which is needed to get fan count.', 'fbpf');
			}

			return new WP_Error('fetchFanCountError', $message);
		}

		$this->set_fan_count($fanCount);

		return true;
	}

	function get_page_name()
	{
		if (! $this->get_page_url()) {
			return '';
		}

		$pageUrl = trim($this->get_page_url(), '/');

		if (false !== strpos($pageUrl, 'profile.php?id=')) {
			$parts = explode('?id=', $pageUrl);
			$pageName = array_pop($parts);
		} else if (false !== strpos($pageUrl, 'facebook.com')) {
			$path = trim(parse_url($pageUrl,  PHP_URL_PATH), '/');
			$parts = explode('/', $path);
			if ($parts[0] === 'pg') {
				$pageName = $parts[1];
			} else {
				$pageName = $parts[0];
			}
		} else {
			$pageName = $pageUrl;
		}

		return $pageName;
	}
}
