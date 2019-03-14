<?php
/**
 * Settings API
 * @package WordPress
 * @subpackage Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://w4dev.com
**/


class FBPF_Settings_Api
{
	public function update_settings($data)
	{
		$settings = new FBPF_Plugin_Settings();
		$old_job_recurrence = $settings->get('job_recurrence');
		foreach ($data as $key => $val) {
			$settings->set($key, $val);
		}
		$settings->save();

		flush_rewrite_rules();

		if ($old_job_recurrence != $settings->get('job_recurrence') || ! wp_next_scheduled('fbpf_updater_cron')) {
			$updaterCrons = new FBPF_Facebook_Page_Fans_Updater_Crons($settings->get('job_recurrence'));
			$updaterCrons->reschedule_crons();
		}

		$facebookApi = new FBPF_Facebook_Api(
			$settings->get('facebook_app_id'),
			$settings->get('facebook_app_secret'),
			'yes' === $settings->get('enable_test'),
			$settings->get('test_access_token'),
			$settings->get('test_facebook_page')
		);

		delete_option('fbpf_settings_error');
		if ($facebookApi->isReady()) {
			$fanCount = $facebookApi->getTestCount();
			if (is_wp_error($fanCount)) {
				$message = $fanCount->get_error_message();
				if (false !== strpos($message, 'Error validating application')) {
					$message = __('Wrong app credentials. Please check your app id and secret', 'fbpf');
				} else if(false !== strpos($message, 'Invalid OAuth access token signature')) {
					$message = __('Wrong app secret.', 'fbpf');
				} else if(false !== strpos($message, 'Page Public Content Access')) {
					$message = __('It looks like you app doesn\'t have "Page Public Content Access" permission, which is needed to get fan count.', 'fbpf');
				}

				update_option('fbpf_settings_error', $message);
			}
		}

		FBPF_Utils::log(sprintf(
			__( 'Settings updated by <a href="%s">%s</a>', 'impm' ),
			admin_url('user-edit.php?user_id='. get_current_user_id()),
			get_user_option('user_login')
		));

		return [
			'success' => true,
			'message' => __('Settings updated', 'impm')
		];
	}

	public function clear_cache()
	{
		if (! current_user_can('administrator')) {
			return [
			   'success' => false,
			   'message' => __('Sorry, you cant do this.', 'impm')
		   ];
		}

		global $wpdb;
		$options = $wpdb->get_col("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%\_fbpf\_%'");
		if (! empty($options)) {
			foreach($options as $option) {
				delete_option($option);
			}
		}

		// clear orphan postmeta
		$wpdb->query("DELETE pm FROM wp_postmeta pm LEFT JOIN wp_posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");

		// clear opcache
		if (function_exists('opcache_reset')) {
			opcache_reset();
		}

		return  [
			'success' => true,
			'message' => __('Cache cleaned', 'impm')
		];
	}
}
