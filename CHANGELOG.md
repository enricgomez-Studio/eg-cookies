# Registro de cambios de EG Cookies

De lo más nuevo a lo más antiguo. Cada entrada indica la versión, la fecha (UTC), el PR, qué cambia y a qué webs afecta.

## 1.0.3 · 19/11/2026 · (Claude, a petición de Enric)

- EG Cookies tiene que ser el único aviso de cookies de la web: detecta plugins de cookies conocidos activos (aviso en el escritorio) y revisa la portada por si hay otro «consent default» de Google, otro banner conocido o una etiqueta de Google Analytics de más. Se ve en Ajustes → EG Cookies y en `POST /cookies` (clave `conflicts`; `check: false` para no revisarlo).
- Idioma: los códigos regionales de WPML (`en-gb`, `pt-br`) usan los textos y la política del idioma base (`en`, `pt`).

## 1.0.2 · 19/11/2026 · (Claude, a petición de Enric)

- Los enlaces a `#galetes` también abren el panel de cookies, como `#cookies`. Así las políticas en catalán que ya enlazan «configurar galetes» funcionan sin tocarlas (bowling-golf.cat).

## 1.0.1 · 17/11/2026 · (Claude, a petición de Enric)

- El repositorio pasa a la organización del estudio: `enricgomez-Studio/eg-cookies`. Las actualizaciones se leen de ahí (GitHub redirige la dirección antigua, así que las webs con la 1.0.0 llegan igualmente a esta versión).
- Webs: sonovet.net.

## 1.0.0 · 13/11/2026 · (Claude, a petición de Enric)

- Primera versión, a partir del módulo provisional `cookies` de EG Publicación 2.99.0 (copia su configuración al activarse).
- Categorías analítica y marketing con «Configurar», botón flotante, tabla de cookies para la política, registro de consentimientos con exportación CSV, scripts bloqueados hasta aceptar, revisión del aviso y textos ca/es/en.
- Webs: sonovet.net.
