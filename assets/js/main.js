// Qubi LLC — minimal vanilla JS.
// Handles: mobile nav toggle, EN/TR language switch, scroll-reveal animations.

// ── Mobile nav toggle ──────────────────────────────────────────────────────
(function () {
  var toggle = document.querySelector('.nav-toggle');
  var links = document.querySelector('.nav-links');
  if (!toggle || !links) return;

  toggle.addEventListener('click', function () {
    var open = links.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  // Collapse menu when a link is tapped on mobile.
  links.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () {
      links.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
})();

// ── Language switch (EN / TR) on privacy / terms / global header ──────────
(function () {
  var sw = document.querySelector('.lang-switch');
  if (!sw) return;

  var STORAGE_KEY = 'qubiLang';

  function setLang(lang) {
    if (lang !== 'en' && lang !== 'tr') lang = 'en';
    document.body.classList.remove('lang-en', 'lang-tr');
    document.body.classList.add('lang-' + lang);
    sw.querySelectorAll('button[data-lang]').forEach(function (b) {
      var active = b.getAttribute('data-lang') === lang;
      b.classList.toggle('is-active', active);
      b.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    document.querySelectorAll('.list-row[data-section]').forEach(function (a) {
      a.setAttribute('href', '#' + a.getAttribute('data-section') + '-' + lang);
    });
    try { localStorage.setItem(STORAGE_KEY, lang); } catch (e) { /* ignore */ }
  }

  var saved = null;
  try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) { /* ignore */ }
  if (saved === 'en' || saved === 'tr') setLang(saved);

  sw.addEventListener('click', function (e) {
    var btn = e.target.closest('button[data-lang]');
    if (!btn) return;
    setLang(btn.getAttribute('data-lang'));
  });
})();

// ── Scroll reveal animations ──────────────────────────────────────────────
// Adds .reveal to selected elements; when each scrolls into view the class
// .in-view fades it up. Lightweight: one IntersectionObserver, unobserves on
// fire so it never thrashes. Skipped when user prefers reduced motion.
(function () {
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;
  if (!('IntersectionObserver' in window)) return;

  // Selectors that get a soft fade-up as the user scrolls.
  var REVEAL_SELECTORS = [
    '.section-head',
    '.feature-card',
    '.value-card',
    '.step-card',
    '.featured-app',
    '.app-card',
    '.list-card',
    '.list-row',
    '.cta-banner',
    '.support-banner',
    '.contact-grid > .card',
    '.screenshot-grid .phone',
    '.last-updated',
    '.coming-soon',
    '.doc-detail > h2',
    '.doc-detail > h3',
  ];

  var els = document.querySelectorAll(REVEAL_SELECTORS.join(','));
  if (!els.length) return;

  els.forEach(function (el) { el.classList.add('reveal'); });

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

  els.forEach(function (el) { io.observe(el); });
})();
