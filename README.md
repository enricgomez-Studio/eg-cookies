# EG Cookies

Banner de cookies de enricgomez Studio para WordPress, con Google Consent Mode v2. Sustituye al módulo provisional `cookies` de EG Publicación (2.99.0).

## Qué hace

- **Consentimiento por defecto denegado** en el `<head>`, antes de cualquier etiqueta de Google. Al aceptar se manda `consent update` (analítica → `analytics_storage`; marketing → `ad_storage`, `ad_user_data`, `ad_personalization`).
- **Banner** con Aceptar y Rechazar con el mismo peso visual, y **Configurar** con las categorías (necesarias siempre activas, analítica y marketing desmarcadas por defecto).
- **Un solo aviso de cookies:** detecta otros plugins o banners de cookies y etiquetas de Analytics de más (Ajustes → EG Cookies y clave `conflicts` de `POST /cookies`).
- **Botón flotante** (izquierda por defecto) para cambiar la elección en cualquier momento, también en móvil. Cualquier enlace a `#cookies` (o `#galetes`) también abre el panel.
- **Retirar el consentimiento** borra las cookies de esa categoría (`_ga*`, `_gcl_*`, `_fbp`...) y recarga la página.
- **Scripts bloqueados hasta aceptar**: `<script type="text/plain" data-eg-category="analytics">` (o `marketing`) se ejecuta solo al aceptar esa categoría.
- **Tabla de cookies** de cada web (`[eg_cookies_tabla]`, y `[eg_cookies_boton]` para el botón de cambiar la elección). Se añade sola al final de la página de la política de cookies configurada.
- **Registro de consentimientos** (tabla `wp_eg_cookies_log`): identificador anónimo, decisión por categoría, revisión del aviso, idioma, página, IP anonimizada y navegador. Se ve y se exporta en CSV en Ajustes → EG Cookies; se borra lo que tenga más de 3 años.
- **Clics a Google Analytics**: teléfono (`clic_telefono`), WhatsApp (`clic_whatsapp`), enlaces de reservas (`pedir_cita`) y reglas propias.
- Textos en catalán, castellano e inglés (según el idioma de WPML o del sitio), todos cambiables.

## Configuración

Desde Make, con la clave de EG Publicación: `POST /wp-json/eg/v1/cookies` (la lista de parámetros está en `includes/rest.php`). Sin parámetros devuelve la configuración, la tabla de cookies y el resumen del registro; los cambios solo se previsualizan y se guardan con `"apply": true`.

```json
{"enabled": true, "policy": {"ca": "/politica-de-cookies/", "es": "/es/politica-de-cookies/"}, "color": "#2471FB",
 "booking_hosts": ["reservas.ejemplo.com"], "cookies": ["_gcl_au"], "apply": true}
```

## Instalación

Desde Make con EG Publicación 2.100.0 o superior: `POST /wp-json/eg/v1/plugins` con `{"install": "eg-cookies"}`. Después se actualiza solo desde las releases de este repositorio.

## Cómo trabajamos en este repositorio

1. Antes de cambiar nada: `git pull` de `main`, la última release y este README y el `CHANGELOG.md`.
2. Cada cambio de código (`eg-cookies.php` o `includes/`) sube la versión en el mismo PR: cabecera `Version:` y `EG_COOKIES_VERSION`, iguales. Al subir a `main`, la GitHub Action publica la release `vX.Y.Z` con `eg-cookies.zip`, que es lo que descargan las webs.
3. Cada cambio añade su entrada arriba del todo en `CHANGELOG.md`.
