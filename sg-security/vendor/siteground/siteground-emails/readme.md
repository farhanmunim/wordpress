SiteGround Email Service for WordPress
=========

The script can be used to generate an email and schedule it to be sent on specific time interval. It uses default WP Mail function.

Installation
=====
	
	composer require siteground/siteground-emails

Usage
=====

	use SiteGround_Emails\Email_Service;
	
	// Initiate the Email Service Class.
	$this->email = new Email_Service(
		'email_cron', // *REQUIRED* Type: string. Cron event name.
		'weekly', // *REQUIRED* Type: string. Cron event interval. The default supported recurrences are ‘hourly’, ‘twicedaily’, ‘daily’, and ‘weekly’.
		time(), // *REQUIRED* Type: int. Timestamp used to schedule the cron event.
		array(
			'headers'           => array( '' ); // *Optional* Type: string[].
			'recipients_option' => 'database option where the message receipients are being stored', // *REQUIRED* Type: string[]. List of email addresses.
			'subject'           => 'Message Subject', // *REQUIRED* Type: string. Message subject.
			'body_method'       => array( 'NAMESPACE', 'Generate message body method' ), // *REQUIRED* Type: array. Array of the namespace and the method which will generate the message body.
		)
	);

	// Schedule the event.
	$this->email->schedule_event();

With the above code you will have a weekly cron event sending email to the defined recipients.

Make sure to include the defined Cron name as an action in the plugin and link it to the siteground-emails 'sg_handle_email' method.

In case you want to stop sending the messages you can call the unschedule_event method as follows:

	// Unschedule the event.
	$this->email->unschedule_event();

Notes
=====

Cron name, inteval and timestamp should be defined in the order listed above. Message arguments can be passed in mixed order.

License
=====

GPLv3 http://www.gnu.org/licenses/gpl-3.0.html