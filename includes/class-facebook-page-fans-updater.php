<?php
class FBPF_Facebook_Page_Fans_Updater extends FBPF_Settings
{
	/* where we store the data */
	protected $option_name = 'fbpf_updater';

	/* default settings */
	protected $settings = [
		'status' 					=> '',
		'time_started'				=> '',
		'time_last_run'				=> '',
		'time_completed'			=> '',
		'next_page'					=> '',
		'updated_count'				=> 0,
		'failed_count'				=> 0,
		'post_types'				=> [],
		'batch_size'				=> 20,
		'page_url_key'				=> '',
		'page_fan_count_key'		=> '',
		'errors'					=> ''
	];

	protected $query;

	public function __construct()
	{
		parent::__construct();
		$this->settings = get_option($this->option_name, $this->settings);
	}

	/* store data to database */
	public function save()
	{
		update_option($this->option_name, $this->settings);
	}

	public function schedule_job()
	{
		if (! fbpf()->settings->get('post_types')){
			return new WP_Error('scheduleError', __('Can\'t schedule facebook page fans updater, post type not selected.'));
		} elseif (! fbpf()->settings->get('batch_size')){
			return new WP_Error('scheduleError', __('Can\'t schedule facebook page fans updater, batch_size not assigned.'));
		} elseif (! fbpf()->settings->get('page_url_key')){
			return new WP_Error('scheduleError', __('Can\'t schedule facebook page fans updater, page_url_key not assigned.'));
		} elseif (! fbpf()->settings->get('page_fan_count_key')){
			return new WP_Error('scheduleError', __('Can\'t schedule facebook page fans updater, page_fan_count_key not assigned.'));
		} elseif (! fbpf()->facebook_api->isReady()) {
			return new WP_Error('scheduleError', __('Can\'t schedule facebook page fans updater, facebook app not configured.'));
		}

		$test = fbpf()->facebook_api->getTestCount();
		if (is_wp_error($test)) {
			return new WP_Error('scheduleError', __('Can\'t schedule facebook page fans updater, facebook app error. '. $test->get_error_message()));
		}

		$this->set('errors', []);
		$this->set('status', 'scheduled');
		$this->set('time_started', '');
		$this->set('time_last_run', '');
		$this->set('time_completed', '');
		$this->set('next_page', 1);
		$this->set('updated_count', 0);
		$this->set('failed_count', 0);
		$this->set('post_types', fbpf()->settings->get('post_types'));
		$this->set('batch_size', fbpf()->settings->get('batch_size', 20));
		$this->set('page_url_key', fbpf()->settings->get('page_url_key'));
		$this->set('page_fan_count_key', fbpf()->settings->get('page_fan_count_key'));
		$this->save();
	}

	public function cancel_job()
	{
		if ('completed' === $this->get('status')) {
			return new WP_Error('cancelError', __('Job already completed.'));
		} else if ('cancelled' === $this->get('status')) {
			return new WP_Error('cancelError', __('Job cancelled already.'));
		} else if ('processing' !== $this->get('status')) {
			return new WP_Error('cancelError', __('No job is running.'));
		} else {
			$this->set('status', 'cancelled');
			$this->set('time_cancelled', time());
			$this->save();

			return true;
		}
	}

	public function process()
	{
		if ('scheduled' === $this->get('status')) {
			$this->set('time_started', time());
			$this->set('status', 'processing');
			$this->save();

			$this->process_job();
		} else if ('processing' == $this->get('status')) {
			$this->process_job();
		} else {
			//
		}
	}

	public function process_job()
	{
		$this->set('time_last_run', time());

		$errors = $this->get('errors');

		$this->query = new WP_Query([
			'posts_per_page' 	=> $this->get('batch_size'),
			'post_type' 		=> $this->get('post_types'),
			'paged' 			=> $this->get('next_page'),
			'meta_key' 			=> $this->get('page_url_key'),
			'meta_compare' 		=> 'EXISTS',
			'orderby' 			=> 'ID',
			'order' 			=> 'ASC',
			'suppress_filters' 	=> true
		]);

		if ($this->query->get_posts()) {
			foreach ($this->query->get_posts() as $post) {
				$fbPageFan = new FBPF_Facebook_Page_Fan(
					$post->ID,
					$this->get('page_url_key'),
					$this->get('page_fan_count_key')
				);

				$fetch = $fbPageFan->fetch_fan_count();
				if (is_wp_error($fetch)) {
					$this->set('failed_count', $this->get('failed_count') + 1);
					FBPF_Utils::log(sprintf(
						__('Updater Error for page %s, post id # %d: %s', 'fbpf'),
						$fbPageFan->get_page_url(),
						$post->ID,
						$fetch->get_error_message()
					));
				} else {
					$this->set('updated_count', $this->get('updated_count') + 1);
					$fbPageFan->update();
				}
			}
		}

		if ($this->query->max_num_pages > $this->get('next_page')) {
			$this->set('next_page', $this->get('next_page') + 1);
		} else {
			$this->set('status', 'completed');
			$this->set('time_completed', time());
		}

		$this->save();
	}

	public function get_data()
	{
		return $this->settings;
	}
}
