<?php

// src/class-api.php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Events_API_Assets {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
	}

	public function enqueue_styles_and_scripts() {
		wp_enqueue_style( 'events-api-style', plugins_url( '../assets/css/style.css', __FILE__ ) );
		wp_enqueue_script( 'events-api-script', plugins_url( '../assets/js/script.js', __FILE__ ), array( 'jquery' ), null, true );
		wp_localize_script( 'events-api-script', 'events_api', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'load_events_nonce' )
		) );
	}
}

new Events_API_Assets();
