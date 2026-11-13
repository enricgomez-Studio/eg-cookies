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
<div id="eg-cookies" class="eg-ck" role="dialog" aria-labelledby="eg-ck-title" aria-describedby="eg-ck-text" data-nosnippet hidden>
<p class="eg-ck-title" id="eg-ck-title"><?php echo esc_html( $t['title'] ); ?></p>
<p class="eg-ck-text" id="eg-ck-text"><?php echo esc_html( $t['text'] ); ?><?php if ( $policy ) : ?> <a href="<?php echo esc_url( $policy ); ?>"><?php echo esc_html( $t['policy'] ); ?></a><?php endif; ?></p>
<div class="eg-ck-prefs" hidden>
<div class="eg-ck-row"><span><strong><?php echo esc_html( $t['necessary'] ); ?></strong><small><?php echo esc_html( $t['necessary_desc'] ); ?></small></span><em><?php echo esc_html( $t['always'] ); ?></em></div>
<?php foreach ( $cats as $cat ) : ?>
<label class="eg-ck-row"><span><strong><?php echo esc_html( $t[ $cat ] ); ?></strong><small><?php echo esc_html( $t[ $cat . '_desc' ] ); ?></small></span><input type="checkbox" role="switch" data-eg-cat="<?php echo esc_attr( $cat ); ?>"><i aria-hidden="true"></i></label>
<?php endforeach; ?>
</div>
<div class="eg-ck-btns">
<button type="button" class="eg-ck-link" data-eg-act="settings"><?php echo esc_html( $t['settings'] ); ?></button>
<button type="button" class="eg-ck-link" data-eg-act="save" hidden><?php echo esc_html( $t['save'] ); ?></button>
<button type="button" data-eg-act="reject"><?php echo esc_html( $t['reject'] ); ?></button>
<button type="button" data-eg-act="accept"><?php echo esc_html( $t['accept'] ); ?></button>
</div>
</div>
<?php if ( $fab ) : ?>
<button type="button" id="eg-cookies-fab" class="eg-ck-fab-<?php echo esc_attr( $fab ); ?>" aria-label="<?php echo esc_attr( $t['floating'] ); ?>" title="<?php echo esc_attr( $t['floating'] ); ?>" data-nosnippet hidden><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-4.6-4.6A4 4 0 0 1 12 2Zm-3.5 6a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm-1 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm5.5 1a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm3.5-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg></button>
<?php endif; ?>
<style>
.eg-ck{--eg-ck:<?php echo esc_attr( $color ); ?>;position:fixed;left:16px;right:16px;bottom:16px;z-index:999999;max-width:760px;max-height:calc(100vh - 32px);overflow:auto;margin:0 auto;background:#fff;color:#1f2933;border-radius:12px;box-shadow:0 8px 34px rgba(0,0,0,.2);padding:18px 22px;font-size:15px;line-height:1.45;box-sizing:border-box}
.eg-ck[hidden],.eg-ck [hidden],#eg-cookies-fab[hidden]{display:none!important}
.eg-ck p{margin:0 0 10px}.eg-ck .eg-ck-title{font-weight:700;font-size:16px;margin-bottom:4px}
.eg-ck a{color:var(--eg-ck);text-decoration:underline}
.eg-ck-prefs{border-top:1px solid #e4e7eb;margin:6px 0 10px}
.eg-ck-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #e4e7eb;cursor:pointer;position:relative}
.eg-ck-row span{display:flex;flex-direction:column;gap:2px}.eg-ck-row small{font-size:13px;color:#52606d}
.eg-ck-row em{font-style:normal;font-size:13px;font-weight:600;color:var(--eg-ck);white-space:nowrap}
.eg-ck-row input{position:absolute;opacity:0;width:1px;height:1px}
.eg-ck-row i{flex:0 0 44px;height:24px;border-radius:24px;background:#cbd2d9;position:relative;transition:background .2s}
.eg-ck-row i:after{content:"";position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .2s}
.eg-ck-row input:checked+i{background:var(--eg-ck)}.eg-ck-row input:checked+i:after{transform:translateX(20px)}
.eg-ck-row input:focus-visible+i{outline:2px solid var(--eg-ck);outline-offset:2px}
.eg-ck-btns{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:10px}
.eg-ck button{font:inherit;font-weight:600;cursor:pointer;border-radius:8px;padding:10px 20px;border:1px solid var(--eg-ck);background:var(--eg-ck);color:#fff;min-width:120px;line-height:1.2;margin:0}
.eg-ck button.eg-ck-link{background:#fff;color:var(--eg-ck);margin-right:auto}
#eg-cookies-fab{position:fixed;bottom:16px;z-index:999998;width:44px;height:44px;border-radius:50%;border:0;padding:0;display:flex;align-items:center;justify-content:center;background:#fff;color:<?php echo esc_attr( $color ); ?>;box-shadow:0 3px 14px rgba(0,0,0,.22);cursor:pointer;opacity:.9}
#eg-cookies-fab:hover{opacity:1}.eg-ck-fab-left{left:16px}.eg-ck-fab-right{right:16px}
@media (max-width:560px){.eg-ck{left:8px;right:8px;bottom:8px;padding:16px}.eg-ck-btns button{flex:1 1 40%}.eg-ck button.eg-ck-link{flex-basis:100%;margin-right:0;order:3}#eg-cookies-fab{bottom:12px;width:40px;height:40px}.eg-ck-fab-left{left:12px}.eg-ck-fab-right{right:12px}}
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
function settings(open){prefs.hidden=!open;bSave.hidden=!open;bSet.hidden=open;if(open){var s=read(),ok=valid(s);box.querySelectorAll('[data-eg-cat]').forEach(function(i){i.checked=!!(ok&&s[i.getAttribute('data-eg-cat').charAt(0)]);});}}
function save(a,m,act){var prev=read(),s={a:a?1:0,m:m?1:0,r:cfg.rev},id=cid();ck('eg_consent','a'+s.a+'m'+s.m+'r'+s.r);ck('eg_consent_id',id);
if(!s.a)clear(/^_ga($|_)|^_gid$|^_gat/);if(!s.m)clear(/^_gcl_|^_fbp$|^_fbc$/);apply(s);hide();
if(cfg.log&&window.fetch){try{fetch(cfg.log,{method:'POST',keepalive:true,credentials:'omit',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id,analytics:s.a,marketing:s.m,rev:s.r,action:act,lang:cfg.lang,url:location.pathname})});}catch(e){}}
if(prev&&((prev.a&&!s.a)||(prev.m&&!s.m))){setTimeout(function(){location.reload();},300);}}
box.addEventListener('click',function(e){var b=e.target.closest('[data-eg-act]');if(!b)return;var act=b.getAttribute('data-eg-act');
if(act==='accept')save(1,1,'accept');else if(act==='reject')save(0,0,'reject');else if(act==='settings')settings(true);
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
