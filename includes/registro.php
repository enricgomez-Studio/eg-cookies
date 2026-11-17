<?php
/**
 * Registro de consentimientos (prueba ante la AEPD): cada elección del banner se guarda con su identificador
 * anónimo (cookie eg_consent_id), la decisión por categoría, la revisión del aviso, idioma, página, IP anonimizada
 * (último bloque a cero) y navegador. Se borra lo que tenga más de `log_days` días (por defecto 1095, 3 años).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const EG_COOKIES_DB_VERSION = '1';

function eg_cookies_log_table() {
	global $wpdb;
	return $wpdb->prefix . 'eg_cookies_log';
}

function eg_cookies_log_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = eg_cookies_log_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created datetime NOT NULL,
		consent_id varchar(64) NOT NULL DEFAULT '',
		analytics tinyint(1) NOT NULL DEFAULT 0,
		marketing tinyint(1) NOT NULL DEFAULT 0,
		action varchar(10) NOT NULL DEFAULT '',
		revision smallint(5) unsigned NOT NULL DEFAULT 1,
		lang varchar(8) NOT NULL DEFAULT '',
		url varchar(255) NOT NULL DEFAULT '',
		ip varchar(45) NOT NULL DEFAULT '',
		ua varchar(255) NOT NULL DEFAULT '',
		PRIMARY KEY  (id),
		KEY consent_id (consent_id),
		KEY created (created)
	) {$charset};" );
	update_option( 'eg_cookies_db', EG_COOKIES_DB_VERSION, false );
}

// Por si se instala sin pasar por la activación (p. ej. copiando la carpeta).
add_action( 'plugins_loaded', function () {
	if ( EG_COOKIES_DB_VERSION !== get_option( 'eg_cookies_db' ) ) {
		eg_cookies_log_install();
	}
	if ( ! wp_next_scheduled( 'eg_cookies_purge' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'eg_cookies_purge' );
	}
} );

add_action( 'eg_cookies_purge', function () {
	global $wpdb;
	$c    = eg_cookies_config();
	$days = max( 30, (int) ( $c['log_days'] ?? 1095 ) );
	$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . eg_cookies_log_table() . ' WHERE created < %s', gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
} );

function eg_cookies_anon_ip( $ip ) {
	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		return preg_replace( '/\.\d+$/', '.0', $ip );
	}
	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
		$bin = inet_pton( $ip );
		return inet_ntop( substr( $bin, 0, 6 ) . str_repeat( "\0", 10 ) );
	}
	return '';
}

/** Guarda una elección. $data: id, analytics, marketing, rev, action, lang, url. */
function eg_cookies_log_insert( array $data ) {
	global $wpdb;
	$ip = eg_cookies_anon_ip( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	return (bool) $wpdb->insert( eg_cookies_log_table(), array(
		'created'    => gmdate( 'Y-m-d H:i:s' ),
		'consent_id' => substr( preg_replace( '/[^A-Za-z0-9-]/', '', (string) ( $data['id'] ?? '' ) ), 0, 64 ),
		'analytics'  => empty( $data['analytics'] ) ? 0 : 1,
		'marketing'  => empty( $data['marketing'] ) ? 0 : 1,
		'action'     => in_array( $data['action'] ?? '', array( 'accept', 'reject', 'save', 'close' ), true ) ? $data['action'] : '',
		'revision'   => max( 1, min( 65535, (int) ( $data['rev'] ?? 1 ) ) ),
		'lang'       => substr( sanitize_key( (string) ( $data['lang'] ?? '' ) ), 0, 8 ),
		'url'        => substr( esc_url_raw( (string) ( $data['url'] ?? '' ) ), 0, 255 ),
		'ip'         => $ip,
		'ua'         => substr( sanitize_text_field( (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ), 0, 255 ),
	) );
}

/** Resumen: total, aceptan todo, rechazan todo, eligen, últimos 30 días y las últimas filas. */
function eg_cookies_log_summary( $limit = 20, $consent_id = '' ) {
	global $wpdb;
	$table = eg_cookies_log_table();
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return array( 'total' => 0, 'rows' => array(), 'error' => 'Falta la tabla del registro.' );
	}
	$since = gmdate( 'Y-m-d H:i:s', time() - 30 * DAY_IN_SECONDS );
	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$stats = $wpdb->get_row( $wpdb->prepare(
		"SELECT COUNT(*) total, SUM(analytics=1 AND marketing=1) all_accepted, SUM(analytics=0 AND marketing=0) all_rejected,
		 SUM(created >= %s) last_30_days, SUM(created >= %s AND analytics=1) last_30_analytics FROM {$table}", $since, $since ), ARRAY_A );
	$where = $consent_id ? $wpdb->prepare( 'WHERE consent_id = %s', $consent_id ) : '';
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT created, consent_id, analytics, marketing, action, revision, lang, url, ip, ua FROM {$table} {$where} ORDER BY id DESC LIMIT %d", max( 1, min( 500, (int) $limit ) ) ), ARRAY_A );
	// phpcs:enable
	return array(
		'total'             => (int) $stats['total'],
		'all_accepted'      => (int) $stats['all_accepted'],
		'all_rejected'      => (int) $stats['all_rejected'],
		'last_30_days'      => (int) $stats['last_30_days'],
		'last_30_analytics' => (int) $stats['last_30_analytics'],
		'rows'              => $rows,
	);
}
