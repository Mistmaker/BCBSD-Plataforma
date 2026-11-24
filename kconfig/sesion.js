/*
 * Aviso de sesión (se agrega automáticamente desde kconfig/auth.php).
 *
 * 1. Consulta cada minuto cuánto le queda a la sesión (sin renovarla).
 * 2. Poco antes de vencer muestra un aviso con cuenta regresiva y el botón
 *    "Seguir trabajando".
 * 3. Si la sesión venció, o una operación responde 401, muestra una ventana para
 *    escribir la clave SIN salir de la página. Al ingresar, las operaciones ajax
 *    (jQuery) que fallaron por la sesión se reenvían solas: no se pierde lo escrito.
 *
 * Sin dependencias (jQuery es opcional, solo para reenviar operaciones).
 */
(function () {
    'use strict';
    if (window.__kSesion) { return; }
    window.__kSesion = true;

    var yo = document.currentScript || document.querySelector('script[src*="kconfig/sesion.js"]');
    if (!yo) { return; }
    var BASE    = yo.getAttribute('data-base') || '/';
    var LOGIN   = yo.getAttribute('data-login') || '';
    var MINUTOS = parseInt(yo.getAttribute('data-minutos'), 10) || 24;
    var URL_ESTADO = BASE + 'kconfig/sesion_estado.php';
    var URL_LOGIN  = BASE + 'kadmin/controller/Controller-login.php';
    var AVISO_SEG  = Math.min(180, Math.max(60, Math.round(MINUTOS * 60 * 0.15))); // aviso 1-3 min antes

    var restante = MINUTOS * 60, pendientes = [], expirada = false, reloj = null;

    /* ---------- utilidades ---------- */
    function peticion(metodo, url, cuerpo, listo) {
        var x = new XMLHttpRequest();
        x.__kSesionPropia = true;
        x.open(metodo, url, true);
        x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        if (cuerpo) { x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8'); }
        x.onload = function () {
            var d = null;
            try { d = JSON.parse(x.responseText); } catch (e) { d = null; }
            listo(x.status, d);
        };
        x.onerror = function () { listo(0, null); };
        x.send(cuerpo || null);
    }
    function cod(o) {
        var p = [];
        for (var k in o) { if (o.hasOwnProperty(k)) { p.push(encodeURIComponent(k) + '=' + encodeURIComponent(o[k])); } }
        return p.join('&');
    }
    function mmss(s) {
        s = Math.max(0, Math.floor(s));
        return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
    }
    function el(tag, css, txt) {
        var e = document.createElement(tag);
        if (css) { e.style.cssText = css; }
        if (txt) { e.textContent = txt; }
        return e;
    }

    /* ---------- aviso previo ---------- */
    var barra = el('div', 'position:fixed;left:50%;top:12px;transform:translateX(-50%);z-index:2147483000;' +
        'background:#fcf8e3;color:#8a6d3b;border:1px solid #faebcc;border-radius:6px;padding:10px 14px;' +
        'font:14px/1.4 Segoe UI,Arial,sans-serif;box-shadow:0 4px 16px rgba(0,0,0,.15);display:none;max-width:92%');
    var barraTxt = el('span');
    barra.id = 'kSesionAviso';
    var barraBtn = el('button', 'margin-left:12px;background:#337ab7;color:#fff;border:0;border-radius:4px;padding:5px 12px;cursor:pointer;font:inherit', 'Seguir trabajando');
    barra.appendChild(barraTxt); barra.appendChild(barraBtn);
    barraBtn.onclick = renovar;

    function renovar() {
        peticion('POST', URL_ESTADO, cod({ renovar: 1 }), function (st, d) {
            if (d && d.activa) { restante = d.restante; barra.style.display = 'none'; }
            else { mostrarModal(); }
        });
    }

    /* ---------- ventana de nuevo ingreso ---------- */
    var fondo = el('div', 'position:fixed;inset:0;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.45);z-index:2147483001;display:none');
    var caja  = el('div', 'position:absolute;left:50%;top:18%;transform:translateX(-50%);width:360px;max-width:92%;background:#fff;' +
        'border-radius:8px;padding:20px 22px;font:14px/1.45 Segoe UI,Arial,sans-serif;color:#333;box-shadow:0 10px 40px rgba(0,0,0,.3)');
    var titulo = el('h3', 'margin:0 0 8px;font-size:18px;color:#b94a48', 'Su sesión expiró');
    var texto  = el('p', 'margin:0 0 12px', 'Por seguridad la sesión se cerró por inactividad. Escriba su clave para continuar: ' +
        'no perderá lo que tiene en esta página.');
    var usuario = el('input', 'width:100%;box-sizing:border-box;margin-bottom:8px;padding:7px;border:1px solid #ccc;border-radius:4px;background:#f5f5f5');
    usuario.type = 'text'; usuario.readOnly = true; usuario.value = LOGIN;
    var clave = el('input', 'width:100%;box-sizing:border-box;margin-bottom:8px;padding:7px;border:1px solid #ccc;border-radius:4px');
    clave.type = 'password'; clave.placeholder = 'Clave'; clave.autocomplete = 'current-password'; clave.id = 'kSesionClave';
    var error = el('div', 'color:#b94a48;min-height:18px;margin-bottom:6px');
    var btn = el('button', 'width:100%;background:#337ab7;color:#fff;border:0;border-radius:4px;padding:8px;cursor:pointer;font:inherit', 'Continuar');
    btn.id = 'kSesionContinuar'; btn.type = 'button'; error.id = 'kSesionError'; fondo.id = 'kSesionVentana';
    var otro = el('a', 'display:block;margin-top:10px;font-size:12px;color:#666', 'Ingresar con otro usuario (se abrirá la pantalla de inicio)');
    otro.href = BASE + 'kadmin/view/login'; otro.target = '_blank'; otro.rel = 'noopener';
    [titulo, texto, usuario, clave, error, btn, otro].forEach(function (n) { caja.appendChild(n); });
    fondo.appendChild(caja);

    function mostrarModal() {
        expirada = true;
        barra.style.display = 'none';
        if (fondo.style.display !== 'block') {
            error.textContent = '';
            clave.value = '';
            fondo.style.display = 'block';
            setTimeout(function () { clave.focus(); }, 50);
        }
    }

    function ingresar() {
        if (!clave.value) { error.textContent = 'Escriba su clave.'; return; }
        btn.disabled = true; btn.textContent = 'Verificando…';
        peticion('POST', URL_LOGIN, cod({ datoname: LOGIN, datolog: clave.value, dato_sx: 'register', modo: 'ajax' }), function (st, d) {
            btn.disabled = false; btn.textContent = 'Continuar';
            if (d && d.ok) {
                expirada = false;
                fondo.style.display = 'none';
                clave.value = '';
                restante = MINUTOS * 60;
                reenviarPendientes();
            } else {
                error.textContent = (d && d.error) ? d.error : 'No se pudo verificar. Intente de nuevo.';
                clave.select();
            }
        });
    }
    btn.onclick = ingresar;
    clave.onkeydown = function (e) { if ((e.key || e.keyCode) === 'Enter' || e.keyCode === 13) { ingresar(); } };

    /* ---------- reenvío de operaciones que fallaron por la sesión ---------- */
    function reenviarPendientes() {
        var lista = pendientes; pendientes = [];
        if (!lista.length || !window.jQuery) { return; }
        lista.forEach(function (s) { try { window.jQuery.ajax(s); } catch (e) { /* nada */ } });
    }

    function enganchar() {
        // jQuery: guardar la operación para reenviarla después de ingresar
        if (window.jQuery && window.jQuery(document).ajaxError) {
            window.jQuery(document).ajaxError(function (ev, xhr, settings) {
                if (xhr && xhr.status === 401 && settings && !settings.__kReenviada) {
                    settings.__kReenviada = true;
                    pendientes.push(settings);
                    mostrarModal();
                }
            });
        }
        // Cualquier otra petición (fetch/XHR sin jQuery): al menos avisar
        var abrir = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.send = function () {
            var x = this;
            if (!x.__kSesionPropia) {
                x.addEventListener('load', function () {
                    if (x.status === 401) { mostrarModal(); }
                    else if (x.status >= 200 && x.status < 400 && !expirada) { restante = MINUTOS * 60; } // el servidor la renovó
                });
            }
            return abrir.apply(this, arguments);
        };
        if (window.fetch) {
            var f = window.fetch;
            window.fetch = function () {
                return f.apply(this, arguments).then(function (r) { if (r.status === 401) { mostrarModal(); } return r; });
            };
        }
    }

    /* ---------- consulta periódica y cuenta regresiva ---------- */
    function consultar() {
        peticion('GET', URL_ESTADO + '?t=' + Date.now(), null, function (st, d) {
            if (!d) { return; }
            if (!d.activa) { mostrarModal(); return; }
            restante = d.restante;
            if (expirada) { expirada = false; fondo.style.display = 'none'; } // ingresó en otra pestaña
        });
    }

    function tic() {
        if (expirada) { return; }
        restante -= 1;
        if (restante <= 0) { consultar(); return; }
        if (restante <= AVISO_SEG) {
            barraTxt.textContent = 'Su sesión se cerrará por inactividad en ' + mmss(restante) + '.';
            barra.style.display = 'block';
        } else {
            barra.style.display = 'none';
        }
    }

    function iniciar() {
        document.body.appendChild(barra);
        document.body.appendChild(fondo);
        enganchar();
        consultar();
        setInterval(consultar, 60000);
        reloj = setInterval(tic, 1000);
        document.addEventListener('visibilitychange', function () { if (!document.hidden) { consultar(); } });
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', iniciar); }
    else { iniciar(); }
})();
