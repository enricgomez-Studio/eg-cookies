<?php
/**
 * Banner, panel «Configurar», botón flotante y Google Consent Mode v2.
 *
 * Cookie `eg_consent` = a{0|1}m{0|1}r{revisión} (analítica, marketing). Los valores `granted` y `denied` del
 * módulo provisional de EG Publicación se leen como todo aceptado o todo rechazado.
 * Scripts de terceros que solo deben cargar con consentimiento: <script type="text/plain" data-eg-category="analytics">
 * (o "marketing"); el banner los activa al aceptar esa categoría.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function eg_cookies_should_print() {
	return eg_cookies_enabled() && ! is_admin() && ! wp_doing_ajax() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& ! is_feed() && ! is_embed() && ! isset( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification
}

/** Consentimiento por defecto: antes que cualquier etiqueta de Google (la de EG Publicación /seo-tech va en la prioridad 1). */
add_action( 'wp_head', function () {
	if ( ! eg_cookies_should_print() ) {
		return;
	}
	$rev = eg_cookies_revision();
	echo "<script data-cfasync=\"false\" nowprocket data-eg-cookies>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}"
		. "(function(){var m=document.cookie.match(/(?:^|; )eg_consent=([^;]+)/),v=m?m[1]:'',a='denied',k='denied',x;"
		. "if(v==='granted'){a=k='granted';}else if((x=v.match(/^a([01])m([01])r(\\d+)/))&&+x[3]>=" . (int) $rev . "){a=x[1]==='1'?'granted':'denied';k=x[2]==='1'?'granted':'denied';}"
		. ( $rev > 1 ? "if(v==='granted'){a=k='denied';}" : '' )
		. "gtag('consent','default',{analytics_storage:a,ad_storage:k,ad_user_data:k,ad_personalization:k,functionality_storage:'granted',security_storage:'granted',wait_for_update:500});"
		. "gtag('set','ads_data_redaction',k!=='granted');})();</script>\n";
}, 0 );

add_action( 'wp_footer', 'eg_cookies_footer', 5 );

function eg_cookies_footer() {
	if ( ! eg_cookies_should_print() ) {
		return;
	}
	$c      = eg_cookies_config();
	$lang   = eg_cookies_lang();
	$t      = eg_cookies_texts( $lang );
	$policy = eg_cookies_policy_url( $lang );
	$cats   = eg_cookies_categories();
	$color  = sanitize_hex_color( (string) ( $c['color'] ?? '' ) );
	$color  = $color ? $color : '#1a73e8';
	$fab    = $c['floating'] ?? 'left';
	$fab    = in_array( $fab, array( 'left', 'right' ), true ) ? $fab : ( false === $fab || 'off' === $fab ? '' : 'left' );
	$cfg    = array(
		'rev'  => eg_cookies_revision(),
		'log'  => ( ! isset( $c['log'] ) || ! empty( $c['log'] ) ) ? esc_url_raw( rest_url( 'eg-cookies/v1/consent' ) ) : '',
		'lang' => $lang,
		'fab'  => (bool) $fab,
	);
	?>
<div id="eg-cookies" class="eg-ck<?php echo 'right' === ( $c['position'] ?? '' ) ? ' eg-ck-right' : ''; ?>" role="dialog" aria-label="<?php echo esc_attr( $t['title'] ); ?>" aria-describedby="eg-ck-text" data-nosnippet hidden>
<button type="button" class="eg-ck-x" data-eg-act="close" aria-label="<?php echo esc_attr( $t['close'] ); ?>" title="<?php echo esc_attr( $t['close'] ); ?>"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
<p class="eg-ck-text" id="eg-ck-text"><?php echo esc_html( $t['text'] ); ?><?php if ( $policy ) : ?> <a href="<?php echo esc_url( $policy ); ?>"><?php echo esc_html( $t['policy'] ); ?></a><?php endif; ?></p>
<div class="eg-ck-prefs" aria-hidden="true"><div class="eg-ck-prefs-in">
<div class="eg-ck-row"><span><strong><?php echo esc_html( $t['necessary'] ); ?></strong><small><?php echo esc_html( $t['necessary_desc'] ); ?></small></span><em><?php echo esc_html( $t['always'] ); ?></em></div>
<?php foreach ( $cats as $cat ) : ?>
<label class="eg-ck-row"><span><strong><?php echo esc_html( $t[ $cat ] ); ?></strong><small><?php echo esc_html( $t[ $cat . '_desc' ] ); ?></small></span><input type="checkbox" role="switch" data-eg-cat="<?php echo esc_attr( $cat ); ?>" tabindex="-1"><i aria-hidden="true"></i></label>
<?php endforeach; ?>
</div></div>
<div class="eg-ck-btns">
<button type="button" class="eg-ck-link" data-eg-act="settings" aria-expanded="false"><?php echo esc_html( $t['settings'] ); ?></button>
<button type="button" class="eg-ck-link" data-eg-act="save" hidden><?php echo esc_html( $t['save'] ); ?></button>
<button type="button" class="eg-ck-btn" data-eg-act="reject"><?php echo esc_html( $t['reject'] ); ?></button>
<button type="button" class="eg-ck-btn" data-eg-act="accept"><?php echo esc_html( $t['accept'] ); ?></button>
</div>
</div>
<?php if ( $fab ) : ?>
<button type="button" id="eg-cookies-fab" class="eg-ck-fab-<?php echo esc_attr( $fab ); ?>" aria-label="<?php echo esc_attr( $t['floating'] ); ?>" title="<?php echo esc_attr( $t['floating'] ); ?>" data-nosnippet hidden><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-4.6-4.6A4 4 0 0 1 12 2Zm-3.5 6a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm-1 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm5.5 1a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm3.5-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg></button>
<?php endif; ?>
<style>
#eg-cookies,#eg-cookies *,#eg-cookies-fab{box-sizing:border-box}
#eg-cookies button,#eg-cookies p,#eg-cookies a,#eg-cookies label,#eg-cookies strong,#eg-cookies small,#eg-cookies em,#eg-cookies-fab{all:unset;box-sizing:border-box}
#eg-cookies{display:block;--eg-ck:<?php echo esc_attr( $color ); ?>;position:fixed;left:20px;right:auto;top:auto;bottom:20px;margin:0;z-index:2147483600;width:400px;max-width:calc(100vw - 40px);max-height:calc(100vh - 40px);overflow:auto;background:#fff;color:#222;border:1px solid rgba(0,0,0,.07);border-radius:14px;box-shadow:0 10px 40px rgba(0,0,0,.16);padding:16px 18px 14px;font:400 13.5px/1.5 inherit;font-family:inherit;letter-spacing:0;text-align:left;text-transform:none;-webkit-font-smoothing:antialiased;animation:egck-in .25s ease-out}
@keyframes egck-in{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
#eg-cookies[hidden],#eg-cookies [hidden],#eg-cookies-fab[hidden]{display:none!important}
#eg-cookies.eg-ck-right{left:auto;right:20px}
#eg-cookies .eg-ck-text{display:block;margin:0 26px 12px 0;font-size:13.5px;line-height:1.5;color:#222}
#eg-cookies a{color:inherit;text-decoration:underline;text-underline-offset:2px;opacity:.7;cursor:pointer;white-space:nowrap}
#eg-cookies a:hover{opacity:1}
#eg-cookies .eg-ck-x{position:absolute;top:10px;right:10px;width:28px;height:28px;border-radius:50%;color:#888;cursor:pointer;display:flex;align-items:center;justify-content:center}
#eg-cookies .eg-ck-x:hover{background:#f2f2f2;color:#222}
#eg-cookies .eg-ck-prefs{display:grid;grid-template-rows:0fr;transition:grid-template-rows .3s ease}
#eg-cookies .eg-ck-prefs>div{overflow:hidden;min-height:0}
#eg-cookies.eg-ck-open .eg-ck-prefs{grid-template-rows:1fr;margin-bottom:12px}
#eg-cookies.eg-ck-open .eg-ck-prefs-in{border-top:1px solid #eee}
#eg-cookies .eg-ck-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:9px 0;border-bottom:1px solid #eee;cursor:pointer;position:relative}
#eg-cookies .eg-ck-row span{display:flex;flex-direction:column;gap:1px}
#eg-cookies .eg-ck-row strong{display:block;font-weight:600;font-size:13px;line-height:1.4;color:#222}
#eg-cookies .eg-ck-row small{display:block;font-size:12px;line-height:1.4;color:#6b6b6b}
#eg-cookies .eg-ck-row em{display:block;font-size:12px;color:#6b6b6b;white-space:nowrap}
#eg-cookies .eg-ck-row input{position:absolute;opacity:0;width:1px;height:1px;margin:0;pointer-events:none}
#eg-cookies .eg-ck-row i{display:block;flex:0 0 34px;height:20px;border-radius:20px;background:#d5d5d5;position:relative;transition:background .2s}
#eg-cookies .eg-ck-row i:after{content:"";position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.2);transition:transform .2s}
#eg-cookies .eg-ck-row input:checked+i{background:var(--eg-ck)}
#eg-cookies .eg-ck-row input:checked+i:after{transform:translateX(14px)}
#eg-cookies .eg-ck-row input:focus-visible+i{outline:2px solid var(--eg-ck);outline-offset:2px}
#eg-cookies .eg-ck-btns{display:flex;align-items:center;gap:8px;width:100%}
#eg-cookies .eg-ck-btn{display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;line-height:1.2;border-radius:8px;padding:9px 16px;background:var(--eg-ck);color:#fff;cursor:pointer;text-align:center;white-space:nowrap}
#eg-cookies .eg-ck-btn:hover{filter:brightness(1.1)}
#eg-cookies .eg-ck-link{display:inline-block;font-size:13px;font-weight:500;line-height:1.2;padding:9px 2px;color:#555;text-decoration:underline;text-underline-offset:3px;cursor:pointer;margin-right:auto;white-space:nowrap}
#eg-cookies .eg-ck-link:hover{color:#000}
#eg-cookies button:focus-visible{outline:2px solid var(--eg-ck);outline-offset:2px}
#eg-cookies-fab{position:fixed;bottom:20px;z-index:2147483500;width:40px;height:40px;border-radius:50%;border:1px solid rgba(0,0,0,.07);display:flex;align-items:center;justify-content:center;background:#fff;color:<?php echo esc_attr( $color ); ?>;box-shadow:0 3px 14px rgba(0,0,0,.16);cursor:pointer;opacity:.85;transition:opacity .2s,transform .2s}
#eg-cookies-fab:hover{opacity:1;transform:scale(1.06)}
#eg-cookies-fab.eg-ck-fab-left{left:20px}#eg-cookies-fab.eg-ck-fab-right{right:20px}
@media (max-width:600px){#eg-cookies,#eg-cookies.eg-ck-right{left:10px;right:10px;bottom:10px;width:auto;max-width:none;padding:14px 14px 12px;border-radius:12px}
#eg-cookies .eg-ck-text{font-size:13px}
#eg-cookies .eg-ck-btn{flex:1 1 0;padding:10px 6px}#eg-cookies .eg-ck-link{margin-right:2px}
#eg-cookies-fab{bottom:12px;width:36px;height:36px}#eg-cookies-fab.eg-ck-fab-left{left:12px}#eg-cookies-fab.eg-ck-fab-right{right:12px}}
@media (prefers-reduced-motion:reduce){#eg-cookies,#eg-cookies *{animation:none!important;transition:none!important}}
</style>
<script data-cfasync="false" nowprocket>(function(cfg){var box=document.getElementById('eg-cookies'),fab=document.getElementById('eg-cookies-fab');if(!box)return;
var prefs=box.querySelector('.eg-ck-prefs'),bSet=box.querySelector('[data-eg-act=settings]'),bSave=box.querySelector('[data-eg-act=save]');
function read(){var m=document.cookie.match(/(?:^|; )eg_consent=([^;]+)/),v=m?m[1]:'',x;if(v==='granted')return{a:1,m:1,r:1};if(v==='denied')return{a:0,m:0,r:1};x=v.match(/^a([01])m([01])r(\d+)/);return x?{a:+x[1],m:+x[2],r:+x[3]}:null;}
function valid(s){return s&&s.r>=cfg.rev;}
function cid(){var m=document.cookie.match(/(?:^|; )eg_consent_id=([A-Za-z0-9-]+)/);if(m)return m[1];return window.crypto&&crypto.randomUUID?crypto.randomUUID():(Date.now().toString(36)+'-'+Math.random().toString(36).slice(2,12));}
function ck(n,v){document.cookie=n+'='+v+'; Max-Age=15552000; path=/; SameSite=Lax'+(location.protocol==='https:'?'; Secure':'');}
function clear(re){var h=location.hostname.replace(/^www\./,''),p=h.split('.'),root=p.slice(-2).join('.');document.cookie.split('; ').forEach(function(c){var n=c.split('=')[0];if(re.test(n)){['',h,'.'+h,'.'+root].forEach(function(d){document.cookie=n+'=; Max-Age=0; path=/'+(d?'; domain='+d:'');});}});}
function run(cat){document.querySelectorAll('script[type="text/plain"][data-eg-category="'+cat+'"]').forEach(function(s){var n=document.createElement('script');for(var i=0;i<s.attributes.length;i++){var at=s.attributes[i];if(at.name!=='type'&&at.name!=='data-eg-category')n.setAttribute(at.name,at.value);}n.text=s.text;s.parentNode.replaceChild(n,s);});}
function apply(s){var a=s.a?'granted':'denied',k=s.m?'granted':'denied';if(window.gtag){gtag('consent','update',{analytics_storage:a,ad_storage:k,ad_user_data:k,ad_personalization:k});gtag('set','ads_data_redaction',!s.m);}if(s.a)run('analytics');if(s.m)run('marketing');
try{document.dispatchEvent(new CustomEvent('eg_cookies',{detail:{analytics:!!s.a,marketing:!!s.m}}));}catch(e){}}
function show(open){box.hidden=false;if(fab)fab.hidden=true;settings(!!open);}
function hide(){box.hidden=true;if(fab&&cfg.fab)fab.hidden=false;}
function settings(open){box.classList.toggle('eg-ck-open',open);prefs.setAttribute('aria-hidden',open?'false':'true');box.querySelectorAll('[data-eg-cat]').forEach(function(i){i.tabIndex=open?0:-1;});bSave.hidden=!open;bSet.hidden=open;bSet.setAttribute('aria-expanded',open?'true':'false');if(open){var s=read(),ok=valid(s);box.querySelectorAll('[data-eg-cat]').forEach(function(i){i.checked=!!(ok&&s[i.getAttribute('data-eg-cat').charAt(0)]);});}}
function save(a,m,act){var prev=read(),s={a:a?1:0,m:m?1:0,r:cfg.rev},id=cid();ck('eg_consent','a'+s.a+'m'+s.m+'r'+s.r);ck('eg_consent_id',id);
if(!s.a)clear(/^_ga($|_)|^_gid$|^_gat/);if(!s.m)clear(/^_gcl_|^_fbp$|^_fbc$/);apply(s);hide();
if(cfg.log&&window.fetch){try{fetch(cfg.log,{method:'POST',keepalive:true,credentials:'omit',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id,analytics:s.a,marketing:s.m,rev:s.r,action:act,lang:cfg.lang,url:location.pathname})});}catch(e){}}
if(prev&&((prev.a&&!s.a)||(prev.m&&!s.m))){setTimeout(function(){location.reload();},300);}}
box.addEventListener('click',function(e){var b=e.target.closest('[data-eg-act]');if(!b)return;var act=b.getAttribute('data-eg-act');
if(act==='accept')save(1,1,'accept');else if(act==='reject')save(0,0,'reject');else if(act==='close')save(0,0,'close');else if(act==='settings')settings(true);
else if(act==='save'){var v={a:0,m:0};box.querySelectorAll('[data-eg-cat]').forEach(function(i){v[i.getAttribute('data-eg-cat').charAt(0)]=i.checked?1:0;});save(v.a,v.m,'save');}});
if(fab)fab.addEventListener('click',function(){show(true);});
document.addEventListener('click',function(e){var a=e.target.closest&&e.target.closest('a[href$="#cookies"],a[href$="#eg-cookies"],[data-eg-cookies-open]');if(a){e.preventDefault();show(true);}});
window.egCookies={open:function(){show(true);},state:function(){var s=read();return valid(s)?{analytics:!!s.a,marketing:!!s.m}:null;}};
var s=read();if(valid(s)){if(s.a)run('analytics');if(s.m)run('marketing');hide();}else{show(false);}})(<?php echo wp_json_encode( $cfg, JSON_UNESCAPED_SLASHES ); ?>);</script>
	<?php
	if ( ! isset( $c['events'] ) || ! empty( $c['events'] ) ) {
		eg_cookies_print_events( $c, $lang );
	}
}

/**
 * Clics que se mandan a Google Analytics: teléfono (clic_telefono), WhatsApp (clic_whatsapp, también Click to Chat),
 * enlaces a los hosts de booking_hosts (pedir_cita) y reglas propias {"event", "href"}.
 */
function eg_cookies_print_events( $c, $lang ) {
	$cfg = array(
		'hosts' => array_values( (array) ( $c['booking_hosts'] ?? array() ) ),
		'rules' => array_values( (array) ( $c['rules'] ?? array() ) ),
		'lang'  => $lang,
	);
	?>
<script data-cfasync="false" nowprocket>(function(c){document.addEventListener('click',function(e){if(!e.target.closest)return;var a=e.target.closest('a,[class*="ht-ctc"],[class*="ht_ctc"]');if(!a)return;
var h=a.getAttribute('href')||'',cl=a.getAttribute('class')||'',ev='',i;
if(/^tel:/i.test(h))ev='clic_telefono';else if(/wa\.me|whatsapp\.com|^whatsapp:/i.test(h)||/ht[-_]ctc/.test(cl))ev='clic_whatsapp';
else{for(i=0;i<c.hosts.length;i++){if(c.hosts[i]&&h.indexOf(c.hosts[i])>-1){ev='pedir_cita';break;}}
if(!ev){for(i=0;i<c.rules.length;i++){if(c.rules[i].href&&h.indexOf(c.rules[i].href)>-1){ev=c.rules[i].event;break;}}}}
if(ev&&window.gtag){gtag('event',ev,{link_url:h,idioma:c.lang,pagina:location.pathname});}},true);})(<?php echo wp_json_encode( $cfg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>);</script>
	<?php
}
