<?php
/*
Plugin Name: Events API Plugin
Description: A plugin to provide an API for events and a shortcode to display them.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Events_API_Plugin {
	public function __construct() {
		add_action('admin_menu', array($this, 'create_settings_page'));
		add_action('admin_init', array($this, 'setup_sections'));
		add_action('admin_init', array($this, 'setup_fields'));
		add_action('rest_api_init', array($this, 'register_api_routes'));
		add_shortcode('display_events', array($this, 'display_events_shortcode'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_and_scripts'));

		// Add AJAX actions
		add_action('wp_ajax_load_events', array($this, 'ajax_load_events'));
		add_action('wp_ajax_nopriv_load_events', array($this, 'ajax_load_events'));
	}

	public function enqueue_styles_and_scripts() {
		wp_enqueue_style('events-api-style', plugins_url('assets/css/style.css', __FILE__));
		wp_enqueue_script('events-api-script', plugins_url('assets/js/script.js', __FILE__), array('jquery'), null, true);
		wp_localize_script('events-api-script', 'events_api', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('load_events_nonce')
		));
	}

	public function create_settings_page() {
		add_options_page(
			'Events API Settings',
			'Events API',
			'manage_options',
			'events_api',
			array($this, 'settings_page_content')
		);
	}

	public function settings_page_content() { ?>
        <div class="wrap">
            <h2>Events API Settings</h2>
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
		add_settings_field('source', 'Source', array($this, 'field_callback'), 'events_api', 'events_api_section', array(
			'label_for' => 'source',
			'type' => 'text',
			'id' => 'source',
			'name' => 'source'
		));
		register_setting('events_api', 'source');

		add_settings_field('location', 'Location', array($this, 'field_callback'), 'events_api', 'events_api_section', array(
			'label_for' => 'location',
			'type' => 'text',
			'id' => 'location',
			'name' => 'location'
		));
		register_setting('events_api', 'location');

		add_settings_field('quantity', 'Quantity', array($this, 'field_callback'), 'events_api', 'events_api_section', array(
			'label_for' => 'quantity',
			'type' => 'number',
			'id' => 'quantity',
			'name' => 'quantity'
		));
		register_setting('events_api', 'quantity');
	}

	public function field_callback($args) {
		$value = get_option($args['name']);
		printf('<input type="%s" id="%s" name="%s" value="%s" />', $args['type'], $args['id'], $args['name'], esc_attr($value));
	}

	public function register_api_routes() {
		register_rest_route('events-api/v1', '/events', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_events'),
			'permission_callback' => '__return_true'
		));
	}

	public function get_events($request) {
		$source = get_option('source');
		$location = get_option('location');
		$quantity = get_option('quantity');

		// Текущая дата в Unix формате
		$current_time = current_time('timestamp');

		$args = array(
			'post_type' => 'ajde_events',
			'posts_per_page' => $quantity ? $quantity : -1,
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

		$query = new WP_Query($args);
		$events = array();

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$meta_data = get_post_meta(get_the_ID());
				$evcal_subtitle = get_post_meta(get_the_ID(), 'evcal_subtitle', true);
				$evcal_event_color = get_post_meta(get_the_ID(), 'evcal_event_color', true);
				$start_unix = get_post_meta(get_the_ID(), '_unix_start_ev', true);
				$end_unix = get_post_meta(get_the_ID(), '_unix_end_ev', true);

				$start_date = date('Y-m-d', $start_unix);
				$start_time = date('H:i:s', $start_unix);
				$end_date = date('Y-m-d', $end_unix);
				$end_time = date('H:i:s', $end_unix);

				$events[] = array(
					'id' => get_the_ID(),
					'title' => get_the_title(),
					'content' => get_the_content(),
					'location' => $location,
					'source' => $source,
					'subtitle' => $evcal_subtitle,
					'background_color' => $evcal_event_color,
					'start_date' => $start_date,
					'start_time' => $start_time,
					'end_date' => $end_date,
					'end_time' => $end_time,
					'meta_data' => $meta_data
				);
			}
			wp_reset_postdata();
		}

		// For REST API endpoint
		return rest_ensure_response($events);
	}

	public function ajax_load_events() {
		check_ajax_referer('load_events_nonce', 'nonce');

		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$source = get_option('source');
		$location = get_option('location');
		$quantity = get_option('quantity');

		// Текущая дата в Unix формате
		$current_time = current_time('timestamp');

		$args = array(
			'post_type' => 'ajde_events',
			'posts_per_page' => $quantity ? $quantity : -1,
			'paged' => $page,
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

		$query = new WP_Query($args);

		if ($query->have_posts()) {
			ob_start();
			while ($query->have_posts()) {
				$query->the_post();
				$meta_data = get_post_meta(get_the_ID());
				$evcal_subtitle = get_post_meta(get_the_ID(), 'evcal_subtitle', true);
				$evcal_event_color = get_post_meta(get_the_ID(), 'evcal_event_color', true);
				$start_unix = get_post_meta(get_the_ID(), '_unix_start_ev', true);
				$end_unix = get_post_meta(get_the_ID(), '_unix_end_ev', true);

				$start_date = date('Y-m-d', $start_unix);
				$start_time = date('H:i:s', $start_unix);
				$end_date = date('Y-m-d', $end_unix);
				$end_time = date('H:i:s', $end_unix);

				$text_color = $this->get_text_color($evcal_event_color);
				$start_day = date('d', strtotime($start_date));
				$start_month = date_i18n('F', strtotime($start_date));

				?>
                <div class="event" style="background: #<?php echo $evcal_event_color ?>; color: #<?php echo $text_color ?>" data-content="<?php echo esc_attr(get_the_content()); ?>">
                    <div class="date">
                        <span class="day"><?php echo esc_html($start_day); ?></span>
                        <span class="month"><?php echo esc_html($start_month); ?></span>
                    </div>
                    <div class="content">
                        <span class="title"><?php echo esc_html(get_the_title()); ?></span>
                        <span class="subtitle"><?php echo esc_html($evcal_subtitle); ?></span>
                    </div>
                </div>
				<?php
			}
			wp_reset_postdata();
			$events_html = ob_get_clean();
			wp_send_json_success($events_html);
		} else {
			wp_send_json_error('No more events.');
		}
	}

	public function get_text_color($hexcolor) {
		$r = hexdec(substr($hexcolor, 0, 2));
		$g = hexdec(substr($hexcolor, 2, 2));
		$b = hexdec(substr($hexcolor, 4, 2));
		$brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
		return ($brightness > 170) ? '202124' : 'ffffff';
	}

	public function display_events_shortcode() {
		$response = wp_remote_get(rest_url('events-api/v1/events'));
		$events = json_decode(wp_remote_retrieve_body($response), true);

		if (empty($events)) {
			return 'No events found.';
		}

		ob_start(); // Начало буферизации вывода
		?>
        <div class="container">
            <div class="row">
                <div class="coming-soon-events">
                    <div class="header-block">
                        <p class="header"><?php echo __('Coming soon') ?></p>
                        <span class="previous_coming_events">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </span>
                        <span class="next_coming_events">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    </div>
					<?php foreach ($events as $event) :
						$start_day = date('d', strtotime($event['start_date']));
						$start_month = date_i18n('F', strtotime($event['start_date']));
						$text_color = $this->get_text_color($event['background_color']);
						?>
                        <div class="event" style="background: #<?php echo $event['background_color'] ?>; color: #<?php echo $text_color ?>" data-content="<?php echo esc_attr($event['content']); ?>">
                            <div class="date">
                                <span class="day"><?php echo esc_html($start_day); ?></span>
                                <span class="month"><?php echo esc_html($start_month); ?></span>
                            </div>
                            <div class="content">
                                <span class="title"><?php echo esc_html($event['title']); ?></span>
                                <span class="subtitle"><?php echo esc_html($event['subtitle']); ?></span>
                            </div>
                        </div>
					<?php endforeach; ?>
                </div>
            </div>
        </div>
		<?php
		// Вставка HTML для всплывающего окна и оверлея
		?>
        <div id="event-overlay" class="event-overlay"></div>
        <div id="event-modal" class="event-modal">
            <div class="event-modal-header">
                <span class="event-modal-header-wrapper">
                    <span id="event-modal-date" class="event-modal-date">
                        <span id="event-modal-day" class="event-modal-day"></span>
                        <span id="event-modal-month" class="event-modal-month"></span>
                    </span>
                    <span id="event-modal-text" class="event-modal-text">
                        <span id="event-modal-title" class="event-modal-title"></span>
                        <span id="event-modal-subtitle" class="event-modal-subtitle"></span>
                    </span>
                </span>
                <span id="event-modal-close" class="event-modal-close">
                    <svg style="width: 32px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <div id="event-modal-body" class="event-modal-body"></div>
        </div>
		<?php
		// Конец вставки HTML
		return ob_get_clean();
	}
}

new Events_API_Plugin();
