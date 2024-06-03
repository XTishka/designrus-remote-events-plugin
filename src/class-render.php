<?php

// src/class-render.php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Events_API_Render {
	public function __construct() {
		add_shortcode('display_events', array($this, 'display_events_shortcode'));
	}

	public function get_text_color($hexcolor) {
		$r = hexdec(substr($hexcolor, 0, 2));
		$g = hexdec(substr($hexcolor, 2, 2));
		$b = hexdec(substr($hexcolor, 4, 2));
		$brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
		return ($brightness > 170) ? '202124' : 'ffffff';
	}

	public function display_events_shortcode() {
		$source = sanitize_text_field(get_option('source'));
		$response = wp_remote_get(trailingslashit($source) . 'wp-json/events-api/v1/events');
		$events = json_decode(wp_remote_retrieve_body($response), true);

		if (empty($events)) {
			return __('No events found.', 'events-api-plugin');
		}

		ob_start(); // Начало буферизации вывода
		?>
        <div class="container">
            <div class="row">
                <div class="coming-soon-events">
                    <div class="header-block">
                        <p class="header"><?php echo __('Coming soon', 'events-api-plugin'); ?></p>
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
						echo $this->display_event($event);
					endforeach; ?>
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

	public function display_event($event) {
		$start_day = date('d', strtotime($event['start_date']));
		$start_month = date_i18n('F', strtotime($event['start_date']));
		$text_color = $this->get_text_color($event['background_color']);
		ob_start();
		?>
        <div class="event" style="background: #<?php echo esc_attr($event['background_color']); ?>; color: #<?php echo esc_attr($text_color); ?>" data-content="<?php echo esc_attr($event['content']); ?>">
            <div class="date">
                <span class="day"><?php echo esc_html($start_day); ?></span>
                <span class="month"><?php echo esc_html($start_month); ?></span>
            </div>
            <div class="content">
                <span class="title"><?php echo esc_html($event['title']); ?></span>
                <span class="subtitle"><?php echo esc_html($event['subtitle']); ?></span>
            </div>
        </div>
		<?php
		return ob_get_clean();
	}
}

new Events_API_Render();
