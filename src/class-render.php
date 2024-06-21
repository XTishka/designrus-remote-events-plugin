<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Events_API_Render {
	public function __construct() {
		add_shortcode('display_events', array($this, 'display_events_shortcode'));
	}

	public function display_events_shortcode() {
		$source = sanitize_text_field(get_option('source'));
		$quantity = intval(get_option('quantity'));
		$response = wp_remote_get(trailingslashit($source) . 'wp-json/events-api/v1/events');
		$response_body = json_decode(wp_remote_retrieve_body($response), true);

		if (empty($response_body['events'])) {
			return __('No events found.', 'events-api-plugin');
		}

		$events = $response_body['events'];

		ob_start();
		?>
        <div class="container">
            <div class="row">
                <div class="coming-soon-events">
                    <div class="header-block">
                        <p class="header"><?php echo __('Coming soon', 'events-api-plugin'); ?></p>
                        <div class="navigation">
                            <span class="previous_coming_events" style="display:none;">
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
                    </div>
                    <div class="events-list">
                        <?php foreach ($events as $event) : ?>
                            <?php echo $this->display_event($event); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $this->display_modal($event); ?>
		<?php
		return ob_get_clean();
	}

	public function display_event($event) {
		ob_start();
		?>
        <div class="event" data-event="<?php echo $event['id'] ?>">
            <div class="image-wrapper">
                <img src="<?php echo $event['featured_image'] ?>" alt="">
            </div>
            <div class="content-wrapper">
                <h3><?php echo $event['title'] ?></h3>
                <div class="date details">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                        <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
                    </svg>
                    <span><?php echo $this->display_date($event) ?></span>
                </div>
                <div class="time details">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                    </svg>
                    <span><?php echo $this->display_time($event); ?></span>
                </div>
                <div class="location details">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                    </svg>
                    <span><?php echo $event['location'] ?></span>
                </div>
            </div>
            <div class="event-hidden-subtitle" style="display:none;"><?php echo $event['subtitle']; ?></div>
            <div class="event-hidden-content" style="display:none;"><?php echo $event['content']; ?></div>
        </div>

		<?php
		return ob_get_clean();
	}

    public function display_modal($event) {
        ob_start();
		?>
        <div id="event-overlay" class="event-overlay"></div>
        <div id="event-modal" class="event-modal">
            <div class="event-modal-header">
                <span class="event-modal-header-wrapper">
                    <span id="event-modal-text" class="event-modal-text">
                        <span id="event-modal-title" class="event-modal-title"></span>
                        <span id="event-modal-subtitle" class="event-modal-subtitle"></span>
                        <ul>
                            <li id="event-modal-date">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                    <path d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                                    <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
                                </svg>
                                <span></span>
                            </li>
                            <li id="event-modal-time">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                                </svg>
                                <span></span>
                            </li>
                            <li id="event-modal-location">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                    <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                                </svg>
                                <span></span>
                            </li>
                        </ul>
                    </span>
                </span>
                <span id="event-modal-close" class="event-modal-close">
                    <svg style="width: 32px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <div id="event-modal-image" class="event-modal-image"></div>
            <div id="event-modal-body" class="event-modal-body"></div>
        </div>
		<?php
		return ob_get_clean();
    }

    private function display_date($event) {
        $start_day = date('d', strtotime($event['start_date']));
		$start_month = date_i18n('M', strtotime($event['start_date']));
		$start_year = date_i18n('Y', strtotime($event['start_date']));

        $end_day = date('d', strtotime($event['end_date']));
		$end_month = date_i18n('M', strtotime($event['end_date']));
        $end_year = date_i18n('Y', strtotime($event['start_date']));

        if ($start_year !== $end_year) {
            return sprintf(
                '%d. %s %d - %d. %s %d',
                $start_day,
                $start_month,
                $start_year,
                $end_day,
                $end_month,
                $end_year,
            );
        }

        if ($start_month !== $end_month) {
            return sprintf(
                '%d. %s - %d. %s %d',
                $start_day,
                $start_month,
                $end_day,
                $end_month,
                $end_year,
            );
        }

        if ($start_month === $end_month) {
            if ($start_day !== $end_day)
                return sprintf(
                    '%d - %d. %s %d',
                    $start_day,
                    $end_day,
                    $end_month,
                    $end_year,
                );
            
            if ($start_day === $end_day) {
                return sprintf(
                    '%d. %s %d',
                    $start_day,
                    $end_month,
                    $end_year,
                );
            }
        }

        return __('no date found');
    }

    private function display_time($event) {
        $start_time = new DateTime($event['start_time']);
        $end_time = new DateTime($event['end_time']);
        return $start_time->format('H:i') . ' - ' . $end_time->format('H:i');
    }
 }

new Events_API_Render();
