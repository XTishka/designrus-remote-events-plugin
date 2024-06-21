<?php

// src/class-api.php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Events_API_Events {
	public function __construct() {
		add_action('rest_api_init', array($this, 'register_api_routes'));
		add_action('wp_ajax_load_events', array($this, 'ajax_load_events'));
		add_action('wp_ajax_nopriv_load_events', array($this, 'ajax_load_events'));
	}

	public function register_api_routes() {
		register_rest_route('events-api/v1', '/events', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_events'),
			'permission_callback' => '__return_true'
		));
	}

	private function get_query_args($location, $quantity = 3, $current_time, $paged = 1) {
		return array(
			'post_type' => 'ajde_events',
			'posts_per_page' => $quantity,
			'paged' => $paged,
			'tax_query' => array(
				array(
					'taxonomy' => 'event_location',
					'field' => 'slug',
					'terms' => $location,
				),
			),
			'meta_query' => array(
				array(
					'key' => '_unix_start_ev',
					'value' => $current_time,
					'compare' => '>=',
					'type' => 'NUMERIC'
				),
			),
			'meta_key' => '_unix_start_ev',
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
		);
	}

	public function get_events($request) {
		$source = sanitize_text_field(get_option('source'));
		$location = sanitize_text_field(get_option('location'));
		$quantity = intval($request->get_param('quantity') ? $request->get_param('quantity') : get_option('quantity'));
		$paged = intval($request->get_param('page') ? $request->get_param('page') : 1);
	
		$current_time = current_time('timestamp');
	
		$args = $this->get_query_args($location, $quantity, $current_time, $paged);
	
		$query = new WP_Query($args);
		$events = array();
	
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
	
				$start_unix = get_post_meta(get_the_ID(), '_unix_start_ev', true);
				$end_unix = get_post_meta(get_the_ID(), '_unix_end_ev', true);
	
				$events[] = array(
					'id' => get_the_ID(),
					'title' => get_the_title(),
					'content' => get_the_content(),
					'location' => $location,
					'source' => $source,
					'subtitle' => get_post_meta(get_the_ID(), 'evcal_subtitle', true),
					'background_color' => get_post_meta(get_the_ID(), 'evcal_event_color', true),
					'start_date' => date('Y-m-d', $start_unix),
					'start_time' => date('H:i:s', $start_unix),
					'end_date' => date('Y-m-d', $end_unix),
					'end_time' => date('H:i:s', $end_unix),
					'featured_image' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
					'meta_data' => get_post_meta(get_the_ID())
				);
			}
			wp_reset_postdata();
		}

		$total_posts = $query->found_posts;
    	$total_pages = ceil($total_posts / $quantity);
	
		return rest_ensure_response(array(
			'events' => $events,
			'total_pages' => $total_pages,
			'current_page' => $paged
		));
	}
	
}

new Events_API_Events();
