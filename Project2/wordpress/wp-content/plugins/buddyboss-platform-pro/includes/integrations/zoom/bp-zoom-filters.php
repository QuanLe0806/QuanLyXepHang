<?php
/**
 * Zoom integration filters
 *
 * @package BuddyBoss\Zoom
 * @since   1.0.4
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_filter( 'bbp_pro_core_install', 'bp_zoom_pro_core_install_zoom_integration' );
add_filter( 'bp_email_get_schema', 'bp_zoom_email_schema', 10 );
add_filter( 'bp_email_get_type_schema', 'bp_zoom_email_type_schema', 10 );
add_filter( 'bp_email_set_tokens', 'bp_zoom_set_email_tokens', 99, 3 );

/**
 * Install or upgrade zoom integration.
 *
 * @since 1.0.4
 */
function bp_zoom_pro_core_install_zoom_integration() {
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$switched_to_root_blog = false;

	// Make sure the current blog is set to the root blog.
	if ( ! bp_is_root_blog() ) {
		switch_to_blog( bp_get_root_blog_id() );
		$switched_to_root_blog = true;
	}

	$sql             = array();
	$charset_collate = $GLOBALS['wpdb']->get_charset_collate();
	$bp_prefix       = bp_core_get_table_prefix();

	$sql[] = "CREATE TABLE {$bp_prefix}bp_zoom_meetings (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				group_id bigint(20) NOT NULL,
				activity_id bigint(20) NOT NULL,
				user_id bigint(20) NOT NULL,
				host_id varchar(150) NOT NULL,
				type int(10) NOT NULL DEFAULT 2,
				title varchar(300) NOT NULL,
				description varchar(800) NULL,
				start_date datetime NOT NULL,
				start_date_utc datetime NOT NULL,
				timezone varchar(150) NOT NULL,
				password varchar(150) NOT NULL,
				duration int(11) NOT NULL,
				join_before_host bool DEFAULT 0,
				host_video bool DEFAULT 0,
				participants_video bool DEFAULT 0,
				mute_participants bool DEFAULT 0,
				waiting_room bool DEFAULT 0,
				meeting_authentication bool DEFAULT 0,
				recurring bool DEFAULT 0,
				auto_recording varchar(75) DEFAULT 'none',
				alternative_host_ids text NULL,
				meeting_id varchar(150) NOT NULL,
				hide_sitewide bool DEFAULT 0,
				parent varchar(150) DEFAULT 0,
				zoom_type varchar(150) DEFAULT 'meeting',
				alert int(11) DEFAULT 0,
				KEY group_id (group_id),
				KEY activity_id (activity_id),
				KEY meeting_id (meeting_id)
			) {$charset_collate};";

	$sql[] = "CREATE TABLE {$bp_prefix}bp_zoom_meeting_meta (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				meeting_id bigint(20) NOT NULL,
				meta_key varchar(255) DEFAULT NULL,
				meta_value longtext DEFAULT NULL,
				KEY meeting_id (meeting_id),
				KEY meta_key (meta_key(191))
			) {$charset_collate};";

	$sql[] = "CREATE TABLE {$bp_prefix}bp_zoom_recordings (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				recording_id varchar(255) NOT NULL,
				meeting_id bigint(20) NOT NULL,
				uuid varchar(255) NOT NULL,
				details varchar(800) NULL,
				file_type varchar(800) NULL,
				password varchar(150) NOT NULL,
				start_time datetime NOT NULL,
				KEY recording_id (recording_id),
				KEY meeting_id (meeting_id)
			) {$charset_collate};";

	$sql[] = "CREATE TABLE {$bp_prefix}bp_zoom_webinars (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				group_id bigint(20) NOT NULL,
				activity_id bigint(20) NOT NULL,
				user_id bigint(20) NOT NULL,
				host_id varchar(150) NOT NULL,
				type int(10) NOT NULL DEFAULT 2,
				title varchar(300) NOT NULL,
				description varchar(800) NULL,
				start_date datetime NOT NULL,
				start_date_utc datetime NOT NULL,
				timezone varchar(150) NOT NULL,
				password varchar(150) NOT NULL,
				duration int(11) NOT NULL,
				host_video bool DEFAULT 0,
				panelists_video bool DEFAULT 0,
				meeting_authentication bool DEFAULT 0,
				practice_session bool DEFAULT 0,
				on_demand bool DEFAULT 0,
				recurring bool DEFAULT 0,
				auto_recording varchar(75) DEFAULT 'none',
				alternative_host_ids text NULL,
				webinar_id varchar(150) NOT NULL,
				hide_sitewide bool DEFAULT 0,
				parent varchar(150) DEFAULT 0,
				zoom_type varchar(150) DEFAULT 'webinar',
				alert int(11) DEFAULT 0,
				KEY group_id (group_id),
				KEY activity_id (activity_id),
				KEY webinar_id (webinar_id)
			) {$charset_collate};";

	$sql[] = "CREATE TABLE {$bp_prefix}bp_zoom_webinar_meta (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				webinar_id bigint(20) NOT NULL,
				meta_key varchar(255) DEFAULT NULL,
				meta_value longtext DEFAULT NULL,
				KEY webinar_id (webinar_id),
				KEY meta_key (meta_key(191))
			) {$charset_collate};";

	$sql[] = "CREATE TABLE {$bp_prefix}bp_zoom_webinar_recordings (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				recording_id varchar(255) NOT NULL,
				webinar_id bigint(20) NOT NULL,
				uuid varchar(255) NOT NULL,
				details varchar(800) NULL,
				file_type varchar(800) NULL,
				password varchar(150) NOT NULL,
				start_time datetime NOT NULL,
				KEY recording_id (recording_id),
				KEY webinar_id (webinar_id)
			) {$charset_collate};";

	dbDelta( $sql );

	if ( $switched_to_root_blog ) {
		restore_current_blog();
	}
}

/**
 * Add zoom email schema.
 *
 * @since 1.0.9
 *
 * @param array $schema Email schemas.
 *
 * @return mixed
 */
function bp_zoom_email_schema( $schema ) {

	$schema['zoom-scheduled-meeting-email'] = array(
		/* translators: do not remove {} brackets or translate its contents. */
		'post_title'   => __( '[{{{site.name}}}] {{poster.name}} scheduled a Zoom Meeting in the group: "{{group.name}}"', 'buddyboss-pro' ),
		/* translators: do not remove {} brackets or translate its contents. */
		'post_content' => __( "<a href=\"{{{poster.url}}}\">{{poster.name}}</a> scheduled a Zoom Meeting in the group \"<a href=\"{{{group.url}}}\">{{group.name}}</a>\":\n\n{{{zoom_meeting}}}", 'buddyboss-pro' ),
		/* translators: do not remove {} brackets or translate its contents. */
		'post_excerpt' => __( "{{poster.name}} scheduled a Zoom Meeting in the group \"{{group.name}}\":\n\n{{{zoom_meeting}}}", 'buddyboss-pro' ),
	);

	$schema['zoom-scheduled-webinar-email'] = array(
		/* translators: do not remove {} brackets or translate its contents. */
		'post_title'   => __( '[{{{site.name}}}] {{poster.name}} scheduled a Zoom Webinar in the group: "{{group.name}}"', 'buddyboss-pro' ),
		/* translators: do not remove {} brackets or translate its contents. */
		'post_content' => __( "<a href=\"{{{poster.url}}}\">{{poster.name}}</a> scheduled a Zoom Webinar in the group \"<a href=\"{{{group.url}}}\">{{group.name}}</a>\":\n\n{{{zoom_webinar}}}", 'buddyboss-pro' ),
		/* translators: do not remove {} brackets or translate its contents. */
		'post_excerpt' => __( "{{poster.name}} scheduled a Zoom Webinar in the group \"{{group.name}}\":\n\n{{{zoom_webinar}}}", 'buddyboss-pro' ),
	);

	return $schema;
}

/**
 * Zoom email type schema.
 *
 * @since 1.0.9
 *
 * @param array $types Types.
 *
 * @return mixed
 */
function bp_zoom_email_type_schema( $types ) {

	$types['zoom-scheduled-meeting-email'] = array(
		'description' => __( 'Member scheduled the Zoom Meeting in the group.', 'buddyboss-pro' ),
		'unsubscribe' => array(
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key' => 'notification_zoom_meeting_scheduled',
			'message'  => __( 'You will no longer receive emails when someone schedules meeting in the group.', 'buddyboss-pro' ),
		),
	);

	$types['zoom-scheduled-webinar-email'] = array(
		'description' => __( 'Member scheduled the Zoom Webinar in the group.', 'buddyboss-pro' ),
		'unsubscribe' => array(
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key' => 'notification_zoom_webinar_scheduled',
			'message'  => __( 'You will no longer receive emails when someone schedules webinar in the group.', 'buddyboss-pro' ),
		),
	);

	return $types;
}

/**
 * Zoom set email tokens
 *
 * @param array     $formatted_tokens Formatted tokens.
 * @param array     $tokens           Tokens.
 * @param \BP_Email $bp_email         Email class.
 *
 * @return array
 * @since 1.0.9
 */
function bp_zoom_set_email_tokens( $formatted_tokens, $tokens, $bp_email ) {
	if ( isset( $tokens['zoom_meeting'] ) ) {
		$email_content_html      = $bp_email->get_content_html();
		$email_content_plaintext = $bp_email->get_content_plaintext();

		if ( false !== strpos( $email_content_html, 'zoom_meeting' ) || false !== strpos( $email_content_plaintext, 'zoom_meeting' ) ) {
			$token_output = call_user_func( 'bp_zoom_meeting_email_token_zoom_meeting', $bp_email, $formatted_tokens, $tokens );
			$formatted_tokens[ sanitize_text_field( 'zoom_meeting' ) ] = $token_output;
		}
	}

	if ( isset( $tokens['zoom_webinar'] ) ) {
		$email_content_html      = $bp_email->get_content_html();
		$email_content_plaintext = $bp_email->get_content_plaintext();

		if ( false !== strpos( $email_content_html, 'zoom_webinar' ) || false !== strpos( $email_content_plaintext, 'zoom_webinar' ) ) {
			$token_output = call_user_func( 'bp_zoom_webinar_email_token_zoom_webinar', $bp_email, $formatted_tokens, $tokens );
			$formatted_tokens[ sanitize_text_field( 'zoom_webinar' ) ] = $token_output;
		}
	}

	return $formatted_tokens;
}

