<?php
/**
 * Plugin Name:       EG Cookies
 * Description:       Banner de cookies de enricgomez Studio con Google Consent Mode v2: categorías (analítica y marketing) con «Configurar», botón flotante para cambiar la elección, tabla de cookies para la política, registro de consentimientos y medición de clics en Google Analytics. Se configura desde Make a través de EG Publicación (POST /wp-json/eg/v1/cookies). Se actualiza solo desde GitHub.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            enricgomez Studio
 * Author URI:        https://www.enricgomez.com
 * Update URI:        https://github.com/enricgomez/eg-cookies
 * Text Domain:       eg-cookies
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Copyright (C) 2026 EG Creative Studio, S.L. (enricgomez Studio)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'EG_COOKIES_VERSION' ) ) {
	return; // Instalado dos veces.
}

const EG_COOKIES_VERSION = '1.0.0';
const EG_COOKIES_REPO    = 'enricgomez/eg-cookies';
define( 'EG_COOKIES_BASENAME', plugin_basename( __FILE__ ) );
define( 'EG_COOKIES_FILE', __FILE__ );

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/banner.php';
require_once __DIR__ . '/includes/tabla.php';
require_once __DIR__ . '/includes/registro.php';
require_once __DIR__ . '/includes/rest.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/updater.php';

register_activation_hook( __FILE__, 'eg_cookies_activate' );
register_deactivation_hook( __FILE__, 'eg_cookies_deactivate' );

function eg_cookies_activate() {
	eg_cookies_log_install();
	if ( ! wp_next_scheduled( 'eg_cookies_purge' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'eg_cookies_purge' );
	}
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
	}
}

function eg_cookies_deactivate() {
	wp_clear_scheduled_hook( 'eg_cookies_purge' );
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
	}
}
