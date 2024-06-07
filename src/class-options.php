<?php

// src/class-options.php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Events_API_Options {
	public function __construct() {
		add_action('admin_menu', array($this, 'create_settings_page'));
		add_action('admin_init', array($this, 'setup_sections'));
		add_action('admin_init', array($this, 'setup_fields'));
	}

	public function create_settings_page() {
		add_options_page(
			__('Events API Settings', 'events-api-plugin'),
			__('Events API', 'events-api-plugin'),
			'manage_options',
			'events_api',
			array($this, 'settings_page_content')
		);
	}

	public function settings_page_content() { ?>
		<div class="wrap">
			<h2><?php _e('Events API Settings', 'events-api-plugin'); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields('events_api');
				do_settings_sections('events_api');
				submit_button();
				?>
			</form>
		</div> <?php
	}

	public function setup_sections() {
		add_settings_section('events_api_section', '', array(), 'events_api');
	}

	public function setup_fields() {
		add_settings_field('source', __('Source', 'events-api-plugin'), array($this, 'field_callback'), 'events_api', 'events_api_section', array(
			'label_for' => 'source',
			'type' => 'text',
			'id' => 'source',
			'name' => 'source'
		));
		register_setting('events_api', 'source');

		add_settings_field('location', __('Location', 'events-api-plugin'), array($this, 'field_callback'), 'events_api', 'events_api_section', array(
			'label_for' => 'location',
			'type' => 'text',
			'id' => 'location',
			'name' => 'location'
		));
		register_setting('events_api', 'location');

		add_settings_field('quantity', __('Quantity', 'events-api-plugin'), array($this, 'field_callback'), 'events_api', 'events_api_section', array(
			'label_for' => 'quantity',
			'type' => 'number',
			'id' => 'quantity',
			'name' => 'quantity'
		));
		register_setting('events_api', 'quantity');
	}

	public function field_callback($args) {
		$value = get_option($args['name']);
		printf('<input type="%s" id="%s" name="%s" value="%s" />', esc_attr($args['type']), esc_attr($args['id']), esc_attr($args['name']), esc_attr($value));
	}
}

new Events_API_Options();
