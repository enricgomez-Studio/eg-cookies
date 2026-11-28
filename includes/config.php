<?php
/**
 * Configuración, textos por idioma y catálogo de cookies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const EG_COOKIES_OPTION = 'eg_cookies';

/** Textos por defecto (ca, es, en). Cualquiera se cambia con `texts` en la configuración. */
function eg_cookies_default_texts() {
	return array(
		'ca' => array(
			'title'          => 'Galetes',
			'text'           => "Fem servir galetes per saber com s'utilitza la web i millorar-la. Només s'activen si les acceptes.",
			'close'          => 'Tancar i rebutjar',
			'accept'         => 'Acceptar',
			'reject'         => 'Rebutjar',
			'settings'       => 'Configurar',
			'save'           => 'Desar',
			'policy'         => 'Política de cookies',
			'floating'       => 'Configurar les galetes',
			'always'         => 'Sempre actives',
			'necessary'      => 'Necessàries',
			'necessary_desc' => 'Fan que la web funcioni i recorden la teva elecció sobre les galetes. No es poden desactivar.',
			'analytics'      => 'Analítica',
			'analytics_desc' => 'Ens diuen, de manera agregada, quantes visites rebem i quines pàgines es consulten, per millorar la web.',
			'marketing'      => 'Màrqueting',
			'marketing_desc' => "Serveixen per mesurar i personalitzar els anuncis que veus en altres webs i xarxes.",
			'th_name'        => 'Galeta',
			'th_provider'    => 'Proveïdor',
			'th_purpose'     => 'Finalitat',
			'th_duration'    => 'Durada',
			'th_category'    => 'Tipus',
			'own'            => 'Pròpia',
		),
		'es' => array(
			'title'          => 'Cookies',
			'text'           => 'Usamos cookies para saber cómo se usa la web y mejorarla. Solo se activan si las aceptas.',
			'close'          => 'Cerrar y rechazar',
			'accept'         => 'Aceptar',
			'reject'         => 'Rechazar',
			'settings'       => 'Configurar',
			'save'           => 'Guardar',
			'policy'         => 'Política de cookies',
			'floating'       => 'Configurar cookies',
			'always'         => 'Siempre activas',
			'necessary'      => 'Necesarias',
			'necessary_desc' => 'Hacen que la web funcione y recuerdan tu elección sobre las cookies. No se pueden desactivar.',
			'analytics'      => 'Analítica',
			'analytics_desc' => 'Nos dicen, de forma agregada, cuántas visitas recibimos y qué páginas se consultan, para mejorar la web.',
			'marketing'      => 'Marketing',
			'marketing_desc' => 'Sirven para medir y personalizar los anuncios que ves en otras webs y redes.',
			'th_name'        => 'Cookie',
			'th_provider'    => 'Proveedor',
			'th_purpose'     => 'Finalidad',
			'th_duration'    => 'Duración',
			'th_category'    => 'Tipo',
			'own'            => 'Propia',
		),
		'en' => array(
			'title'          => 'Cookies',
			'text'           => 'We use cookies to understand how the site is used and improve it. They are only set if you accept them.',
			'close'          => 'Close and reject',
			'accept'         => 'Accept',
			'reject'         => 'Reject',
			'settings'       => 'Settings',
			'save'           => 'Save',
			'policy'         => 'Cookie policy',
			'floating'       => 'Cookie settings',
			'always'         => 'Always active',
			'necessary'      => 'Necessary',
			'necessary_desc' => 'They make the site work and remember your cookie choice. They cannot be disabled.',
			'analytics'      => 'Analytics',
			'analytics_desc' => 'They tell us, in aggregate, how many visits we get and which pages are viewed, so we can improve the site.',
			'marketing'      => 'Marketing',
			'marketing_desc' => 'They are used to measure and personalise the ads you see on other sites and networks.',
			'th_name'        => 'Cookie',
			'th_provider'    => 'Provider',
			'th_purpose'     => 'Purpose',
			'th_duration'    => 'Duration',
			'th_category'    => 'Type',
			'own'            => 'Own',
		),
	);
}

/**
 * Catálogo de cookies conocidas. En la configuración, `cookies` admite estas claves o fichas propias:
 * {"name", "provider", "category": necessary|analytics|marketing, "purpose": {"ca","es"}, "duration": {"ca","es"}}.
 */
function eg_cookies_catalog() {
	$ga4 = eg_cookies_ga4_id();
	$ga_session = $ga4 ? '_ga_' . preg_replace( '/^G-/', '', $ga4 ) : '_ga_*';
	return array(
		'eg_consent'    => array( 'name' => 'eg_consent', 'provider' => '', 'category' => 'necessary',
			'purpose'  => array( 'ca' => 'Guarda la teva elecció sobre les galetes.', 'es' => 'Guarda tu elección sobre las cookies.', 'en' => 'Stores your cookie choice.' ),
			'duration' => array( 'ca' => '6 mesos', 'es' => '6 meses', 'en' => '6 months' ) ),
		'eg_consent_id' => array( 'name' => 'eg_consent_id', 'provider' => '', 'category' => 'necessary',
			'purpose'  => array( 'ca' => 'Identificador anònim de la teva elecció, per al registre de consentiments.', 'es' => 'Identificador anónimo de tu elección, para el registro de consentimientos.', 'en' => 'Anonymous ID of your choice, for the consent log.' ),
			'duration' => array( 'ca' => '6 mesos', 'es' => '6 meses', 'en' => '6 months' ) ),
		'_ga'           => array( 'name' => '_ga', 'provider' => 'Google Analytics', 'category' => 'analytics',
			'purpose'  => array( 'ca' => 'Distingeix els visitants de manera anònima per comptar les visites.', 'es' => 'Distingue a los visitantes de forma anónima para contar las visitas.', 'en' => 'Distinguishes visitors anonymously to count visits.' ),
			'duration' => array( 'ca' => '2 anys', 'es' => '2 años', 'en' => '2 years' ) ),
		'_ga_session'   => array( 'name' => $ga_session, 'provider' => 'Google Analytics', 'category' => 'analytics',
			'purpose'  => array( 'ca' => 'Manté l\'estat de la visita per a Google Analytics 4.', 'es' => 'Mantiene el estado de la visita para Google Analytics 4.', 'en' => 'Keeps the session state for Google Analytics 4.' ),
			'duration' => array( 'ca' => '2 anys', 'es' => '2 años', 'en' => '2 years' ) ),
		'_gcl_au'       => array( 'name' => '_gcl_au', 'provider' => 'Google Ads', 'category' => 'marketing',
			'purpose'  => array( 'ca' => 'Mesura l\'eficàcia dels anuncis de Google.', 'es' => 'Mide la eficacia de los anuncios de Google.', 'en' => 'Measures the effectiveness of Google ads.' ),
			'duration' => array( 'ca' => '3 mesos', 'es' => '3 meses', 'en' => '3 months' ) ),
		'_fbp'          => array( 'name' => '_fbp', 'provider' => 'Meta', 'category' => 'marketing',
			'purpose'  => array( 'ca' => 'Mesura i personalitza els anuncis de Facebook i Instagram.', 'es' => 'Mide y personaliza los anuncios de Facebook e Instagram.', 'en' => 'Measures and personalises Facebook and Instagram ads.' ),
			'duration' => array( 'ca' => '3 mesos', 'es' => '3 meses', 'en' => '3 months' ) ),
		'wpml'          => array( 'name' => 'wp-wpml_current_language', 'provider' => '', 'category' => 'necessary',
			'purpose'  => array( 'ca' => 'Recorda l\'idioma de la web.', 'es' => 'Recuerda el idioma de la web.', 'en' => 'Remembers the site language.' ),
			'duration' => array( 'ca' => '1 dia', 'es' => '1 día', 'en' => '1 day' ) ),
		'woocommerce'   => array( 'name' => 'woocommerce_cart_hash, woocommerce_items_in_cart, wp_woocommerce_session_*', 'provider' => '', 'category' => 'necessary',
			'purpose'  => array( 'ca' => 'Recorden el contingut del carretó de la botiga.', 'es' => 'Recuerdan el contenido del carrito de la tienda.', 'en' => 'Remember the contents of the shop cart.' ),
			'duration' => array( 'ca' => 'Sessió / 2 dies', 'es' => 'Sesión / 2 días', 'en' => 'Session / 2 days' ) ),
	);
}

/** Configuración guardada. La primera vez copia la del módulo provisional de EG Publicación (2.99.0). */
function eg_cookies_config() {
	$c = get_option( EG_COOKIES_OPTION, null );
	if ( null === $c ) {
		$old = get_option( 'eg_pub_cookies', array() );
		$c   = is_array( $old ) ? $old : array();
		update_option( EG_COOKIES_OPTION, $c, true );
	}
	return is_array( $c ) ? $c : array();
}

function eg_cookies_enabled() {
	$c = eg_cookies_config();
	return ! empty( $c['enabled'] );
}

/** Categorías opcionales que se ofrecen en «Configurar». */
function eg_cookies_categories() {
	$c = eg_cookies_config();
	$cats = isset( $c['categories'] ) ? (array) $c['categories'] : array( 'analytics', 'marketing' );
	return array_values( array_intersect( array( 'analytics', 'marketing' ), $cats ) );
}

/** Revisión del aviso: si sube, se vuelve a pedir el consentimiento a todo el mundo. */
function eg_cookies_revision() {
	$c = eg_cookies_config();
	return max( 1, (int) ( $c['revision'] ?? 1 ) );
}

function eg_cookies_ga4_id() {
	$c  = eg_cookies_config();
	$id = (string) ( $c['ga4'] ?? '' );
	if ( '' === $id ) {
		$id = (string) get_option( 'eg_pub_ga4_id', '' );
	}
	return strtoupper( $id );
}

function eg_cookies_lang() {
	$lang = apply_filters( 'wpml_current_language', null );
	if ( ! $lang ) {
		$lang = get_locale();
	}
	// «en-gb», «pt-br», «es_ES»… → «en», «pt», «es».
	return substr( sanitize_key( str_replace( '_', '-', (string) $lang ) ), 0, 2 );
}

/** Textos en el idioma actual, con los cambios de la configuración. */
function eg_cookies_texts( $lang = null ) {
	$lang     = $lang ? $lang : eg_cookies_lang();
	$defaults = eg_cookies_default_texts();
	$t        = $defaults[ $lang ] ?? $defaults['en'];
	$c        = eg_cookies_config();
	if ( ! empty( $c['texts'][ $lang ] ) && is_array( $c['texts'][ $lang ] ) ) {
		$t = array_merge( $t, array_filter( array_map( 'strval', $c['texts'][ $lang ] ), 'strlen' ) );
	}
	return $t;
}

/** URL de la política de cookies en el idioma actual. */
function eg_cookies_policy_url( $lang = null ) {
	$lang   = $lang ? $lang : eg_cookies_lang();
	$c      = eg_cookies_config();
	$all    = array_filter( (array) ( $c['policy'] ?? array() ), 'strlen' );
	// Sin WPML (o idioma sin política propia): la del castellano o la primera que haya.
	$policy = (string) ( $all[ $lang ] ?? '' );
	// Página de la política por ID: con WPML, su traducción al idioma actual.
	if ( '' === $policy && ! empty( $c['policy_page'] ) ) {
		$id  = (int) apply_filters( 'wpml_object_id', (int) $c['policy_page'], 'page', true, $lang );
		$url = $id && 'publish' === get_post_status( $id ) ? get_permalink( $id ) : '';
		if ( $url ) {
			return $url;
		}
	}
	if ( '' === $policy ) {
		$policy = (string) ( apply_filters( 'wpml_current_language', null ) ? '' : ( $all['es'] ?? ( $all ? reset( $all ) : '' ) ) );
	}
	if ( '' === $policy ) {
		return (string) get_privacy_policy_url();
	}
	return 0 === strpos( $policy, '/' ) ? home_url( $policy ) : $policy;
}

/**
 * Lista de cookies de la web: las necesarias del banner, las que detecta (GA4, WPML, WooCommerce) y las de
 * `cookies` en la configuración (claves del catálogo o fichas propias). `cookies_auto: false` quita la detección.
 */
/** IDs de Google Ads (AW-…) que EG Cookies configura con gtag, sujetas al consentimiento de marketing. */
function eg_cookies_ads_ids() {
	$c = eg_cookies_config();
	return array_values( array_filter( (array) ( $c['ads'] ?? array() ), function ( $id ) {
		return (bool) preg_match( '/^AW-[0-9]{6,15}$/', (string) $id );
	} ) );
}

function eg_cookies_list() {
	$c       = eg_cookies_config();
	$catalog = eg_cookies_catalog();
	$keys    = array( 'eg_consent', 'eg_consent_id' );
	if ( ! isset( $c['cookies_auto'] ) || ! empty( $c['cookies_auto'] ) ) {
		if ( eg_cookies_ga4_id() ) {
			$keys[] = '_ga';
			$keys[] = '_ga_session';
		}
		if ( eg_cookies_ads_ids() ) {
			$keys[] = '_gcl_au';
		}
		if ( class_exists( 'WooCommerce' ) ) {
			$keys[] = 'woocommerce';
		}
	}
	$list = array();
	foreach ( $keys as $k ) {
		$list[ $catalog[ $k ]['name'] ] = $catalog[ $k ];
	}
	foreach ( (array) ( $c['cookies'] ?? array() ) as $item ) {
		if ( is_string( $item ) && isset( $catalog[ $item ] ) ) {
			$list[ $catalog[ $item ]['name'] ] = $catalog[ $item ];
		} elseif ( is_array( $item ) && ! empty( $item['name'] ) ) {
			$list[ $item['name'] ] = $item;
		}
	}
	$order = array( 'necessary' => 0, 'analytics' => 1, 'marketing' => 2 );
	uasort( $list, function ( $a, $b ) use ( $order ) {
		return ( $order[ $a['category'] ] ?? 3 ) <=> ( $order[ $b['category'] ] ?? 3 );
	} );
	return array_values( $list );
}

/** Texto de un campo traducible: {"ca": "...", "es": "..."} o una cadena. */
function eg_cookies_i18n( $value, $lang ) {
	if ( is_array( $value ) ) {
		return (string) ( $value[ $lang ] ?? $value['es'] ?? reset( $value ) );
	}
	return (string) $value;
}

/**
 * Dominio de las cookies de consentimiento (opción `cookie_domain`), p. ej. «.sonovet.net» para que
 * subdominios como reservas.sonovet.net puedan leer eg_consent. Vacío = solo el host actual.
 * Solo vale si el host de la web termina en ese dominio.
 */
function eg_cookies_cookie_domain() {
	$c   = eg_cookies_config();
	$dom = ltrim( strtolower( (string) ( $c['cookie_domain'] ?? '' ) ), '.' );
	if ( '' === $dom || ! preg_match( '/^[a-z0-9-]+(\.[a-z0-9-]+)+$/', $dom ) ) {
		return '';
	}
	$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	return ( $host === $dom || substr( $host, -strlen( '.' . $dom ) ) === '.' . $dom ) ? '.' . $dom : '';
}
