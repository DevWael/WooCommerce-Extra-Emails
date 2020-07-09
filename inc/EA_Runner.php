<?php
defined( 'ABSPATH' ) || exit; //prevent direct file access.
class EA_Runner {

	public function run_hooks() {
		add_action( 'init', array( $this, 'settings' ) );
		add_action( 'plugins_loaded', array( $this, 'i18n' ) );
	}

	public function settings() {
		$settings = new EA_Settings();
		$settings->build();
	}

	public function i18n() {
		$i18n = new EA_Localize();
		$i18n->load_text_domain();
	}
}