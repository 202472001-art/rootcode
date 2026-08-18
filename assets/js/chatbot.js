'use strict';

(() => {
  const root = document.querySelector('[data-chatbot]');
  if (!root) return;
  const toggle = root.querySelector('.chatbot-toggle');
  const windowEl = root.querySelector('.chatbot-window');
  const close = root.querySelector('[data-chat-close]');
  const form = root.querySelector('[data-chat-form]');
  const input = root.querySelector('[data-chat-input]');
  const messages = root.querySelector('[data-chat-messages]');
  const suggestions = root.querySelector('[data-chat-suggestions]');

  // Edita este arreglo para cambiar o agregar respuestas del chatbot.
  const answers = [
    { words: ['servicio', 'ofrecen', 'hacen', 'desarrollan'], answer: 'RootCode ofrece páginas corporativas, catálogos, tiendas, sistemas web, landing pages, mantenimiento, hosting y dominio.' },
    { words: ['solicitar', 'solicitud', 'pedir', 'proyecto'], answer: 'Entra a Solicitudes, selecciona Nueva solicitud y completa el tipo, presupuesto, fecha y descripción del proyecto.' },
    { words: ['tiempo', 'tarda', 'duración', 'semanas'], answer: 'Una página sencilla puede tardar de 2 a 4 semanas. Un sistema personalizado requiere revisar primero los requisitos.' },
    { words: ['contacto', 'administrador', 'hablar'], answer: 'Puedes escribir al administrador desde la sección Mensajes o usar el formulario de contacto del sitio público.' },
    { words: ['registro', 'cuenta', 'iniciar sesión', 'login'], answer: 'Para registrarte usa Crear una cuenta. Después inicia sesión con tu correo y contraseña.' },
    { words: ['estado', 'avance', 'seguimiento'], answer: 'Abre Solicitudes y selecciona Ver. Ahí encontrarás el estado y las notas que agregó el administrador.' },
    { words: ['soporte', 'error', 'problema', 'ayuda'], answer: 'Describe el problema en Mensajes. Incluye qué estabas haciendo y el texto del error para recibir soporte.' },
    { words: ['pago', 'formas de pago', 'anticipo'], answer: 'Las formas de pago se acuerdan con el administrador. Normalmente puede solicitarse un anticipo y pagos por avance.' },
    { words: ['hosting', 'dominio', 'ssl'], answer: 'El hosting guarda los archivos del sitio, el dominio es su dirección y el SSL protege la conexión con HTTPS.' },
    { words: ['mantenimiento', 'actualización', 'respaldo'], answer: 'El mantenimiento puede incluir respaldos, correcciones, actualizaciones y cambios pequeños de contenido.' },
    { words: ['precio', 'costo', 'cuánto cuesta', 'presupuesto'], answer: 'El costo depende del alcance. Registra una solicitud con tu presupuesto para que el administrador pueda revisarlo.' },
    { words: ['pregunta frecuente', 'faq'], answer: 'Puedes preguntarme por servicios, solicitudes, tiempos, pagos, soporte, hosting, dominio, mantenimiento y estados.' }
  ];

  const addMessage = (text, type) => {
    const item = document.createElement('div');
    item.className = `chat-message ${type}`;
    item.textContent = text;
    messages.appendChild(item);
    messages.scrollTop = messages.scrollHeight;
  };
  const normalize = (text) => text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const getAnswer = (question) => {
    const normalized = normalize(question);
    let best = null;
    let score = 0;
    answers.forEach((item) => {
      const current = item.words.reduce((total, word) => total + (normalized.includes(normalize(word)) ? 1 : 0), 0);
      if (current > score) { score = current; best = item; }
    });
    return best ? best.answer : 'No encontré una respuesta exacta. Abre Mensajes para contactar directamente al administrador.';
  };
  const ask = (question) => {
    if (!question.trim()) return;
    addMessage(question, 'user');
    window.setTimeout(() => addMessage(getAnswer(question), 'bot'), 180);
  };
  const openChat = () => {
    windowEl.removeAttribute('hidden');
    toggle.setAttribute('aria-expanded', 'true');
    input.focus();
  };
  const closeChat = () => {
    windowEl.setAttribute('hidden', '');
    toggle.setAttribute('aria-expanded', 'false');
  };
  toggle.addEventListener('click', () => windowEl.hasAttribute('hidden') ? openChat() : closeChat());
  close.addEventListener('click', closeChat);
  form.addEventListener('submit', (event) => { event.preventDefault(); const question = input.value.trim(); if (!question) return; ask(question); input.value = ''; });
  suggestions?.querySelectorAll('button').forEach((button) => button.addEventListener('click', () => ask(button.textContent || '')));
  document.querySelectorAll('[data-open-chatbot]').forEach((button) => button.addEventListener('click', openChat));
})();
