<?php
/**
 * Endpoints.
 *
 *   POST /wp-json/eg-cookies/v1/consent   público: lo llama el banner para el registro de consentimientos.
 *
 *   POST /wp-json/eg/v1/cookies           configuración desde Make, con la clave X-EG-Key de EG Publicación
 *                                         (solo existe si EG Publicación está activo). Sin parámetros devuelve
 *                                         la configuración, la tabla de cookies y el resumen del registro.
 *                                         Los cambios solo se previsualizan; apply:true guarda y vacía la caché.
 *   enabled        true/false: banner y consentimiento por defecto denegado en el <head>.
 *   policy         {"ca": "/politica-de-cookies/", "es": "/es/politica-de-cookies/"}: política por idioma (WPML).
 *   texts          {"ca": {"text", "accept", "reject", "settings", ...}}: cambia los textos (ver config.php).
 *   color          color de los botones (#hex).
 *   categories     ["analytics", "marketing"]: categorías que se ofrecen en «Configurar».
 *   position       "left" | "right": esquina del banner (por defecto "left").
 *   floating       "left" | "right" | false: botón flotante para cambiar la elección (por defecto "left").
 *   cookies        lista de la tabla: claves del catálogo ("_gcl_au", "_fbp", "wpml"...) o fichas
 *                  {"name", "provider", "category", "purpose": {"ca","es"}, "duration": {"ca","es"}}.
 *   cookies_auto   true/false (por defecto true): añade solas GA4 (si hay ID) y WooCommerce.
 *   table_auto     true/false (por defecto true): añade la tabla al final de la página de la política.
 *   ga4            "G-XXXX": ID de GA4 para la tabla (si no, la de EG Publicación /seo-tech).
 *   revision       número o "bump": al subirla se vuelve a pedir el consentimiento a todo el mundo.
 *   log            true/false (por defecto true): registro de consentimientos. log_days: días que se guardan.
 *   events         true/false (por defecto true): clics a GA4 (teléfono, WhatsApp, booking_hosts, rules).
 *   booking_hosts  ["reservas.ejemplo.com"]: enlaces que cuentan como pedir_cita.
 *   rules          [{"event": "soy_clinica", "href": "/contacte/"}]: otros clics, por un trozo del enlace.
 *   log_rows       número de filas del registro en la respuesta (por defecto 10). consent_id: filas de un visitante.
 *   check          true/false (por defecto true): revisa si hay otros avisos de cookies (clave «conflicts»).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'eg-cookies/v1', '/consent', array(
		'methods'             => 'POST',
		'callback'            => 'eg_cookies_rest_consent',
		'permission_callback' => '__return_true',
	) );
	if ( function_exists( 'eg_pub_permission' ) ) {
		register_rest_route( 'eg/v1', '/cookies', array(
			'methods'             => 'POST',
			'callback'            => 'eg_cookies_rest_config',
			'permission_callback' => function ( WP_REST_Request $r ) {
				$ok = eg_pub_permission( $r );
				return ( true === $ok && ! current_user_can( 'manage_options' ) ) ? false : $ok;
			},
		), true );
	}
}, 20 );

function eg_cookies_rest_consent( WP_REST_Request $r ) {
	$c = eg_cookies_config();
	if ( empty( $c['enabled'] ) || ( isset( $c['log'] ) && empty( $c['log'] ) ) ) {
		return new WP_REST_Response( array( 'ok' => false ), 404 );
	}
	// Máximo 30 registros por minuto y IP.
	$key   = 'eg_ck_rl_' . md5( eg_cookies_anon_ip( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ) . gmdate( 'YmdHi' ) );
	$count = (int) get_transient( $key );
	if ( $count >= 30 ) {
		return new WP_REST_Response( array( 'ok' => false ), 429 );
	}
	set_transient( $key, $count + 1, 2 * MINUTE_IN_SECONDS );
	$p = $r->get_json_params();
	$p = is_array( $p ) ? $p : $r->get_params();
	if ( empty( $p['id'] ) ) {
		return new WP_REST_Response( array( 'ok' => false ), 400 );
	}
	return new WP_REST_Response( array( 'ok' => eg_cookies_log_insert( $p ) ), 200 );
}

function eg_cookies_rest_config( WP_REST_Request $r ) {
	$p      = $r->get_params();
	$apply  = ! empty( $p['apply'] );
	$before = eg_cookies_config();
	$after  = $before;

	foreach ( array( 'enabled', 'events', 'cookies_auto', 'table_auto', 'log' ) as $k ) {
		if ( array_key_exists( $k, $p ) ) {
			$after[ $k ] = rest_sanitize_boolean( $p[ $k ] );
		}
	}
	if ( array_key_exists( 'color', $p ) ) {
		$after['color'] = (string) sanitize_hex_color( (string) $p['color'] );
	}
	if ( array_key_exists( 'ga4', $p ) ) {
		$ga4          = strtoupper( trim( (string) $p['ga4'] ) );
		$after['ga4'] = preg_match( '/^G-[A-Z0-9]{4,20}$/', $ga4 ) ? $ga4 : '';
	}
	if ( array_key_exists( 'log_days', $p ) ) {
		$after['log_days'] = max( 30, (int) $p['log_days'] );
	}
	if ( array_key_exists( 'position', $p ) ) {
		$after['position'] = 'right' === $p['position'] ? 'right' : 'left';
	}
	if ( array_key_exists( 'floating', $p ) ) {
		$f                 = $p['floating'];
		$after['floating'] = in_array( $f, array( 'left', 'right' ), true ) ? $f : ( rest_sanitize_boolean( $f ) ? 'left' : false );
	}
	if ( isset( $p['categories'] ) ) {
		$after['categories'] = array_values( array_intersect( array( 'analytics', 'marketing' ), array_map( 'sanitize_key', (array) $p['categories'] ) ) );
	}
	if ( isset( $p['revision'] ) ) {
		$after['revision'] = 'bump' === $p['revision'] ? eg_cookies_revision() + 1 : max( 1, (int) $p['revision'] );
	}
	if ( isset( $p['policy'] ) && is_array( $p['policy'] ) ) {
		foreach ( $p['policy'] as $lang => $url ) {
			$url = trim( (string) $url );
			if ( '' === $url ) {
				unset( $after['policy'][ sanitize_key( $lang ) ] );
				continue;
			}
			$after['policy'][ sanitize_key( $lang ) ] = 0 === strpos( $url, '/' ) ? '/' . ltrim( sanitize_text_field( $url ), '/' ) : esc_url_raw( $url );
		}
	}
	if ( isset( $p['texts'] ) && is_array( $p['texts'] ) ) {
		$allowed = array_keys( eg_cookies_default_texts()['es'] );
		foreach ( $p['texts'] as $lang => $texts ) {
			foreach ( (array) $texts as $k => $v ) {
				if ( in_array( $k, $allowed, true ) && is_scalar( $v ) ) {
					$after['texts'][ sanitize_key( $lang ) ][ $k ] = sanitize_text_field( (string) $v );
				}
			}
		}
	}
	if ( isset( $p['cookies'] ) ) {
		$list = array();
		foreach ( (array) $p['cookies'] as $item ) {
			if ( is_string( $item ) ) {
				$list[] = sanitize_text_field( $item );
				continue;
			}
			if ( ! is_array( $item ) || empty( $item['name'] ) ) {
				continue;
			}
			$clean = array(
				'name'     => sanitize_text_field( (string) $item['name'] ),
				'provider' => sanitize_text_field( (string) ( $item['provider'] ?? '' ) ),
				'category' => in_array( $item['category'] ?? '', array( 'necessary', 'analytics', 'marketing' ), true ) ? $item['category'] : 'necessary',
			);
			foreach ( array( 'purpose', 'duration' ) as $f ) {
				$v = $item[ $f ] ?? '';
				$clean[ $f ] = is_array( $v ) ? array_map( 'sanitize_text_field', array_map( 'strval', $v ) ) : sanitize_text_field( (string) $v );
			}
			$list[] = $clean;
		}
		$after['cookies'] = $list;
	}
	if ( isset( $p['booking_hosts'] ) ) {
		$after['booking_hosts'] = array_values( array_filter( array_map( function ( $h ) {
			return preg_replace( '/[^a-z0-9.\-\/]/', '', strtolower( (string) $h ) );
		}, (array) $p['booking_hosts'] ) ) );
	}
	if ( isset( $p['rules'] ) ) {
		$rules = array();
		foreach ( (array) $p['rules'] as $rule ) {
			$event = sanitize_key( (string) ( $rule['event'] ?? '' ) );
			$href  = sanitize_text_field( (string) ( $rule['href'] ?? '' ) );
			if ( $event && $href ) {
				$rules[] = array( 'event' => $event, 'href' => $href );
			}
		}
		$after['rules'] = $rules;
	}

	$changed = $after !== $before;
	if ( $apply && $changed ) {
		update_option( EG_COOKIES_OPTION, $after, true );
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
	}
	return new WP_REST_Response( array(
		'ok'      => true,
		'version' => EG_COOKIES_VERSION,
		'applied' => $apply && $changed,
		'changed' => $changed,
		'config'  => $after,
		'before'  => $before,
		'cookies' => eg_cookies_list(),
		'ga4'     => eg_cookies_ga4_id(),
		'conflicts' => eg_cookies_conflicts( ! isset( $p['check'] ) || rest_sanitize_boolean( $p['check'] ) ),
		'log'     => eg_cookies_log_summary( (int) ( $p['log_rows'] ?? 10 ), sanitize_text_field( (string) ( $p['consent_id'] ?? '' ) ) ),
	), 200 );
}
