<?php
/**
 * Main Plugin Class
 * @package WordPress
 * @subpackage Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


final class Facebook_Page_Fans
{
	// plugin name
	public $name = 'Facebook Page Fans';

	// plugin version
	public $version = '2.2';

	// class instance
	public $facebook_api = null;

	// class instance
	public $settings = null;

	// class instance
	protected static $_instance = null;

	// static instance
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct()
	{
		$this->define_constants();
		$this->include_files();
		$this->initialize();
		$this->init_hooks();

		do_action('fbpf/loaded');
	}

	private function define_constants()
	{
		define('FBPF_NAME'				, $this->name);
		define('FBPF_VERSION'			, $this->version);
		define('FBPF_DIR'				, plugin_dir_path(FBPF_PLUGIN_FILE));
		define('FBPF_URL'				, plugin_dir_url(FBPF_PLUGIN_FILE));
		define('FBPF_BASENAME'			, plugin_basename(FBPF_PLUGIN_FILE));
		define('FBPF_SLUG'				, 'fbpf');
	}

	private function include_files()
	{
		// core
		include_once(FBPF_DIR . 'includes/libraries/class-logger.php');
		include_once(FBPF_DIR . 'includes/libraries/functions-form.php');
		include_once(FBPF_DIR . 'includes/class-config.php');
		include_once(FBPF_DIR . 'includes/class-utils.php');

		// abstract classes
		foreach(glob(FBPF_DIR . 'includes/abstracts/*.php') as $file) {
			include_once($file);
		}

		// models
		foreach(glob(FBPF_DIR . 'includes/models/*.php') as $file) {
			include_once($file);
		}

		// apis
		foreach (glob(FBPF_DIR . 'includes/apis/*.php') as $file) {
			include_once($file);
		}

		// rest api controllers
		foreach (glob(FBPF_DIR . 'includes/rest-api-controllers/*.php') as $file) {
			include_once($file);
		}

		include_once(FBPF_DIR . 'includes/class-facebook-page-fans-updater.php');
		include_once(FBPF_DIR . 'includes/class-facebook-page-fans-updater-crons.php');

		// admin
		if (is_admin()) {
			include_once(FBPF_DIR . 'admin/class-admin.php');
			foreach(glob(FBPF_DIR . 'admin/interfaces/*.php') as $file){
				include_once($file);
			}
			foreach(glob(FBPF_DIR . 'admin/pages/*.php') as $file){
				include_once($file);
			}
		}

		unset($file);
	}

	private function initialize()
	{
		$this->settings = new FBPF_Plugin_Settings();
		$this->facebook_api = new FBPF_Facebook_Api(
			$this->settings->get('facebook_app_id'),
			$this->settings->get('facebook_app_secret'),
			'yes' === $this->settings->get('enable_test'),
			$this->settings->get('test_access_token'),
			$this->settings->get('test_facebook_page')
		);

		if (is_admin()) {
			new FBPF_Admin();
			new FBPF_Admin_Page_Settings();

			/* render admin logs page if debuggin is enabled */
			if ('yes' == $this->settings->get('enable_debugging')) {
				new FBPF_Admin_Page_Logs();
			}
		}
	}

	private function init_hooks()
	{
		add_action('rest_api_init'							, [$this, 'rest_api_init'] 			, 10);
		add_action('admin_enqueue_scripts'					, [$this, 'register_admin_scripts']	, 10);

		// cronjobs
		add_action('fbpf_updater_cron'						, [$this, 'updater_cron']			, 10);
		add_action('fbpf_processor_cron'					, [$this, 'processor_cron']			, 10);
		add_action('fbpf_processor_second_cron'				, [$this, 'processor_cron']			, 10);
	}

	// fan count updater cronjob handler
	public function updater_cron()
	{
		$fbFansUpdater = new FBPF_Facebook_Page_Fans_Updater();
		if ('processing' == $fbFansUpdater->get('status')) {
			FBPF_Utils::log(__('Unable to schedule new job. Error: Another job in progress, skipping.', 'fbpf'));
			return false;
		}

		$schedule = $fbFansUpdater->schedule_job();
		if (is_wp_error($schedule)) {
			FBPF_Utils::log(sprintf(__('Unable to schedule job. %s', 'fbpf'), $schedule->get_error_message()));
			return false;
		} else {
			FBPF_Utils::log(__('Job scheduled.', 'fbpf'));

			wp_schedule_single_event(time(), 'fbpf_processor_cron');
			FBPF_Utils::log(__('Job processor scheduled.', 'fbpf'));

			return true;
		}
	}

	// fan count updater process cronjob handler
	public function processor_cron()
	{
		FBPF_Utils::log(__('Job processor started.', 'fbpf'));

		$fbFansUpdater = new FBPF_Facebook_Page_Fans_Updater();
		if (! in_array($fbFansUpdater->get('status'), ['scheduled', 'processing'])) {
			FBPF_Utils::log(__('No job scheduled to progress, skipping.', 'fbpf'));
			return false;
		}

		$fbFansUpdater->process();
		if ('processing' === $fbFansUpdater->get('status')) {
			if ('fbpf_processor_cron' == current_filter()) {
				wp_schedule_single_event(time() + 10, 'fbpf_processor_second_cron');
			} elseif ('fbpf_processor_second_cron' == current_filter()) {
				wp_schedule_single_event(time() + 10, 'fbpf_processor_cron');
			}
		}

		FBPF_Utils::log(__('Job processor ended.'));

		if ('completed' === $fbFansUpdater->get('status')) {
			FBPF_Utils::log(sprintf(
				__('Job complete. Total updated %d, failed %d.', 'fbpf'),
				$fbFansUpdater->get('updated_count'),
				$fbFansUpdater->get('failed_count')
			));
		}
		return true;
	}

	public function rest_api_init()
	{
		$rest_api_classes = [
			'FBPF_Settings_Rest_Api_Controller',
			'FBPF_Facebook_Page_Fans_Updater_Rest_Api_Controller'
		];

		foreach ($rest_api_classes as $rest_api_class) {
			$controller = new $rest_api_class();
			$controller->register_routes();
		}
	}

	public function register_admin_scripts()
	{
		wp_register_style('fbpf_form', 					FBPF_URL . 'assets/form.css'				, [], FBPF_VERSION);
		wp_register_script('fbpf_form', 				FBPF_URL . 'assets/form.js'					, ['jquery'], FBPF_VERSION);
		wp_register_style('fbpf_admin', 				FBPF_URL . 'assets/admin.css'				, ['fbpf_form'], FBPF_VERSION);
		wp_register_script('fbpf_admin', 				FBPF_URL . 'assets/admin.js'				, ['jquery', 'fbpf_form'], FBPF_VERSION);
	}
}
