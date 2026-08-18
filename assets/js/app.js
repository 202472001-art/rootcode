'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const navToggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.main-nav');
  if (navToggle && nav) {
    navToggle.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', String(open));
    });
  }

  const sidebar = document.querySelector('[data-sidebar]');
  const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
  const overlay = document.querySelector('[data-sidebar-overlay]');
  const setSidebar = (open) => {
    if (!sidebar) return;
    sidebar.classList.toggle('open', open);
    overlay?.classList.toggle('show', open);
  };
  sidebarToggle?.addEventListener('click', () => setSidebar(!sidebar?.classList.contains('open')));
  overlay?.addEventListener('click', () => setSidebar(false));

  document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (event) => {
      if (!window.confirm(element.getAttribute('data-confirm') || '¿Confirmas esta acción?')) event.preventDefault();
    });
  });

  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = document.querySelector(button.getAttribute('data-password-toggle'));
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      button.textContent = input.type === 'password' ? 'Mostrar' : 'Ocultar';
    });
  });

  document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const invalid = [...form.querySelectorAll('[required]')].find((field) => !String(field.value).trim());
      if (invalid) {
        event.preventDefault();
        invalid.focus();
        window.alert('Completa todos los campos obligatorios.');
      }
    });
  });

  document.querySelectorAll('[data-slider]').forEach((slider) => {
    const track = slider.querySelector('[data-slider-track]');
    const cards = [...slider.querySelectorAll('.slider-card')];
    const dots = slider.querySelector('[data-slider-dots]');
    const prev = slider.querySelector('[data-slider-prev]');
    const next = slider.querySelector('[data-slider-next]');
    if (!track || cards.length === 0) return;
    let index = 0;
    const visible = () => window.innerWidth <= 720 ? 1 : window.innerWidth <= 1050 ? 2 : 3;
    const maxIndex = () => Math.max(0, cards.length - visible());
    const renderDots = () => {
      if (!dots) return;
      dots.innerHTML = '';
      for (let i = 0; i <= maxIndex(); i += 1) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = i === index ? 'active' : '';
        dot.setAttribute('aria-label', `Ir a la posición ${i + 1}`);
        dot.addEventListener('click', () => { index = i; update(); });
        dots.appendChild(dot);
      }
    };
    const update = () => {
      index = Math.min(index, maxIndex());
      const gap = 20;
      const width = (track.parentElement.clientWidth - gap * (visible() - 1)) / visible();
      cards.forEach((card) => { card.style.flexBasis = `${width}px`; });
      track.style.transform = `translateX(-${index * (width + gap)}px)`;
      prev.disabled = index === 0;
      next.disabled = index >= maxIndex();
      renderDots();
    };
    prev?.addEventListener('click', () => { if (index > 0) { index -= 1; update(); } });
    next?.addEventListener('click', () => { if (index < maxIndex()) { index += 1; update(); } });
    window.addEventListener('resize', update);
    update();
  });
});
