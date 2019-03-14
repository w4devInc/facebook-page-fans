<?php

class FBPF_Plugin_Settings extends FBPF_Settings
{
	/* where we store the data */
	protected $option_name = 'fbpf_settings';

	/* default settings */
	protected $settings = [
		'facebook_app_id' 				=> '',
		'facebook_app_secret'			=> '',
		'post_types'					=> '',
		'enable_debugging'				=> 'no'
	];

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
}
