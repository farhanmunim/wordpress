<?php
/**
 * SiteGround Email Service
 */

namespace SiteGround_Emails;

/**
 * SiteGround_Email_Service class.
 */
class Email_Service {

	/**
	 * Cron Name.
	 *
	 * @since  1.0.0
	 *
	 * @var    string
	 * @access protected
	 */
	protected $sg_cron_name;

	/**
	 * Cron Interval.
	 *
	 * @since  1.0.0
	 *
	 * @var    string
	 * @access protected
	 */
	protected $sg_cron_interval;

	/**
	 * Cron Next Run.
	 *
	 * @var    int timestamp
	 * @access protected
	 */
	protected $sg_cron_next_run;

	/**
	 * Mail Headers.
	 *
	 * @since  1.0.0
	 *
	 * @var    string[]
	 * @access protected
	 */
	protected $sg_mail_headers;

	/**
	 * Recipients list.
	 *
	 * @since  1.0.0
	 *
	 * @var    string[]
	 * @access protected
	 */
	protected $sg_mail_recipients;

	/**
	 * Mail Subject.
	 *
	 * @since  1.0.0
	 *
	 * @var    string
	 * @access protected
	 */
	protected $sg_mail_subject;

	/**
	 * Mail Body.
	 *
	 * @since  1.0.0
	 *
	 * @var    string
	 * @access protected
	 */
	protected $sg_mail_body;

	/**
	 * Mail From Name.
	 *
	 * @since  1.0.1
	 *
	 * @var    string
	 * @access protected
	 */
	protected $sg_mail_from_name;

	/**
	 * Initiate the email service.
	 *
	 * @since 1.0.0
	 *
	 * @param string $sg_cron_name     Name of the Cron Event.
	 * @param string $sg_cron_interval The Cron Event interval.
	 * @param int    $sg_cron_next_run Timestamp for cron schedule next run.
	 * @param array  $mail_args        Message Arguments.
	 */
	public function __construct( $sg_cron_name, $sg_cron_interval, $sg_cron_next_run, $mail_args ) {
		$this->sg_cron_name     = $sg_cron_name;
		$this->sg_cron_interval = $sg_cron_interval;
		$this->sg_cron_next_run = $sg_cron_next_run;

		$this->sg_mail_headers    = array_key_exists( 'headers', $mail_args ) ? $mail_args['headers'] : array( 'Content-Type: text/html; charset=UTF-8' );
		$this->sg_mail_recipients = $mail_args['recipients_option'];
		$this->sg_mail_subject    = $mail_args['subject'];
		$this->sg_mail_body       = $mail_args['body_method'];
		$this->sg_mail_from_name  = array_key_exists( 'from_name', $mail_args ) ? $mail_args['from_name'] : false;
	}

	/**
	 * Handle email.
	 *
	 * @since  1.0.0
	 *
	 * @return bool True on successfull message sent, False on failure.
	 */
	public function sg_handle_email() {
		$receipients = get_option( $this->sg_mail_recipients, array() );

		// Make sure the mail recipients are passed as an array.
		$receipients = is_array( $receipients ) ? $receipients : array( $receipients );

		// Remove any invalid email addresses.
		foreach ( $receipients as $key => $recipient ) {
			if ( false === filter_var( $recipient, FILTER_VALIDATE_EMAIL ) ) {
				unset( $receipients[ $key ] );
			};
		}

		// Generate the message body from the callable method.
		$body = call_user_func( $this->sg_mail_body );

		// Get the specific subject for the SGO email.
		if ( 'sgo_campaign_cron' === $this->sg_cron_name ) {
			$this->sg_mail_subject = call_user_func( $this->sg_mail_subject );
		}

		// Bail if we fail to build the body of the message.
		if ( false === $body ) {
			// Unschedule the event, so we don't make additional actions if the body is empty.
			$this->unschedule_event();

			return false;
		}

		// Apply the from name if it is set.
		if ( false !== $this->sg_mail_from_name ) {
			add_filter( 'wp_mail_from_name', array( $this, 'set_mail_from_name' ) );
		}

		// Sent the email.
		$result = wp_mail(
			$receipients,
			$this->sg_mail_subject,
			$body,
			$this->sg_mail_headers
		);

		// Remove the from name if it is set.
		if ( false !== $this->sg_mail_from_name ) {
			remove_filter( 'wp_mail_from_name', array( $this, 'set_mail_from_name' ) );
		}

		return $result;
	}

	/**
	 * Set "Mail From" name.
	 *
	 * @since 1.0.1
	 *
	 * @return string The Mail From Name.
	 */
	public function set_mail_from_name( $from_name ) {
		return $this->sg_mail_from_name;
	}

	/**
	 * Schedule event.
	 *
	 * @since  1.0.0
	 *
	 * @return bool True if event successfully/already scheduled. False or WP_Error on failure.
	 */
	public function schedule_event() {
		if ( ! wp_next_scheduled( $this->sg_cron_name ) ) {
			return wp_schedule_event( $this->sg_cron_next_run, $this->sg_cron_interval, $this->sg_cron_name );
		}

		return true;
	}

	/**
	 * Unschedule event.
	 *
	 * @since  1.0.0
	 *
	 * @return bool True if event successfully/already unscheduled. False or WP_Error on failure.
	 */
	public function unschedule_event() {
		// Retrieve the next timestamp for the cron event.
		$timestamp = wp_next_scheduled( $this->sg_cron_name );

		// Return true if there is no such event scheduled.
		if ( false === $timestamp ) {
			return true;
		}

		// Unschedule the event.
		return wp_unschedule_event( $timestamp, $this->sg_cron_name );
	}
}
