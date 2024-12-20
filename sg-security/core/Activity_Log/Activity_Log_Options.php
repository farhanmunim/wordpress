<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Options main class
 */
class Activity_Log_Options extends Activity_Log_Helper {
	/**
	 * Log option update
	 *
	 * @param string $option The option name.
	 *
	 * @since  1.0.0
	 */
	public function log_option_update( $option ) {

		// We don't want to log all option updates, but only the main WordPress optins.
		// Please refer to https://codex.wordpress.org/Option_Reference.
		$log_options = array(
			'comment_max_links',
			'comment_moderation',
			'comments_notify',
			'default_comment_status',
			'default_ping_status',
			'default_pingback_flag',
			'moderation_keys',
			'moderation_notify',
			'require_name_email',
			'thread_comments',
			'thread_comments_depth',
			'show_avatars',
			'avatar_rating',
			'avatar_default',
			'close_comments_for_old_posts',
			'close_comments_days_old',
			'page_comments',
			'comments_per_page',
			'default_comments_page',
			'comment_order',
			'comment_whitelist',
			'admin_email',
			'blogdescription',
			'blogname',
			'comment_registration',
			'date_format',
			'default_role',
			'gmt_offset',
			'home',
			'siteurl',
			'start_of_week',
			'time_format',
			'timezone_string',
			'users_can_register',
			'links_updated_date_format',
			'links_recently_updated_prepend',
			'links_recently_updated_append',
			'links_recently_updated_time',
			'thumbnail_size_w',
			'thumbnail_size_h',
			'thumbnail_crop',
			'medium_size_w',
			'medium_size_h',
			'large_size_w',
			'large_size_h',
			'embed_autourls',
			'embed_size_w',
			'embed_size_h',
			'hack_file',
			'html_type',
			'secret',
			'upload_path',
			'upload_url_path',
			'uploads_use_yearmonth_folders',
			'use_linksupdate',
			'permalink_structure',
			'category_base',
			'tag_base',
			'blog_public',
			'blog_charset',
			'gzipcompression',
			'page_on_front',
			'page_for_posts',
			'posts_per_page',
			'posts_per_rss',
			'rss_language',
			'rss_use_excerpt',
			'show_on_front',
			'template',
			'stylesheet',
			'default_category',
			'default_email_category',
			'default_link_category',
			'default_post_edit_rows',
			'mailserver_login',
			'mailserver_pass',
			'mailserver_port',
			'mailserver_url',
			'ping_sites',
			'use_balanceTags',
			'use_smilies',
			'use_trackback',
			'enable_app',
			'enable_xmlrpc',
			// 'active_plugins',
			'advanced_edit',
			'recently_edited',
			'image_default_link_type',
			'image_default_size',
			'image_default_align',
			'sidebars_widgets',
			'sticky_posts',
			'widget_categories',
			'widget_text',
			'widget_rss',
		);

		// Add a hook, so the users can modify the options list.
		$log_options = apply_filters( 'sg_security_log_options', $log_options );

		// Bail if the option is not in the list.
		if ( ! in_array( $option, $log_options ) ) {
			return;
		}

		$this->log_event( array(
			'activity'    => __( 'Updated Option', 'sg-security' ),
			'description' => __( 'Updated Option', 'sg-security' ) . ' - ' . $option,
			'object_id'   => $option,
			'type'        => 'option',
			'action'      => 'update',
		) );
	}
}
