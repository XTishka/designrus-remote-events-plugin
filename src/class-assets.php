<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Events_API_Assets {
	public function __construct() {
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_and_scripts'));
		add_action('plugins_loaded', array($this, 'load_textdomain'));
	}

	public function enqueue_styles_and_scripts() {
		wp_enqueue_style('events-api-style', plugins_url('../assets/css/style.css', __FILE__));
		wp_enqueue_script('events-api-script', plugins_url('../assets/js/script.js', __FILE__), array('jquery'), null, true);

		wp_localize_script('events-api-script', 'wpApiSettings', array(
			'root' => esc_url_raw(rest_url()),
			'nonce' => wp_create_nonce('wp_rest'),
			'quantity' => intval(get_option('quantity'))
		));
	
		wp_localize_script('events-api-script', 'wpAjaxSettings', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('load_events_nonce')
		));
	}

	public function load_textdomain() {
		load_plugin_textdomain('events-api-plugin', false, dirname(plugin_basename(__FILE__)) . '/../languages');
	}
}

new Events_API_Assets();
