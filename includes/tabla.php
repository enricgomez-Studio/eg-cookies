<?php
/**
 * Tabla de cookies para la política de cookies.
 *   [eg_cookies_tabla]   tabla con las cookies de la web en el idioma de la página (lang="es" para forzarlo).
 *   [eg_cookies_boton]   botón que abre el panel para cambiar la elección.
 * Con `table_auto` (por defecto activado) la tabla y el botón se añaden solos al final de la página de la política
 * de cookies configurada en `policy`, si la página no lleva ya el shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'eg_cookies_tabla', 'eg_cookies_table_html' );
add_shortcode( 'eg_cookies_boton', 'eg_cookies_button_html' );

function eg_cookies_table_html( $atts = array() ) {
	$atts = shortcode_atts( array( 'lang' => '' ), $atts, 'eg_cookies_tabla' );
	$lang = $atts['lang'] ? sanitize_key( $atts['lang'] ) : eg_cookies_lang();
	$t    = eg_cookies_texts( $lang );
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$rows = '';
	foreach ( eg_cookies_list() as $ck ) {
		$cat   = in_array( $ck['category'] ?? '', array( 'necessary', 'analytics', 'marketing' ), true ) ? $ck['category'] : 'necessary';
		$prov  = (string) ( $ck['provider'] ?? '' );
		$rows .= '<tr><td><code>' . esc_html( $ck['name'] ) . '</code></td>'
			. '<td>' . esc_html( '' !== $prov ? $prov : $t['own'] . ' (' . $host . ')' ) . '</td>'
			. '<td>' . esc_html( eg_cookies_i18n( $ck['purpose'] ?? '', $lang ) ) . '</td>'
			. '<td>' . esc_html( eg_cookies_i18n( $ck['duration'] ?? '', $lang ) ) . '</td>'
			. '<td>' . esc_html( $t[ $cat ] ) . '</td></tr>';
	}
	static $styled = false;
	$css = '';
	if ( ! $styled ) {
		$styled = true;
		$css    = '<style>.eg-ck-table-wrap{overflow-x:auto;margin:1em 0}.eg-ck-table{width:100%;border-collapse:collapse;font-size:.92em}'
			. '.eg-ck-table th,.eg-ck-table td{border:1px solid #d9dee3;padding:8px 10px;text-align:left;vertical-align:top}'
			. '.eg-ck-table th{background:#f4f6f8;font-weight:600}.eg-ck-table code{font-size:.95em;word-break:break-word}</style>';
	}
	return $css . '<div class="eg-ck-table-wrap"><table class="eg-ck-table"><thead><tr>'
		. '<th>' . esc_html( $t['th_name'] ) . '</th><th>' . esc_html( $t['th_provider'] ) . '</th><th>' . esc_html( $t['th_purpose'] ) . '</th>'
		. '<th>' . esc_html( $t['th_duration'] ) . '</th><th>' . esc_html( $t['th_category'] ) . '</th>'
		. '</tr></thead><tbody>' . $rows . '</tbody></table></div>';
}

function eg_cookies_button_html( $atts = array() ) {
	$atts = shortcode_atts( array( 'lang' => '' ), $atts, 'eg_cookies_boton' );
	$t    = eg_cookies_texts( $atts['lang'] ? sanitize_key( $atts['lang'] ) : null );
	return '<p><a href="#cookies" class="eg-ck-open" data-eg-cookies-open role="button">' . esc_html( $t['floating'] ) . '</a></p>';
}

add_filter( 'the_content', function ( $content ) {
	$c = eg_cookies_config();
	if ( ( isset( $c['table_auto'] ) && empty( $c['table_auto'] ) ) || ! is_singular() || doing_action( 'wp_head' ) || (int) get_the_ID() !== (int) get_queried_object_id() ) {
		return $content;
	}
	if ( false !== strpos( $content, 'eg-ck-table' ) || has_shortcode( $content, 'eg_cookies_tabla' ) ) {
		return $content;
	}
	$policy = eg_cookies_policy_url();
	if ( ! $policy || ( empty( $c['policy'] ) && empty( $c['policy_page'] ) ) ) {
		return $content; // Sin política configurada no se adivina la página.
	}
	$want = untrailingslashit( (string) wp_parse_url( $policy, PHP_URL_PATH ) );
	$here = untrailingslashit( (string) wp_parse_url( get_permalink(), PHP_URL_PATH ) );
	if ( $want !== $here ) {
		return $content;
	}
	return $content . eg_cookies_table_html() . eg_cookies_button_html();
}, 20 );
