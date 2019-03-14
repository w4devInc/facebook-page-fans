<?php
/**
 * Core Environment
 * @package WordPress
 * @subpackage Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class FBPF_Config
{
	public static function cron_schedules()
	{
		$schedules = [];
		foreach (wp_get_schedules() as $key => $schedule) {
			$schedules[] = [
				'key' 	=> $key,
				'name' 	=> $schedule['display']
			];
		}

		return $schedules;
	}

	public static function post_types()
	{
		$types = [];
		foreach (get_post_types([], 'objects') as $type) {
			$types[] = [
				'key' 	=> $type->name,
				'name' 	=> sprintf('%s <span style="color:#999;">(%s)</span>', $type->label, $type->name)
			];
		}

		return $types;
	}
}
