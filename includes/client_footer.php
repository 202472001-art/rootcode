</main>
</div>
<div class="sidebar-overlay" data-sidebar-overlay></div>
<div class="chatbot" data-chatbot>
    <button class="chatbot-toggle" type="button" aria-expanded="false" aria-label="Abrir chatbot"><span aria-hidden="true">?</span></button>
    <section class="chatbot-window" aria-label="Asistente RootCode" hidden>
        <header><strong>Asistente RootCode</strong><button type="button" data-chat-close aria-label="Cerrar">×</button></header>
        <div class="chat-suggestions" data-chat-suggestions>
            <button type="button">¿Qué servicios ofrecen?</button>
            <button type="button">¿Cómo hago una solicitud?</button>
            <button type="button">¿Cuánto tarda un proyecto?</button>
            <button type="button">¿Cómo veo mi estado?</button>
        </div>
        <div class="chatbot-messages" data-chat-messages>
            <div class="chat-message bot">Hola. Elige una pregunta o escribe una duda sobre RootCode.</div>
        </div>
        <form class="chatbot-form" data-chat-form>
            <input type="text" data-chat-input maxlength="200" placeholder="Escribe tu pregunta" autocomplete="off" required>
            <button type="submit">Enviar</button>
        </form>
    </section>
</div>
<script src="<?= asset('js/app.js') ?>" defer></script>
<script src="<?= asset('js/chatbot.js') ?>" defer></script>
</body>
</html>
