<?php
/**
 * Admin Environment
 * @package WordPress
 * @subpackage Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


interface FBPF_Interface_Admin_Page
{
	public function load_page();
	public function handle_actions();
	public function print_scripts();
	public function render_page();
}
