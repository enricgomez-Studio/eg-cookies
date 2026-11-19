<?php
/**
 * Ajustes → EG Cookies: estado, tabla de cookies y registro de consentimientos (con exportación CSV).
 * La configuración se cambia desde Make (POST /wp-json/eg/v1/cookies).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_options_page( 'EG Cookies', 'EG Cookies', 'manage_options', 'eg-cookies', 'eg_cookies_admin_page' );
} );

add_filter( 'plugin_action_links_' . EG_COOKIES_BASENAME, function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=eg-cookies' ) ) . '">Ajustes</a>' );
	return $links;
} );

function eg_cookies_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$c   = eg_cookies_config();
	$log = eg_cookies_log_summary( 50 );
	$csv = wp_nonce_url( admin_url( 'admin-post.php?action=eg_cookies_csv' ), 'eg_cookies_csv' );
	echo '<div class="wrap"><h1>EG Cookies <small style="font-size:13px;color:#646970">v' . esc_html( EG_COOKIES_VERSION ) . '</small></h1>';
	echo '<p>Banner: <strong>' . ( empty( $c['enabled'] ) ? 'desactivado' : 'activo' ) . '</strong> · Revisión del aviso: ' . (int) eg_cookies_revision()
		. ' · Categorías: ' . esc_html( implode( ', ', eg_cookies_categories() ) ) . ' · GA4: ' . esc_html( eg_cookies_ga4_id() ? eg_cookies_ga4_id() : '—' ) . '</p>';
	if ( ! empty( $c['policy'] ) ) {
		echo '<p>Política de cookies: ';
		foreach ( (array) $c['policy'] as $lang => $url ) {
			echo '<a href="' . esc_url( eg_cookies_policy_url( $lang ) ) . '" target="_blank">' . esc_html( $lang ) . '</a> ';
		}
		echo '</p>';
	}
	echo '<p>La configuración se cambia desde Make con EG Publicación (<code>POST /wp-json/eg/v1/cookies</code>). Shortcodes: <code>[eg_cookies_tabla]</code> y <code>[eg_cookies_boton]</code>.</p>';
	$cf = eg_cookies_conflicts();
	echo '<h2>Otros avisos de cookies</h2>';
	if ( $cf['ok'] ) {
		echo '<p>✅ No se ha encontrado ningún otro aviso ni plugin de cookies.</p>';
	} else {
		echo '<div class="notice notice-warning inline"><ul style="list-style:disc;padding-left:20px">';
		foreach ( $cf['plugins'] as $pl ) {
			echo '<li>Plugin activo: ' . esc_html( $pl['name'] ) . ' (<code>' . esc_html( $pl['plugin'] ) . '</code>)</li>';
		}
		foreach ( (array) $cf['html'] as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul></div>';
	}
	echo '<h2>Cookies de la web</h2>' . eg_cookies_table_html( array( 'lang' => 'es' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<h2>Registro de consentimientos</h2>';
	echo '<p>Total: <strong>' . (int) $log['total'] . '</strong> · Aceptan todo: ' . (int) ( $log['all_accepted'] ?? 0 ) . ' · Rechazan todo: ' . (int) ( $log['all_rejected'] ?? 0 )
		. ' · Últimos 30 días: ' . (int) ( $log['last_30_days'] ?? 0 ) . ' <a class="button" href="' . esc_url( $csv ) . '">Exportar CSV</a></p>';
	echo '<table class="widefat striped"><thead><tr><th>Fecha (UTC)</th><th>ID</th><th>Analítica</th><th>Marketing</th><th>Acción</th><th>Rev.</th><th>Idioma</th><th>Página</th><th>IP</th></tr></thead><tbody>';
	foreach ( $log['rows'] as $row ) {
		echo '<tr><td>' . esc_html( $row['created'] ) . '</td><td><code>' . esc_html( $row['consent_id'] ) . '</code></td><td>' . ( $row['analytics'] ? 'Sí' : 'No' ) . '</td><td>' . ( $row['marketing'] ? 'Sí' : 'No' )
			. '</td><td>' . esc_html( $row['action'] ) . '</td><td>' . (int) $row['revision'] . '</td><td>' . esc_html( $row['lang'] ) . '</td><td>' . esc_html( $row['url'] ) . '</td><td>' . esc_html( $row['ip'] ) . '</td></tr>';
	}
	if ( ! $log['rows'] ) {
		echo '<tr><td colspan="9">Todavía no hay consentimientos registrados.</td></tr>';
	}
	echo '</tbody></table></div>';
}

add_action( 'admin_post_eg_cookies_csv', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Sin permiso.' );
	}
	check_admin_referer( 'eg_cookies_csv' );
	global $wpdb;
	$rows = $wpdb->get_results( 'SELECT created, consent_id, analytics, marketing, action, revision, lang, url, ip, ua FROM ' . eg_cookies_log_table() . ' ORDER BY id DESC', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=consentimientos-cookies-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" );
	fputcsv( $out, array( 'fecha_utc', 'consent_id', 'analitica', 'marketing', 'accion', 'revision', 'idioma', 'pagina', 'ip', 'navegador' ) );
	foreach ( (array) $rows as $row ) {
		fputcsv( $out, $row );
	}
	fclose( $out );
	exit;
} );
