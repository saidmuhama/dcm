/**
 * DCM Lazy Navigation — SPA-style page transitions
 * Intercepts ?view= link clicks, fetches content via AJAX (_dcm_ajax=1),
 * swaps <main> with smooth animation, isolates inline scripts in IIFE scope.
 */
(function ($) {
  'use strict';

  /* ── Guards ──────────────────────────────────────────────── */
  if (!window.history || !window.history.pushState) return; // fallback: normal nav

  let _currentView  = new URLSearchParams(window.location.search).get('view') || '';
  let _currentXhr   = null;
  let _navigating   = false;
  const _loadedExternalScripts = new Set();

  /* ── Progress bar ─────────────────────────────────────────── */
  const $bar = $('<div id="dcm-progress"></div>').appendTo('body');

  function barStart() {
    $bar.removeClass('dcm-bar-done').addClass('dcm-bar-active');
  }
  function barDone() {
    $bar.removeClass('dcm-bar-active').addClass('dcm-bar-done');
    setTimeout(function () { $bar.removeClass('dcm-bar-done'); }, 500);
  }

  /* ── Page skeleton ─────────────────────────────────────────── */
  const SKELETON_HTML =
    '<div id="dcm-page-skeleton">' +
      '<div class="sk-card"><div class="sk-title"></div>' +
        '<div class="sk-row"><div class="sk-stat"></div><div class="sk-stat"></div><div class="sk-stat"></div><div class="sk-stat"></div></div>' +
        '<div class="sk-line w80"></div><div class="sk-line w60"></div><div class="sk-line w45"></div>' +
      '</div>' +
      '<div class="sk-card"><div class="sk-title"></div>' +
        '<div class="sk-line w80"></div><div class="sk-line w60"></div>' +
      '</div>' +
    '</div>';

  /* ── External script tracker ──────────────────────────────── */
  // Pre-seed scripts that are already on the page
  document.querySelectorAll('script[src]').forEach(function (s) {
    if (s.src) _loadedExternalScripts.add(s.src);
  });

  /* ── Execute scripts from fetched fragment ────────────────── */
  function executeFragmentScripts(container) {
    var scripts = container.querySelectorAll('script');
    var externalQueue = [];

    scripts.forEach(function (script) {
      if (script.src) {
        var abs = new URL(script.src, window.location.origin).href;
        if (!_loadedExternalScripts.has(abs)) {
          _loadedExternalScripts.add(abs);
          externalQueue.push(abs);
        }
      } else {
        var code = script.textContent || '';
        if (code.trim()) {
          try {
            // IIFE scope — prevents const/let re-declaration conflicts
            new Function(code)();
          } catch (e) {
            // Try again without IIFE (some scripts reference parent scope)
            try { eval(code); } catch (e2) {
              console.warn('[DCM Nav] Script error:', e2.message);
            }
          }
        }
      }
      script.parentNode && script.parentNode.removeChild(script);
    });

    // Load external scripts sequentially
    return externalQueue.reduce(function (p, src) {
      return p.then(function () {
        return new Promise(function (resolve) {
          var el = document.createElement('script');
          el.src = src;
          el.async = false;
          el.onload = el.onerror = resolve;
          document.body.appendChild(el);
        });
      });
    }, Promise.resolve());
  }

  /* ── Update sidebar active state ──────────────────────────── */
  function syncActiveLinks(view) {
    $('.adminuiux-sidebar .nav-link').each(function () {
      $(this).removeClass('dcm-nav-pending');
      var href = $(this).attr('href') || '';
      var m    = href.match(/[?&]view=([^&\s#]+)/);
      var v    = m ? m[1] : null;
      if (!v) return;

      if (v === view) {
        $(this).addClass('active');
        // Auto-expand parent collapse if needed
        var $parent = $(this).closest('.collapse');
        if ($parent.length && !$parent.hasClass('show')) {
          $parent.addClass('show');
          $('[data-bs-target="#' + $parent.attr('id') + '"]').removeClass('collapsed').attr('aria-expanded', 'true');
        }
      } else {
        $(this).removeClass('active');
      }
    });
  }

  /* ── Update document title from fetched HTML ───────────────── */
  function extractTitle(html) {
    var m = html.match(/<title[^>]*>([\s\S]*?)<\/title>/i);
    return m ? m[1].trim() : document.title;
  }

  /* ── Core navigate ─────────────────────────────────────────── */
  function navigate(href, push) {
    if (_navigating) {
      if (_currentXhr) _currentXhr.abort();
    }
    _navigating = true;

    var url = new URL(href, window.location.href);
    var view = url.searchParams.get('view') || '';

    // Build AJAX URL
    var ajaxUrl = new URL(href, window.location.href);
    ajaxUrl.searchParams.set('_dcm_ajax', '1');

    var $main = $('main.adminuiux-content');
    barStart();

    // Show skeleton placeholder
    $main.css('position', 'relative').html(SKELETON_HTML);

    // Mark nav link as pending
    $('.adminuiux-sidebar .nav-link').each(function () {
      var m = ($(this).attr('href') || '').match(/[?&]view=([^&\s#]+)/);
      if (m && m[1] === view) $(this).addClass('dcm-nav-pending');
    });

    _currentXhr = $.ajax({
      url:     ajaxUrl.toString(),
      method:  'GET',
      timeout: 18000,

      success: function (html) {
        var $tmp = $('<div>').html(html);

        // Swap main content (without scripts first)
        $main.html($tmp.html());

        // Execute all scripts from the fragment
        executeFragmentScripts($main[0]).then(function () {
          // Entrance animation
          $main.addClass('dcm-page-enter');
          var tid = setTimeout(function () { $main.removeClass('dcm-page-enter'); }, 450);

          if (push) {
            var cleanUrl = new URL(href, window.location.href);
            history.pushState({ view: view }, '', cleanUrl.toString());
          }

          _currentView = view;
          syncActiveLinks(view);
          barDone();
          _navigating = false;
          _currentXhr = null;

          // Scroll to top
          window.scrollTo({ top: 0, behavior: 'instant' });

          // Re-init third-party icon libs
          if (typeof feather !== 'undefined') {
            try { feather.replace(); } catch (e) {}
          }
        });
      },

      error: function (xhr) {
        if (xhr.statusText === 'abort') {
          _navigating = false;
          return;
        }
        // Fallback: real navigation
        barDone();
        _navigating = false;
        window.location.href = href;
      }
    });
  }

  /* ── Intercept nav clicks ─────────────────────────────────── */
  $(document).on('click', 'a', function (e) {
    var $a   = $(this);
    var href = $a.attr('href');

    if (!href || !href.includes('view=')) return;

    // Bail on exceptions
    if (href.includes('kill-session'))          return;
    if (href.startsWith('http') && !href.startsWith(window.location.origin)) return;
    if ($a.attr('target') === '_blank')         return;
    if ($a.attr('download') != null)            return;
    if ($a.data('bs-toggle'))                   return;
    if ($a.data('bs-dismiss'))                  return;
    if (e.ctrlKey || e.metaKey || e.shiftKey)  return;

    // Check it's an internal link to this app
    try {
      var dest = new URL(href, window.location.href);
      if (dest.origin !== window.location.origin) return;
    } catch (er) { return; }

    e.preventDefault();

    // Skip if same URL
    var destFull = new URL(href, window.location.href).href;
    if (window.location.href === destFull) return;

    navigate(href, true);
  });

  /* ── Browser back / forward ───────────────────────────────── */
  window.addEventListener('popstate', function () {
    navigate(window.location.href, false);
  });

  /* ── Seed initial state ───────────────────────────────────── */
  history.replaceState({ view: _currentView }, '', window.location.href);

})(jQuery);
