/* Afoyo African Safaris: front-end behaviour, ported from the static design (website/js/site.js).
   Plain JS, no dependencies. Each block runs only when its data-* hook is on the page. */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var mobileMQ = window.matchMedia('(max-width: 767px)');

  var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
  function trapFocus(container, e) {
    if (e.key !== 'Tab') return;
    var items = $$(FOCUSABLE, container).filter(function (el) { return el.offsetParent !== null; });
    if (!items.length) return;
    var first = items[0], last = items[items.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }

  /* ---------------- Header: names for the theme's menu buttons (they are links with only an icon) ---------------- */
  $$('.canvas-menu.gva-offcanvas > a.dropdown-toggle').forEach(function (a) { a.setAttribute('role', 'button'); a.setAttribute('aria-label', 'Open menu'); });
  $$('.gva-offcanvas-content .control-close-mm').forEach(function (a) { a.setAttribute('role', 'button'); a.setAttribute('aria-label', 'Close menu'); });
  // Without a real address a link cannot be reached with Tab or pressed with Enter or Space.
  $$('.canvas-menu.gva-offcanvas > a.dropdown-toggle, .gva-offcanvas-content .control-close-mm').forEach(function (a) {
    var href = a.getAttribute('href');
    if (!href) a.tabIndex = 0;
    a.addEventListener('keydown', function (e) {
      if (e.key === ' ' || (e.key === 'Enter' && !href)) { e.preventDefault(); a.click(); }
    });
  });

  /* ---------------- Header: close an open dropdown with Escape ---------------- */
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var el = document.activeElement;
    if (el && el.closest && el.closest('.wp-site-header ul.submenu-inner')) {
      var parent = el.closest('ul.submenu-inner').parentElement.querySelector('a');
      if (parent) { parent.focus(); el.blur(); }
    }
  });

  /* ---------------- Home hero slider ---------------- */
  $$('[data-hero]').forEach(function (root) {
    var slides = $$('[data-slide]', root);
    var dots = $$('[data-go]', root);
    var eyebrow = $('[data-hero-eyebrow]', root);
    var title = $('[data-hero-title]', root);
    var prev = $('[data-prev]', root), next = $('[data-next]', root);
    if (slides.length < 2 || !prev || !next) return;
    var cur = 0, timer = null, paused = false;
    function show(i) {
      i = (i + slides.length) % slides.length;
      if (i === cur) return;
      slides[cur].classList.remove('is-active'); slides[cur].setAttribute('aria-hidden', 'true');
      slides[i].classList.add('is-active'); slides[i].removeAttribute('aria-hidden');
      dots.forEach(function (d, k) { if (k === i) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current'); });
      [eyebrow, title].forEach(function (el) { el.classList.add('is-swapping'); });
      var s = slides[i];
      setTimeout(function () {
        eyebrow.textContent = s.dataset.eyebrow; title.textContent = s.dataset.title;
        [eyebrow, title].forEach(function (el) { el.classList.remove('is-swapping'); });
      }, reduceMotion ? 0 : 450);
      cur = i;
    }
    function stop() { if (timer) clearInterval(timer); timer = null; }
    function start() { stop(); if (!reduceMotion) timer = setInterval(function () { if (!paused && !document.hidden) show(cur + 1); }, 6000); }
    prev.addEventListener('click', function () { show(cur - 1); start(); });
    next.addEventListener('click', function () { show(cur + 1); start(); });
    dots.forEach(function (d) { d.addEventListener('click', function () { show(+d.dataset.go); start(); }); });
    root.addEventListener('mouseenter', function () { paused = true; });
    root.addEventListener('mouseleave', function () { paused = false; });
    root.addEventListener('focusin', function () { paused = true; });
    root.addEventListener('focusout', function () { paused = false; });
    start();
  });

  /* ---------------- Safari search: leave unused choices out of the address ---------------- */
  $$('[data-safari-search]').forEach(function (form) {
    form.addEventListener('submit', function () {
      $$('select', form).forEach(function (sel) { if (!sel.value) sel.disabled = true; });
      setTimeout(function () { $$('select', form).forEach(function (sel) { sel.disabled = false; }); }, 0);
    });
  });

  /* ---------------- Testimonials: rotate order on desktop ---------------- */
  $$('[data-quotes]').forEach(function (root) {
    var list = $('[data-quote-list]', root);
    var prev = $('[data-qprev]', root), next = $('[data-qnext]', root);
    if (!list || !prev || !next) return;
    var dots = $$('[data-quote-dots] span', root);
    var k = 0, n = $$('[data-quote]', list).length;
    function rotate(dir) {
      var items = $$('[data-quote]', list);
      if (dir > 0) list.appendChild(items[0]); else list.insertBefore(items[items.length - 1], items[0]);
      k = (k + dir + n) % n;
      dots.forEach(function (d, i) { d.classList.toggle('is-on', i === k); });
    }
    prev.addEventListener('click', function () { rotate(-1); });
    next.addEventListener('click', function () { rotate(1); });
  });

  /* ---------------- Mobile swipe rails: position dots ---------------- */
  $$('[data-rail]').forEach(function (rail) {
    var dotsBox = rail.parentElement.querySelector('[data-rail-dots]');
    if (!dotsBox) return;
    var items = Array.prototype.slice.call(rail.children);
    dotsBox.setAttribute('aria-hidden', 'true');
    var dots = items.map(function (item) {
      var b = document.createElement('button');
      b.type = 'button'; b.tabIndex = -1;
      b.addEventListener('click', function () { rail.scrollTo({ left: item.offsetLeft - rail.offsetLeft - 20, behavior: reduceMotion ? 'auto' : 'smooth' }); });
      dotsBox.appendChild(b);
      return b;
    });
    function update() {
      var max = rail.scrollWidth - rail.clientWidth;
      var i = max <= 0 ? 0 : Math.round((rail.scrollLeft / max) * (items.length - 1));
      dots.forEach(function (d, k) { d.setAttribute('aria-current', String(k === i)); });
    }
    rail.addEventListener('scroll', function () { requestAnimationFrame(update); }, { passive: true });
    update();
  });

  /* ---------------- Trip listing: result count before any filter is used ---------------- */
  (function listingCount() {
    var total = $('[data-afoyo-total]');
    var out = $('.wte-filter-foundposts');
    if (!total || !out || out.textContent.trim()) return;
    var n = parseInt(total.getAttribute('data-afoyo-total'), 10) || 0;
    var strong = document.createElement('strong');
    strong.textContent = String(n);
    out.appendChild(strong);
    out.appendChild(document.createTextNode(n === 1 ? ' safari' : ' safaris'));
  })();
  // After a filter, WTE writes "5 Trips Found": say "5 safaris" as the design does.
  (function listingWords() {
    var out = $('.wte-filter-foundposts');
    if (!out || !window.MutationObserver) return;
    new MutationObserver(function () {
      var strong = $('strong', out);
      var last = out.lastChild;
      if (strong && last && last.nodeType === 3 && /trips? found/i.test(last.textContent)) {
        last.textContent = strong.textContent.trim() === '1' ? ' safari' : ' safaris';
      }
    }).observe(out, { childList: true, subtree: true, characterData: true });
  })();

  /* ---------------- Trip listing: make WTE's sort menu usable from the keyboard ---------------- */
  $$('.wte-ordering .wpte__select-field').forEach(function (field) {
    var input = $('.wpte__input', field);
    if (!input) return;
    input.setAttribute('tabindex', '0');
    input.setAttribute('role', 'button');
    input.setAttribute('aria-haspopup', 'listbox');
    input.setAttribute('aria-label', 'Sort safaris');
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); var first = $('li', field); if (first) first.focus(); }
    });
    $$('li', field).forEach(function (li) {
      li.setAttribute('tabindex', '0');
      li.setAttribute('role', 'option');
      li.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); li.click(); input.focus(); }
        if (e.key === 'Escape') { input.click(); input.focus(); }
      });
    });
  });

  /* ---------------- Trip listing: WTE's filter panel as a bottom sheet on mobile ---------------- */
  (function filterSheet() {
    var panel = $('.advanced-search-wrapper');
    var openBtn = $('#wte-filterbar-toggle-btn');
    var closeBtn = $('#wte-filterbar-close-btn');
    if (!panel || !openBtn) return;
    var backdrop = document.createElement('div');
    backdrop.className = 'sheet-backdrop';
    panel.parentNode.insertBefore(backdrop, panel);
    openBtn.setAttribute('aria-expanded', 'false');
    function set(open) {
      panel.classList.toggle('is-open', open);
      backdrop.classList.toggle('is-open', open);
      document.body.classList.toggle('no-scroll', open && mobileMQ.matches);
      openBtn.setAttribute('aria-expanded', String(open));
      if (open && closeBtn) closeBtn.focus(); else if (!open) openBtn.focus();
    }
    openBtn.addEventListener('click', function (e) { e.preventDefault(); set(!panel.classList.contains('is-open')); });
    if (closeBtn) {
      closeBtn.setAttribute('aria-label', 'Close filters');
      closeBtn.addEventListener('click', function () { set(false); });
    }
    backdrop.addEventListener('click', function () { set(false); });
    panel.addEventListener('keydown', function (e) { if (e.key === 'Escape' && panel.classList.contains('is-open')) set(false); });
  })();

  /* ---------------- Safaris grid (listing look): sort + "show all" on mobile ---------------- */
  var SORTS = {
    featured: function (a, b) { return a.pos - b.pos; },
    priceAsc: function (a, b) { return (a.price == null ? 1e9 : a.price) - (b.price == null ? 1e9 : b.price) || a.pos - b.pos; },
    priceDesc: function (a, b) { return (b.price == null ? -1 : b.price) - (a.price == null ? -1 : a.price) || a.pos - b.pos; },
    durAsc: function (a, b) { return a.days - b.days || a.pos - b.pos; },
    durDesc: function (a, b) { return b.days - a.days || a.pos - b.pos; }
  };
  $$('[data-sortable]').forEach(function (root) {
    var grid = $('[data-grid]', root);
    if (!grid) return;
    var trips = $$('[data-trip]', grid).map(function (el, pos) {
      return { el: el, pos: pos, days: +el.dataset.days, price: el.dataset.price ? +el.dataset.price : null };
    });
    var extras = $$('.tour-card--tailor', grid);
    var limit = +root.dataset.mobileLimit || 3, all = false;
    var select = $('[data-sort]', root), more = $('[data-more]', root);
    function render() {
      trips.slice().sort(SORTS[select ? select.value : 'featured'] || SORTS.featured).forEach(function (t, k) {
        grid.appendChild(t.el);
        t.el.hidden = mobileMQ.matches && !all && k >= limit;
      });
      extras.forEach(function (el) { grid.appendChild(el); });
      if (more) more.hidden = all || !mobileMQ.matches;
    }
    if (select) select.addEventListener('change', render);
    if (more) more.addEventListener('click', function () { all = true; render(); });
    mobileMQ.addEventListener('change', render);
    render();
  });

  /* ---------------- "On this page" contents: open on desktop, highlight current section ---------------- */
  $$('[data-toc]').forEach(function (toc) {
    function sync() { toc.open = !mobileMQ.matches; }
    sync();
    mobileMQ.addEventListener('change', sync);
    var links = $$('a[href^="#"]', toc);
    links.forEach(function (a) { a.addEventListener('click', function () { if (mobileMQ.matches) toc.open = false; }); });
    var targets = links.map(function (a) { return document.getElementById(a.getAttribute('href').slice(1)); });
    function spy() {
      var y = window.scrollY + 140, cur = 0;
      targets.forEach(function (t, i) { if (t && t.getBoundingClientRect().top + window.scrollY <= y) cur = i; });
      links.forEach(function (a, i) { a.classList.toggle('is-current', i === cur); if (i === cur) a.setAttribute('aria-current', 'location'); else a.removeAttribute('aria-current'); });
    }
    window.addEventListener('scroll', function () { requestAnimationFrame(spy); }, { passive: true });
    spy();
  });

  /* ---------------- Accordions (itinerary, FAQs) ---------------- */
  function setAcc(btn, open) {
    btn.setAttribute('aria-expanded', String(open));
    var panel = document.getElementById(btn.getAttribute('aria-controls'));
    if (panel) panel.hidden = !open;
    var item = btn.closest('.acc');
    if (item) item.classList.toggle('is-open', open);
  }
  $$('[data-acc]').forEach(function (group) {
    var single = group.hasAttribute('data-single');
    var btns = $$('.acc__btn', group);
    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var open = btn.getAttribute('aria-expanded') !== 'true';
        if (single && open) btns.forEach(function (b) { if (b !== btn) setAcc(b, false); });
        setAcc(btn, open);
        group.dispatchEvent(new CustomEvent('acc:change'));
      });
    });
  });
  $$('[data-expand-all]').forEach(function (ctl) {
    var group = document.getElementById(ctl.dataset.expandAll);
    var label = $('span', ctl);
    if (!group || !label) return;
    var btns = $$('.acc__btn', group);
    function allOpen() { return btns.every(function (b) { return b.getAttribute('aria-expanded') === 'true'; }); }
    function sync() { label.textContent = allOpen() ? (ctl.dataset.labelCollapse || 'Collapse all') : (ctl.dataset.labelExpand || 'Expand all'); }
    ctl.addEventListener('click', function () { var open = !allOpen(); btns.forEach(function (b) { setAcc(b, open); }); sync(); });
    group.addEventListener('acc:change', sync);
    sync();
  });

  /* ---------------- Tabs (tour page) ---------------- */
  $$('[data-tabs]').forEach(function (root) {
    var tabs = $$('[role="tab"]', root);
    function select(tab, focus) {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.setAttribute('aria-selected', String(on));
        t.tabIndex = on ? 0 : -1;
        document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
      });
      if (focus) tab.focus();
      if (tab.scrollIntoView && mobileMQ.matches) tab.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    }
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { select(t); });
      t.addEventListener('keydown', function (e) {
        var j = null;
        if (e.key === 'ArrowRight') j = (i + 1) % tabs.length;
        if (e.key === 'ArrowLeft') j = (i - 1 + tabs.length) % tabs.length;
        if (e.key === 'Home') j = 0;
        if (e.key === 'End') j = tabs.length - 1;
        if (j !== null) { e.preventDefault(); select(tabs[j], true); }
      });
    });
  });

  /* ---------------- Tour gallery + lightbox ---------------- */
  (function gallery() {
    var root = $('[data-gallery]');
    var lb = $('[data-lightbox]');
    if (!root || !lb) return;
    var photos = JSON.parse(root.dataset.photos);
    var imgEl = $('[data-lb-img]', lb), cap = $('[data-lb-cap]', lb);
    var cur = 0, returnTo = null;
    function render() {
      imgEl.src = photos[cur].src; imgEl.alt = photos[cur].alt;
      cap.textContent = (cur + 1) + ' / ' + photos.length + ' · ' + photos[cur].alt;
    }
    function open(i) {
      returnTo = document.activeElement; cur = i; render();
      lb.hidden = false; document.body.classList.add('no-scroll');
      $('[data-lb-close]', lb).focus();
    }
    function close() { lb.hidden = true; document.body.classList.remove('no-scroll'); if (returnTo) returnTo.focus(); }
    function step(d) { cur = (cur + d + photos.length) % photos.length; render(); }
    $$('[data-lb]', root).forEach(function (b) { b.addEventListener('click', function () { open(+b.dataset.lb); }); });
    $('[data-lb-close]', lb).addEventListener('click', close);
    $('[data-lb-prev]', lb).addEventListener('click', function () { step(-1); });
    $('[data-lb-next]', lb).addEventListener('click', function () { step(1); });
    lb.addEventListener('click', function (e) { if (e.target === lb || e.target.classList.contains('lightbox__stage')) close(); });
    lb.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') step(-1);
      else if (e.key === 'ArrowRight') step(1);
      trapFocus(lb, e);
    });
    var x0 = null;
    lb.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (e) { if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 40) step(dx < 0 ? 1 : -1); x0 = null; });
    // Mobile: the gallery is a swipe strip with a "n / total" counter
    var track = $('[data-gallery-track]', root), count = $('[data-gallery-count]', root);
    if (track && count) {
      track.addEventListener('scroll', function () {
        var i = Math.round(track.scrollLeft / Math.max(1, track.clientWidth));
        count.textContent = (i + 1) + '/' + photos.length;
      }, { passive: true });
    }
  })();

  /* ---------------- Blog post sharing ---------------- */
  $$('[data-share]').forEach(function (box) {
    var copy = $('[data-copy-link]', box), msg = $('[data-copy-msg]', box);
    if (copy && msg) copy.addEventListener('click', function () {
      var done = function () { msg.textContent = 'Link copied'; setTimeout(function () { msg.textContent = ''; }, 2500); };
      if (navigator.clipboard) navigator.clipboard.writeText(location.href).then(done, function () { msg.textContent = 'Copy failed'; });
      else done();
    });
  });
})();
