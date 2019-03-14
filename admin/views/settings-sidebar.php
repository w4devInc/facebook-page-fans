<h2><?php _e('Updater', 'fbpf'); ?></h2>
<p><?php
	$fbFansUpdater = new FBPF_Facebook_Page_Fans_Updater();
	if ('processing' == $fbFansUpdater->get('status')) {
		printf(
			__('Updater is running since %s.', 'fbpf'),
			human_time_diff($fbFansUpdater->get('time_started'))
		);

		?><br /><br /><button class="button button-primary wff_ajax_action_btn" data-alert="1" data-url="<?php echo rest_url('fbpf/v2/facebook-page-fans-updater/cancel'); ?>"><?php _e('Cancel Updater', 'fbpf'); ?></button><?php
	} elseif ('cancelled' == $fbFansUpdater->get('status')) {
		printf(
			__('Last update were cancelled %s ago.<br />Total %d items were updated, %d failed.', 'fbpf'),
			human_time_diff($fbFansUpdater->get('time_cancelled')),
			$fbFansUpdater->get('updated_count'),
			$fbFansUpdater->get('failed_count')
		);

		?><br /><br /><button class="button button-primary wff_ajax_action_btn" data-alert="1" data-url="<?php echo rest_url('fbpf/v2/facebook-page-fans-updater/start'); ?>"><?php _e('Update Now', 'fbpf'); ?></button>
		<?php
	} elseif ('completed' == $fbFansUpdater->get('status')) {
		printf(
			__('Last update completed %s ago.<br />Total %d items were updated, %d failed.'),
			human_time_diff($fbFansUpdater->get('time_completed')),
			$fbFansUpdater->get('updated_count'),
			$fbFansUpdater->get('failed_count')
		);

		?><br /><br /><button class="button button-primary wff_ajax_action_btn" data-alert="1" data-url="<?php echo rest_url('fbpf/v2/facebook-page-fans-updater/start'); ?>"><?php _e('Update Now', 'fbpf'); ?></button>
		<?php
	} elseif ('scheduled' == $fbFansUpdater->get('status')) {
		_e('Update scheduled, will start shortly. Reload this page to see updates.', 'fbpf');
	} else {
		_e('No information about updater. Please update plugin settings and check back.', 'fbpf');
	}
?><p>
<h2>Cron Updater</h2>
<p><?php
	if ($timestamp = wp_next_scheduled('fbpf_updater_cron')) {
		printf(
			__('Next update is scheduled to run in %s', 'fbpf'),
			human_time_diff($timestamp)
		);
	} else {
		_e('Update is not scheduled.', 'fbpf');
	}

	if ($timestamp = wp_next_scheduled('fbpf_processor_cron')) {
		echo '<br />';
		printf(
			__('Next process is scheduled to run in %s', 'fbpf'),
			human_time_diff($timestamp)
		);
	} elseif ($timestamp = wp_next_scheduled('fbpf_processor_second_cron')) {
		echo '<br />';
		printf(
			__('Next process is scheduled to run in %s', 'fbpf'),
			human_time_diff($timestamp)
		);
	} else {
		echo '><br />';
		_e('No process is running.', 'fbpf');
	}
?></p>
