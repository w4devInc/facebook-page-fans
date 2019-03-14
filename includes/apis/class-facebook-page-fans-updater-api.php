<?php
/**
 * Facebook API
 * @package WordPress
 * @subpackage Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class FBPF_Facebook_Page_Fans_Updater_Api
{
	public function start_updater()
	{
		$fbFansUpdater = new FBPF_Facebook_Page_Fans_Updater();
		if ('processing' == $fbFansUpdater->get('status')) {
			return [
				'success' => false,
				'message' => __('Unable to schedule updater. Error: Another job in progress.', 'fbpf')
			];
		}

		$schedule = $fbFansUpdater->schedule_job();
		if (is_wp_error($schedule)) {
			return [
				'success' => false,
				'message' => sprintf(__('Unable to schedule job. %s', 'fbpf'), $schedule->get_error_message())
			];
		} else {
			wp_schedule_single_event(time(), 'fbpf_processor_cron');
			return [
				'success' => true,
				'urlReload' => true,
				'message' => __('Job scheduled.', 'fbpf')
			];
		}
	}

	public function cancel_updater()
	{
		$fbFansUpdater = new FBPF_Facebook_Page_Fans_Updater();
		$cancel = $fbFansUpdater->cancel_job();
		if (is_wp_error($cancel)) {
			return [
				'success' => false,
				'message' => sprintf(__('Unable to cancel job. %s', 'fbpf'), $cancel->get_error_message())
			];
		} else {
			return [
				'success' => true,
				'urlReload' => true,
				'message' => __('Job cancelled.', 'fbpf')
			];
		}
	}
}
