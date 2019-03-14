<?php
/**
 * Plugin Settings Form
**/

$options = $settings->get_settings();


$fields = array();
$fields['action'] = array(
	'position'		=> 1,
	'name' 			=> 'action',
	'type' 			=> 'hidden',
	'value' 		=> 'fbpf_settings_update'
);

$pos = 10;

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'html'			=> '<div class="wf_field_group_title">'. __('Facebook App Credentials:', 'twpf') .'</div>
	<div class="wf_field_group_subtitle">'. sprintf(
		__('App credentials can be found at your <a href="%s">facebook developers page</a>. <br />Find your app under the <code>Apps</code>, click on it, then <code>Settings</code> > <code>Basic</code>', 'fbpf'),
		'https://developers.facebook.com/apps'
	) .'</div>'
];

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'App ID', 'fbpf' ),
	'name'	  		=> 'facebook_app_id',
	'type'    		=> 'text'
];
++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'App Secret', 'fbpf' ),
	'name'	  		=> 'facebook_app_secret',
	'type'    		=> 'text'
];


++ $pos;
$fields[] = [
	'position'		=> $pos,
	'html'			=> '<div class="wf_field_group_title">'. __('General Settings:') .'</div>'
];

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Post Types', 'fbpf' ),
	'name'	  		=> 'post_types',
	'type'    		=> 'checkbox',
	'option'		=> FBPF_Config::post_types(),
	'desc'			=> __('All of the selected post types will be scanned for page url data and updated with count', 'fbpf')
];
++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Batch Size', 'fbpf' ),
	'name'	  		=> 'batch_size',
	'type'    		=> 'text',
	'desc'			=> __('There could be thousand of posts in your database, which can not be updated in a single run. As a result, it will be redistributed in batch and updated the given x amount each time the process runs.', 'fbpf'),
	'default'		=> 20
];
++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Meta key for Page Url', 'fbpf' ),
	'name'	  		=> 'page_url_key',
	'type'    		=> 'text',
	'desc'			=> __('We will take the value of this field as the reference of facebook page url.<br>The value can be full page url, ie:<br><code>https://www.facebook.com/google</code><br>or it can be the page slug ie <code>google</code>', 'fbpf'),
	'default'		=> 'facebook_page_fan_count'
];
++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Meta key for Page Fans Count', 'fbpf' ),
	'name'	  		=> 'page_fan_count_key',
	'type'    		=> 'text',
	'desc'			=> __('This is the meta field where we will update the follower count.', 'fbpf')
];

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Cron Recurrence', 'fbpf' ),
	'name'	  		=> 'job_recurrence',
	'type'    		=> 'select',
	'option'		=> FBPF_Config::cron_schedules(),
	'desc'			=> __('How frequently the script should run ?', 'fbpf')
];

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'html'			=> '<div class="wf_field_group_title">'. __('Advanced Settings:') .'</div>'
];

++ $pos;
$fields['enable_debugging'] = [
	'position'		=> $pos,
	'label'    		=> __('Enable debugging ?', 'pkbi'),
	'name'			=> 'enable_debugging',
	'type'    		=> 'radio',
	'option'		=> ['yes' => 'Yes', 'no' => 'No'],
	'desc'			=> __('By enabling debugging, you will be able to see process logs and trace errors', 'pkbi')
];

++ $pos;
$fields['enable_test'] = [
	'position'		=> $pos,
	'label'    		=> __('Enable test mode ?', 'pkbi'),
	'name'			=> 'enable_test',
	'type'    		=> 'radio',
	'option'		=> ['yes' => 'Yes', 'no' => 'No'],
	'desc'			=> __('Don\'t have Page public content access ? enabled test mode to check the flow on your owned facebook page.', 'pkbi')
];
++ $pos;
$fields['enable_test'] = [
	'position'		=> $pos,
	'label'    		=> __('Enable test mode ?', 'pkbi'),
	'name'			=> 'enable_test',
	'type'    		=> 'radio',
	'option'		=> ['yes' => 'Yes', 'no' => 'No'],
	'desc'			=> __('Don\'t have Page public content access ? enabled test mode to check the flow on your owned facebook page.', 'pkbi')
];
++ $pos;
$fields['test_access_token'] = [
	'position'		=> $pos,
	'label'    		=> __('Enter user access token', 'pkbi'),
	'name'			=> 'test_access_token',
	'type'    		=> 'textarea',
	'desc'			=> sprintf(
		__('Go to <a href="%s">app explorer</a>, select the app and grant manage_page and pages_show_list permission. Click on Get Access Token button and copy the token here.', 'pkbi'),
		'https://developers.facebook.com/tools/explorer/'
	)
];
++ $pos;
$fields['test_facebook_page'] = [
	'position'		=> $pos,
	'label'    		=> __('Enter one of your facebook page slug', 'pkbi'),
	'name'			=> 'test_facebook_page',
	'type'    		=> 'text',
	'desc'			=> __('not the full url, just the slug. ie: https://facebook.com/SLUG/')
];

$form_args 	= [
	'id' 			=> 'fbpf_settings_form',
	'name' 			=> 'fbpf_settings_form',
	'ajax' 			=> true,
	'action' 		=> rest_url('fbpf/v2/settings'),
	'loading_text'	=> __('Updating', 'fbpf')
];

// allow filters
$fields = apply_filters( 'fbpf/settings_page/form_fields', $fields, $options, $form_args );

// order by position
uasort( $fields, 'FBPF_Utils::order_by_position' ); // order by position

echo fbpf_form_fields( $fields, $options, $form_args );
