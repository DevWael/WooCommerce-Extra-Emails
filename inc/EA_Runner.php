<?php
defined( 'ABSPATH' ) || exit; //prevent direct file access.
class EA_Runner {

	public function run_hooks() {
		add_action( 'init', array( $this, 'settings' ) );
		add_action( 'plugins_loaded', array( $this, 'i18n' ) );
		add_filter( 'woocommerce_email_headers', array( $this, 'wc_email' ), 10, 3 );
	}

	public function settings() {
		$settings = new EA_Settings();
		$settings->build();
	}

	public function i18n() {
		$i18n = new EA_Localize();
		$i18n->load_text_domain();
	}

	public function wc_email( $headers, $email_id, $order ) {
		$email_headers = new EA_Email_Headers();

		return $email_headers->set_email_headers( $headers, $email_id, $order );
	}
}