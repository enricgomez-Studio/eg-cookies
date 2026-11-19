<?php
/**
 * Detecta otros avisos de cookies en la web, para que EG Cookies sea el único.
 *
 * - Plugins de cookies conocidos que estén activos.
 * - En la portada: otro «consent default» de Google, otro banner conocido o una etiqueta
 *   de Google Analytics que no sea la de EG Publicación (las que se cargarían sin pedir permiso).
 *
 * Se ve en Ajustes → EG Cookies y en la respuesta de POST /eg/v1/cookies (clave «conflicts»).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Carpetas de plugins de cookies conocidos => nombre. */
function eg_cookies_known_plugins() {
	return array(
		'cookie-law-info'            => 'CookieYes / GDPR Cookie Consent',
		'cookie-notice'              => 'Cookie Notice & Compliance',
		'complianz-gdpr'             => 'Complianz',
		'complianz-gdpr-premium'     => 'Complianz Premium',
		'cookiebot'                  => 'Cookiebot',
		'gdpr-cookie-compliance'     => 'GDPR Cookie Compliance (Moove)',
		'real-cookie-banner'         => 'Real Cookie Banner',
		'real-cookie-banner-pro'     => 'Real Cookie Banner Pro',
		'borlabs-cookie'             => 'Borlabs Cookie',
		'iubenda-cookie-law-solution' => 'iubenda',
		'cookie-script-com'          => 'Cookie-Script',
		'uk-cookie-consent'          => 'Termly',
		'ga-cookie-consent'          => 'GA Cookie Consent',
		'beautiful-and-responsive-cookie-consent' => 'Beautiful Cookie Consent Banner',
		'pixelyoursite'              => 'PixelYourSite (consentimiento propio)',
	);
}

/** Marcas en el HTML de banners conocidos (id o clase) => nombre. */
function eg_cookies_known_markers() {
	return array(
		'cookie-law-info-bar'     => 'CookieYes',
		'cky-consent-container'   => 'CookieYes',
		'cookie-notice-container' => 'Cookie Notice',
		'cmplz-cookiebanner'      => 'Complianz',
		'CybotCookiebotDialog'    => 'Cookiebot',
		'moove_gdpr_cookie_info_bar' => 'GDPR Cookie Compliance',
		'real-cookie-banner'      => 'Real Cookie Banner',
		'BorlabsCookie'           => 'Borlabs Cookie',
		'iubenda-cs-banner'       => 'iubenda',
		'cookieconsent:'          => 'Cookie Consent (Osano)',
		'id="bg-gal"'             => 'Bowling Golf · Nucli (galetes.php)',
	);
}

/** Plugins de cookies activos que no son EG Cookies. */
function eg_cookies_conflict_plugins() {
	$active = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
	}
	$known = eg_cookies_known_plugins();
	$found = array();
	foreach ( $active as $file ) {
		$dir = strtok( (string) $file, '/' );
		if ( isset( $known[ $dir ] ) ) {
			$found[] = array( 'plugin' => $file, 'name' => $known[ $dir ] );
		}
	}
	return $found;
}

/**
 * Revisa el HTML de la portada (sin caché). Devuelve null si no se puede leer.
 *
 * @return array|null
 */
function eg_cookies_conflict_html() {
	$url = add_query_arg( 'eg_ck_check', time(), home_url( '/' ) );
	$res = wp_remote_get( $url, array(
		'timeout'   => 15,
		'sslverify' => false,
		'headers'   => array( 'Cache-Control' => 'no-cache' ),
		'cookies'   => array(),
	) );
	if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
		return null;
	}
	$html  = (string) wp_remote_retrieve_body( $res );
	$found = array();

	// Otro «consent default» además del de EG Cookies.
	$defaults = preg_match_all( "/gtag\\(\\s*['\"]consent['\"]\\s*,\\s*['\"]default['\"]/", $html );
	$ours     = preg_match_all( '/<script[^>]*data-eg-cookies[^>]*>[^<]*gtag\(\s*[\'"]consent[\'"]\s*,\s*[\'"]default/', $html );
	if ( $defaults > $ours ) {
		$found[] = 'Otro aviso fija el consentimiento de Google por su cuenta (' . ( $defaults - $ours ) . ' «consent default» de más).';
	}
	foreach ( eg_cookies_known_markers() as $marker => $name ) {
		if ( false !== strpos( $html, $marker ) ) {
			$found[] = 'Banner de ' . $name . '.';
		}
	}
	// Etiquetas de Google Analytics que no son la de EG Publicación ni la de la configuración.
	if ( preg_match_all( '/gtag\/js\?id=(G-[A-Z0-9]+)/', $html, $m ) ) {
		$tags = array_count_values( $m[1] );
		$own  = eg_cookies_ga4_id();
		foreach ( $tags as $id => $n ) {
			if ( $id !== $own || $n > 1 ) {
				$found[] = 'Google Analytics ' . $id . ' se carga ' . $n . ' vez(es)' . ( $id !== $own ? ' y no es la ID configurada (' . ( $own ? $own : 'ninguna' ) . ')' : '' ) . '.';
			}
		}
	}
	if ( false !== strpos( $html, 'googletagmanager.com/gtm.js' ) ) {
		$found[] = 'Hay Google Tag Manager: comprueba que sus etiquetas respetan el consentimiento.';
	}
	return $found;
}

/** Resumen para la respuesta de la API y la pantalla de ajustes. */
function eg_cookies_conflicts( $check_html = true ) {
	$plugins = eg_cookies_conflict_plugins();
	$html    = $check_html && eg_cookies_enabled() ? eg_cookies_conflict_html() : array();
	return array(
		'ok'      => ! $plugins && ! $html,
		'plugins' => $plugins,
		'html'    => null === $html ? 'No se ha podido leer la portada.' : $html,
	);
}

// Aviso en el escritorio si hay otro plugin de cookies activo.
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$plugins = eg_cookies_conflict_plugins();
	if ( ! $plugins ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>EG Cookies:</strong> hay otro plugin de cookies activo ('
		. esc_html( implode( ', ', wp_list_pluck( $plugins, 'name' ) ) )
		. '). Desactívalo para que no salgan dos avisos ni se cargue nada sin permiso.</p></div>';
} );
