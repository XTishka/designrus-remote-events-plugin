<?php
/*
Plugin Name: Events API Plugin
Description: A plugin to provide an API for events and a shortcode to display them.
Version: 1.0
Author: Design'R'us | Takhir Berdyiev
Text Domain: events-api-plugin
*/

// events-api-plugin.php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Events_API_Plugin {
	public function __construct() {
		$this->load_dependencies();
	}

	private function load_dependencies() {
		require_once plugin_dir_path(__FILE__) . 'src/class-assets.php';
		require_once plugin_dir_path(__FILE__) . 'src/class-options.php';
		require_once plugin_dir_path(__FILE__) . 'src/class-api.php';
		require_once plugin_dir_path(__FILE__) . 'src/class-render.php';
	}
}

new Events_API_Plugin();
