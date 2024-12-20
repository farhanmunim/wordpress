<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $wpdb;

// The plugin tables.
$tables = array(
	'sgs_log_visitors',
	'sgs_log_events',
);

$user_2fa_meta = array(
	'configured',
	'secret',
	'qr',
	'backup_codes',
);

// Loop through all tables and delete them.
foreach ( $tables as $table ) {
	$wpdb->query( // phpcs:ignore
		'DROP TABLE IF EXISTS ' . $wpdb->dbname . '.' . $wpdb->prefix . $table // phpcs:ignore
	);
}

// Reset all users 2FA.
foreach ( $user_2fa_meta as $meta ) {
	delete_metadata( 'user', 0, 'sg_security_2fa_' . $meta, '', true );
}

// Delete encryption file.
wp_delete_file( defined( 'SGS_ENCRYPTION_KEY_FILE_PATH' ) ? SGS_ENCRYPTION_KEY_FILE_PATH : WP_CONTENT_DIR . '/sgs_encrypt_key.php' );

// Stop uninstall service if SG Optimizer plugin exists.
if ( file_exists( WP_PLUGIN_DIR . '/sg-cachepress/sg-cachepress.php' ) ) {
	return;
}

// Stop collecting data.
require_once dirname( __FILE__ ) . '/vendor/siteground/siteground-data/src/Settings.php';

use SiteGround_Data\Settings;

$settings = new Settings();

$settings->stop_collecting_data();