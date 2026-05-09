/*  Kids Store — storefront vanilla JS.
 *  - Hero slider (auto-play + manual)
 *  - Toast notifications
 *  - AJAX cart / wishlist
 *  - Quick search suggestions
 *  - Mobile menu, product gallery + zoom, qty steppers
 */
(function () {
  'use strict';

  const BASE  = (document.querySelector('meta[name=base-url]')   || {}).content || '';
  const CSRF  = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
  const RTL   = document.body.classList.contains('rtl');
  const LANG  = document.body.dataset.lang || 'en';

  const t = (en, ar) => LANG === 'ar' ? (ar || en) : en;
  const url = (path) => BASE + '/' + path.replace(/^\/+/, '');

  /* -------------------- Toasts ------------------------------ */
  const stack = document.getElementById('toast-stack');
  function toast(message, type) {
    if (!stack) return;
    type = type || 'info';
    const el = document.createElement('div');
    el.className = 'toast toast--' + type;
    el.textContent = message;
    stack.appendChild(el);
    requestAnimationFrame(() => el.classList.add('show'));
    setTimeout(() => {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 250);
    }, 2600);
  }
  window.toast = toast;

  /* -------------------- AJAX helper ------------------------- */
  function postJSON(endpoint, data) {
    const fd = new FormData();
    Object.keys(data || {}).forEach(k => {
      if (data[k] !== undefined && data[k] !== null) fd.append(k, data[k]);
    });
    return fetch(url(endpoint), {
      method: 'POST',
      headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
      body: fd,
      credentials: 'same-origin',
    }).then(r => r.json().catch(() => ({ ok: false, error: 'Server error' })));
  }

  /* -------------------- Mobile menu ------------------------- */
  const burger = document.querySelector('.hamburger');
  const navList = document.querySelector('.nav-list');
  if (burger && navList) {
    burger.addEventListener('click', () => {
      const open = burger.getAttribute('aria-expanded') === 'true';
      burger.setAttribute('aria-expanded', String(!open));
      navList.classList.toggle('is-open', !open);
    });
  }

  /* -------------------- Hero slider ------------------------- */
  document.querySelectorAll('.js-slider').forEach(initSlider);
  function initSlider(slider) {
    const slides = slider.querySelectorAll('.hero__slide');
    if (!slides.length) return;
    const dots   = slider.querySelector('.slider-dots');
    const prev   = slider.querySelector('.slider-nav--prev');
    const next   = slider.querySelector('.slider-nav--next');
    const auto   = parseInt(slider.dataset.autoplay, 10) || 0;
    let idx = 0, timer;

    slides.forEach((s, i) => {
      s.classList.toggle('is-active', i === 0);
      const b = document.createElement('button');
      b.type = 'button';
      b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
      b.className = i === 0 ? 'is-active' : '';
      b.addEventListener('click', () => go(i));
      dots && dots.appendChild(b);
    });

    function go(i) {
      slides[idx].classList.remove('is-active');
      if (dots) dots.children[idx].classList.remove('is-active');
      idx = (i + slides.length) % slides.length;
      slides[idx].classList.add('is-active');
      if (dots) dots.children[idx].classList.add('is-active');
      restart();
    }
    function restart() {
      clearInterval(timer);
      if (auto > 0) timer = setInterval(() => go(idx + 1), auto);
    }
    if (prev) prev.addEventListener('click', () => go(idx + (RTL ? 1 : -1)));
    if (next) next.addEventListener('click', () => go(idx + (RTL ? -1 : 1)));
    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', restart);
    restart();
  }

  /* -------------------- Add to cart (cards) ----------------- */
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-add-cart');
    if (!btn) return;
    e.preventDefault();
    const pid = btn.dataset.id;
    if (!pid) return;
    btn.disabled = true;
    postJSON('ajax/add_to_cart.php', { product_id: pid, qty: 1 })
      .then(res => {
        btn.disabled = false;
        if (res.ok) {
          toast(t('Added to cart!', 'تمت الإضافة إلى السلة!'), 'success');
          updateCounts(res.count, undefined);
        } else {
          toast(res.error || t('Could not add to cart', 'تعذرت إضافة المنتج'), 'error');
        }
      })
      .catch(() => { btn.disabled = false; toast(t('Network error', 'خطأ في الاتصال'), 'error'); });
  });

  /* -------------------- Add to cart (detail page) ----------- */
  const detailForm = document.querySelector('.js-product-form');
  const addBtn     = document.querySelector('.js-add-cart-detail');
  if (detailForm && addBtn) {
    addBtn.addEventListener('click', () => {
      const pid    = detailForm.dataset.id;
      const qty    = detailForm.querySelector('[name=qty]').value || 1;
      const sizeEl = detailForm.querySelector('[name=size]:checked');
      const colorEl= detailForm.querySelector('[name=color]:checked');
      addBtn.disabled = true;
      postJSON('ajax/add_to_cart.php', {
        product_id: pid,
        qty: qty,
        size:  sizeEl  ? sizeEl.value  : '',
        color: colorEl ? colorEl.value : '',
      }).then(res => {
        addBtn.disabled = false;
        if (res.ok) {
          toast(t('Added to cart!', 'تمت الإضافة إلى السلة!'), 'success');
          updateCounts(res.count, undefined);
        } else {
          toast(res.error || t('Could not add to cart', 'تعذرت الإضافة'), 'error');
        }
      });
    });
  }

  /* -------------------- Qty steppers ------------------------ */
  document.querySelectorAll('.qty').forEach(group => {
    const minus = group.querySelector('.js-qty-minus');
    const plus  = group.querySelector('.js-qty-plus');
    const input = group.querySelector('.qty__input');
    if (!minus || !plus || !input) return;
    const max = parseInt(input.max, 10) || 99;
    minus.addEventListener('click', () => { input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1); });
    plus.addEventListener('click',  () => { input.value = Math.min(max, (parseInt(input.value, 10) || 1) + 1); });
  });

  /* -------------------- Wishlist toggle --------------------- */
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-wish');
    if (!btn) return;
    e.preventDefault();
    const pid = btn.dataset.id;
    if (!pid) return;
    postJSON('ajax/toggle_wishlist.php', { product_id: pid })
      .then(res => {
        if (!res.ok) return toast(res.error || 'Error', 'error');
        // Toggle every wish-button bound to this id
        document.querySelectorAll('.js-wish[data-id="' + CSS.escape(pid) + '"]')
          .forEach(b => b.classList.toggle('is-active', !!res.added));
        updateCounts(undefined, res.count);
        toast(res.added ? t('Added to wishlist', 'أُضيف إلى المفضلة')
                        : t('Removed from wishlist', 'أُزيل من المفضلة'), 'success');
      });
  });

  function updateCounts(cart, wish) {
    if (cart !== undefined) document.querySelectorAll('.js-cart-count').forEach(el => el.textContent = cart);
    if (wish !== undefined) document.querySelectorAll('.js-wishlist-count').forEach(el => el.textContent = wish);
  }

  /* -------------------- Quick search ----------------------- */
  const searchForm = document.querySelector('.search');
  if (searchForm) {
    const input = searchForm.querySelector('input[name=q]');
    const list  = searchForm.querySelector('.search__suggest');
    let timer, lastQ = '';

    function close() { list.hidden = true; list.innerHTML = ''; }
    document.addEventListener('click', (e) => { if (!searchForm.contains(e.target)) close(); });

    input.addEventListener('input', () => {
      const q = input.value.trim();
      clearTimeout(timer);
      if (q.length < 2) { close(); return; }
      if (q === lastQ) return;
      lastQ = q;
      timer = setTimeout(() => {
        fetch(url('ajax/search.php?q=' + encodeURIComponent(q)), {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).then(r => r.json()).then(res => {
          if (!res.ok || !res.rows.length) {
            list.innerHTML = '<li><span class="meta" style="padding:.5rem .65rem;color:var(--ink-500)">' +
                             t('No results', 'لا توجد نتائج') + '</span></li>';
            list.hidden = false;
            return;
          }
          list.innerHTML = res.rows.map(r => (
            '<li><a href="' + r.url + '">' +
              '<img src="' + r.img + '" alt="">' +
              '<span class="meta"><span>' + escapeHtml(r.name) + '</span><small>' + escapeHtml(r.price) + '</small></span>' +
            '</a></li>'
          )).join('');
          list.hidden = false;
        }).catch(close);
      }, 220);
    });
  }
  function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[c]); }

  /* -------------------- Product gallery + zoom ------------- */
  const gallery = document.querySelector('.js-gallery');
  if (gallery) {
    const main = gallery.querySelector('#js-gallery-main');
    gallery.querySelectorAll('.gallery__thumb').forEach(btn => {
      btn.addEventListener('click', () => {
        gallery.querySelectorAll('.gallery__thumb').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        if (main && btn.dataset.src) {
          main.src = btn.dataset.src;
          main.classList.remove('zoomed');
          main.style.transformOrigin = '';
        }
      });
    });
    if (main) {
      // Click to toggle zoom
      main.addEventListener('click', () => main.classList.toggle('zoomed'));
      // Mouse-driven zoom origin while hovering
      main.parentElement.addEventListener('mousemove', (e) => {
        if (!main.classList.contains('zoomed')) return;
        const rect = main.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top)  / rect.height) * 100;
        main.style.transformOrigin = x + '% ' + y + '%';
      });
      main.parentElement.addEventListener('mouseleave', () => {
        main.classList.remove('zoomed');
        main.style.transformOrigin = '';
      });
    }
  }

  /* -------------------- Reveal on scroll ------------------- */
  const io = ('IntersectionObserver' in window) ? new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (en.isIntersecting) {
        en.target.style.transition = 'opacity .4s ease, transform .4s ease';
        en.target.style.opacity = '1';
        en.target.style.transform = 'none';
        io.unobserve(en.target);
      }
    });
  }, { threshold: 0.08 }) : null;

  if (io) {
    document.querySelectorAll('.product-card, .section-tile, .value-card, .trust__item').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(8px)';
      io.observe(el);
    });
  }
})();
