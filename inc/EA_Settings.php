<?php
defined( 'ABSPATH' ) || exit; //prevent direct file access.
class EA_Settings {
	public function build() {
		new EA_Options_Builder( $this->components() );
	}

	public static function get_email_settings( $setting_id ) {
		return get_option( 'ea_settings_' . $setting_id );
	}

	private function components() {
		return array(
			'title'        => 'Email Settings',
			'prefix'       => 'ea_settings_',
			'action'       => 'ea_settings',
			'redirect_url' => admin_url( 'admin.php?page=ea_settings' ),
			'setting_page' => array(
				'parent'      => true, //display as parent or child menu item
				'parent_slug' => '', //required if parent is set to false
				'capability'  => 'manage_options',
				'name'        => esc_html__( 'Email Settings', 'wsd' ),
				'slug'        => 'ea_settings',
				'icon'        => '', //required if parent is set to true
				'position'    => 6, //required if parent is set to true
			),
			'form_args'    => array(
				'id'           => 'ea_settings',
				'class'        => 'ea_settings',
				'nonce_action' => 'ea_settings',
				'nonce_name'   => 'ea_settings',
			),
			'input_args'   => array(
				array(
					'id'    => 'on_hold',
					'type'  => 'email',
					'class' => 'ea_input',
					'label' => __( 'Order on-hold', 'wcea' ),
					//'desc'        => 'input desc',
					//'placeholder' => 'new_input',
				),
				array(
					'id'    => 'processing',
					'type'  => 'email',
					'class' => 'ea_input',
					'label' => __( 'Processing order', 'wcea' ),
					//'desc'        => 'input desc',
					//'placeholder' => 'new_input',
				),
				array(
					'id'    => 'completed',
					'type'  => 'email',
					'class' => 'ea_input',
					'label' => __( 'Completed order', 'wcea' ),
					//'desc'        => 'input desc',
					//'placeholder' => 'new_input',
				),
				array(
					'id'    => 'refunded',
					'type'  => 'email',
					'class' => 'ea_input',
					'label' => __( 'Refunded order', 'wcea' ),
					//'desc'        => 'input desc',
					//'placeholder' => 'new_input',
				),
			)
		);
	}
}