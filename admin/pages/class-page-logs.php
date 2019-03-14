<?php
/**
 * Logs
 * @package WordPress
 * @subpackage  Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


if (! defined('ABSPATH')) {
	die('Accessing directly to this file is not allowed');
}

class FBPF_Admin_Page_Logs implements FBPF_Interface_Admin_Page
{
	public function __construct()
	{
		add_action('admin_menu' 								, [$this, 'admin_menu'], 200);
		add_action('wp_ajax_fbpf_clear_logs'					, [$this, 'clear_logs_ajax']);
		add_action('wp_ajax_fbpf_logs_template'					, [$this, 'logs_template_ajax']);
		add_action('fbpf_daily_cron'							, 'FBPF_Logger::clear_logs');
	}
	public function handle_actions()
	{
	}

	public function clear_logs_ajax()
	{
		FBPF_Logger::clear_logs();
		FBPF_Utils::ajax_ok("Logs cleaned");
	}

	public function logs_template_ajax()
	{
		if (file_exists(FBPF_Logger::log_file())) {
			FBPF_Utils::ajax_ok($this->log_template());
		} else {
			FBPF_Utils::ajax_error(__('No logs available :)', 'fbpf'));
		}
	}

	public function load_page()
	{
		if (! wp_next_scheduled('fbpf_daily_cron')) {
			wp_schedule_event(time() + 2, 'daily', 'fbpf_daily_cron');
		}
		do_action('fbpf/admin_page/logs/load');
	}

	public function render_page()
	{
		?><style>
			#fbpf_logs_wrap{ max-height:340px; overflow:hidden; overflow-y:scroll;}
			#fbpf_logs_wrap ul li{ padding:8px 5px 8px 120px; margin:0; font-size:12px; border-bottom:1px solid #e5e5e5; position:relative; }
			#fbpf_logs_wrap > ul > li > time{ position:absolute; top: auto; left:10px; }
			#fbpf_logs_wrap ul ul > li{ padding:5px;  border-bottom:none; }
			#fbpf_logs_wrap ul ul > li:before{ content:"- "}
			@media (min-width:480px){
				.fbpf-box-title .wff_ajax_action_btn{float:right;}
			}
			@media (max-width:480px){
				.fbpf-box-title span{display:block;}
				.fbpf-box-title .wff_ajax_action_btn{margin-top:20px;}
			}
		</style>
		<div class="wrap fbpf-wrap">
			<h1><?php _e(' Facebook Page Fans Logs', 'fbpf'); ?></h1><br>
			<div class="fbpf-admin-content">
				<div class="fbpf-box">
					<div class="fbpf-box-title">
	                    <span><?php _e('Logs refreshes automatically.', 'fbpf'); ?></span>
                    	<a class="button wff_ajax_action_btn" data-target="#fbpf_logs_wrap" data-url="<?php echo admin_url('admin-ajax.php?action=fbpf_clear_logs'); ?>" data-action="fbpf_clear_logs"><?php _e('Clear logs', 'fbpf'); ?></a>
                    </div>
					<div class="fbpf-box-content">
                        <div id="fbpf_logs_wrap"><?php
                        if (file_exists(FBPF_Logger::log_file())) {
                            echo $this->log_template();
                        } else {
                            echo '<div class="_error"><p>'. __('No logs available :)', 'fbpf') .'</p></div>';
                        }
                    ?></div>
                </div>
			</div>
		</div>

		<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				setTimeout(refreshLog, 5000);
				$(window).on('fbpf_clear_logs/done', function(obj, r){
					if('ok' == r.status){
						$('#fbpf_logs_wrap').html('<div class="_ok"><p><?php _e('logs cleared', 'fbpf'); ?></p></div>');
					}
				});
			});
			function refreshLog(){
				$('.fbpf_admin_widget h2').next('.fbpf_desc').addClass('ld');
				$.post(ajaxurl + '?action=fbpf_logs_template', function(r){
					if(r.status == 'ok'){
						$('#fbpf_logs_wrap').html(r.html);
						setTimeout(refreshLog, 5000);
					}
					else if (r.status == 'error'){
						$('#fbpf_logs_wrap').html('<div class="_error"><p>' + r.html + '</p></div>');
						setTimeout(refreshLog, 20000);
					}
					$('.fbpf_admin_widget h2').next('.fbpf_desc').removeClass('ld');
				});
			}
		})(jQuery);
		</script>
		<?php
	}

	public function log_template()
	{
		$buff = '';
		$lines = file(FBPF_Logger::log_file());

		if(! empty($lines))
		{
			$lines = array_reverse($lines);

			$buff .= '<ul>';
			foreach($lines as $line)
			{
				$date = substr($line,1, 19);
				$line = substr($line, 19 + 3);
				$line = maybe_unserialize(trim($line));

				if (is_array($line)) {
					$line = implode('</li><li>', $line);

					$buff .= '<li><ul>';
					$buff .= sprintf('<li>%s</li>', $line);
					$buff .= '</ul></li>';
				} else {
					$time = strtotime($date);
					$curr_time = current_time('timestamp');
					$date_str = date('d/M H:i A', $time);

					if ($time > $curr_time - HOUR_IN_SECONDS) {
						$buff .= sprintf('<li><time title="%s">'.__('%s ago', 'fbpf').'</time><span>%s</span></li>', $date_str, human_time_diff($time, $curr_time), $line);
					} else {
						$buff .= sprintf('<li><time title="%s">%s</time><span>%s</span></li>', $date_str, $date_str, $line);
					}
				}
			}
			$buff .= '</ul>';
		}

		return $buff;
	}

	public function admin_menu()
	{
		// access capability
		$access_cap = apply_filters('fbpf/access_cap/logs', 'manage_options');

		// register menu
		$admin_page = add_submenu_page(
			FBPF_SLUG,
			sprintf('%s - %s', __('Logs', 'fbpf'), __(' Facebook Page Fans', 'fbpf')),
			__('Logs', 'fbpf'),
			$access_cap,
			'fbpf-logs',
			[$this, 'render_page']
		);

		add_action("admin_print_styles-{$admin_page}"	, [$this, 'print_scripts']);
		add_action("load-{$admin_page}"					, [$this, 'load_page']);
		add_action("load-{$admin_page}"					, [$this, 'handle_actions']);
	}

	public function print_scripts()
	{
		wp_localize_script('fbpf_admin', 'fbpf', [
			'apiUrl' 		=> rest_url('fbpf/v2/'),
			'logsUrl'		=> admin_url('admin.php?page=fbpf-logs')
		]);

		wp_enqueue_style(['fbpf_admin']);
		wp_enqueue_script(['fbpf_admin']);
	}
}
