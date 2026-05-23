/*  Luluat Almisbah — Admin panel JS.
 *  - Sidebar toggle for mobile
 *  - Sales line chart (canvas, no external libs)
 */
(function () {
  'use strict';

  // Sidebar (mobile)
  const root = document.querySelector('.admin');
  const btn  = document.querySelector('.admin__menu-btn');
  if (root && btn) {
    btn.addEventListener('click', () => root.classList.toggle('is-open'));
  }

  // Chart on dashboard
  const canvas = document.getElementById('salesChart');
  if (canvas && canvas.getContext) {
    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const values = JSON.parse(canvas.dataset.values || '[]');
    drawSalesChart(canvas, labels, values);
    window.addEventListener('resize', () => drawSalesChart(canvas, labels, values));
  }

  function drawSalesChart(canvas, labels, values) {
    const dpr = window.devicePixelRatio || 1;
    const cssWidth  = canvas.parentElement.clientWidth;
    const cssHeight = parseInt(canvas.getAttribute('height') || '180', 10);
    canvas.width  = cssWidth * dpr;
    canvas.height = cssHeight * dpr;
    canvas.style.width  = cssWidth + 'px';
    canvas.style.height = cssHeight + 'px';
    const ctx = canvas.getContext('2d');
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, cssWidth, cssHeight);

    const padding = { top: 16, right: 14, bottom: 24, left: 36 };
    const chartW = cssWidth  - padding.left - padding.right;
    const chartH = cssHeight - padding.top  - padding.bottom;
    const max = Math.max.apply(null, values.length ? values : [10]) || 10;
    const stepX = chartW / Math.max(1, values.length - 1);

    // Gridlines
    ctx.strokeStyle = '#ece6f1';
    ctx.lineWidth = 1;
    ctx.font = '11px Poppins, system-ui, sans-serif';
    ctx.fillStyle = '#6f6678';
    for (let i = 0; i <= 4; i++) {
      const y = padding.top + (chartH / 4) * i;
      ctx.beginPath();
      ctx.moveTo(padding.left, y);
      ctx.lineTo(padding.left + chartW, y);
      ctx.stroke();
      const v = Math.round(max - (max / 4) * i);
      ctx.fillText(String(v), 4, y + 4);
    }

    // Filled area
    ctx.beginPath();
    ctx.moveTo(padding.left, padding.top + chartH);
    values.forEach((v, i) => {
      const x = padding.left + stepX * i;
      const y = padding.top + chartH - (v / max) * chartH;
      i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.lineTo(padding.left + chartW, padding.top + chartH);
    ctx.closePath();
    const grad = ctx.createLinearGradient(0, padding.top, 0, padding.top + chartH);
    grad.addColorStop(0, 'rgba(255,107,155,.35)');
    grad.addColorStop(1, 'rgba(255,107,155,0)');
    ctx.fillStyle = grad;
    ctx.fill();

    // Line
    ctx.beginPath();
    values.forEach((v, i) => {
      const x = padding.left + stepX * i;
      const y = padding.top + chartH - (v / max) * chartH;
      i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.strokeStyle = '#ee4d83';
    ctx.lineWidth = 2.5;
    ctx.stroke();

    // Dots
    values.forEach((v, i) => {
      const x = padding.left + stepX * i;
      const y = padding.top + chartH - (v / max) * chartH;
      ctx.beginPath();
      ctx.arc(x, y, 3, 0, Math.PI * 2);
      ctx.fillStyle = '#ee4d83';
      ctx.fill();
    });

    // X labels (every Nth)
    const every = Math.max(1, Math.floor(values.length / 7));
    values.forEach((_, i) => {
      if (i % every !== 0 && i !== values.length - 1) return;
      const x = padding.left + stepX * i;
      ctx.fillStyle = '#6f6678';
      ctx.fillText(String(labels[i] || ''), x - 16, cssHeight - 6);
    });
  }
})();
