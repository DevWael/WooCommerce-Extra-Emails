<?php
defined( 'ABSPATH' ) || exit; //prevent direct file access.
class EA_Email_Headers {

	protected $on_hold_email;
	protected $processing_email;
	protected $completed_email;
	protected $refunded_email;

	public function __construct() {
		$this->on_hold_email    = EA_Settings::get_email_settings( 'on_hold' );
		$this->processing_email = EA_Settings::get_email_settings( 'processing' );
		$this->completed_email  = EA_Settings::get_email_settings( 'completed' );
		$this->refunded_email   = EA_Settings::get_email_settings( 'refunded' );
	}

	public function set_email_headers( $headers, $email_id ) {
		if ( $email_id == 'customer_processing_order' && $this->processing_email ) {
			$headers .= 'Bcc: ' . $this->processing_email . "\r\n";

		}
		if ( $email_id == 'customer_completed_order' && $this->completed_email ) {
			$headers .= 'Bcc: ' . $this->completed_email . "\r\n";

		}
		if ( $email_id == 'customer_on_hold_order' && $this->on_hold_email ) {

			$headers .= 'Bcc: ' . $this->on_hold_email . "\r\n";

		}
		if ( $email_id == 'customer_refunded_order' && $this->refunded_email ) {
			$headers .= 'Bcc: ' . $this->refunded_email . "\r\n";
		}

		return $headers;
	}
}