<?php
defined( 'ABSPATH' ) || exit; //prevent direct file access.
class EA_Localize {

	public function load_text_domain(){
		load_plugin_textdomain(
			'wcea',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

}