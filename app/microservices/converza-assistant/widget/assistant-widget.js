// 🤖 CONVERZA ASSISTANT WIDGET - JAVASCRIPT

(function() {
    'use strict';
    
    // Elementos del DOM
    const toggleBtn = document.getElementById('assistant-toggle-btn');
    const closeBtn = document.getElementById('assistant-close-btn');
    const chatPanel = document.getElementById('assistant-chat-panel');
    const messagesContainer = document.getElementById('assistant-messages');
    const input = document.getElementById('assistant-input');
    const sendBtn = document.getElementById('assistant-send-btn');
    const suggestionsContainer = document.getElementById('assistant-suggestions');
    const typingIndicator = document.getElementById('assistant-typing');
    const badge = document.getElementById('assistant-badge');
    
    // Configuración
    const API_ENDPOINT = '/Converza/app/microservices/converza-assistant/api/assistant.php';
    
    // Función para obtener user ID actualizado
    function getCurrentUserId() {
        return window.USER_ID || window.ASSISTANT_USER_DATA?.id || 0;
    }
    
    function getCurrentUserName() {
        return window.USER_NAME || window.ASSISTANT_USER_DATA?.nombre || 'Usuario';
    }
    
    function getCurrentUserPhoto() {
        return window.USER_PHOTO || window.ASSISTANT_USER_DATA?.foto || '/Converza/public/avatars/defect.jpg';
    }
    
    // Debug: Verificar datos cargados inicialmente
    console.log('🤖 Datos iniciales del usuario para el asistente:');
    console.log('   ID:', getCurrentUserId());
    console.log('   Nombre:', getCurrentUserName());
    console.log('   Foto:', getCurrentUserPhoto());
    
    // Estado
    let isOpen = false;
    let messageHistory = [];
    
    // =========================================
    // Event Listeners
    // =========================================
    
    toggleBtn.addEventListener('click', () => {
        toggleChat();
    });
    
    closeBtn.addEventListener('click', () => {
        toggleChat();
    });
    
    sendBtn.addEventListener('click', () => {
        sendMessage();
    });
    
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    input.addEventListener('input', () => {
        autoResize(input);
    });
    
    // Sugerencias
    suggestionsContainer.addEventListener('click', (e) => {
        const btn = e.target.closest('.suggestion-btn');
        if (btn) {
            const question = btn.dataset.question;
            input.value = question;
            sendMessage();
        }
    });
    
    // =========================================
    // Funciones
    // =========================================
    
    function toggleChat() {
        isOpen = !isOpen;
        chatPanel.style.display = isOpen ? 'flex' : 'none';
        
        if (isOpen) {
            input.focus();
            badge.style.display = 'none';
            scrollToBottom();
        }
    }
    
    function sendMessage() {
        const question = input.value.trim();
        
        if (!question) return;
        
        // Obtener user_id actualizado desde variables globales
        const currentUserId = window.USER_ID || window.ASSISTANT_USER_DATA?.id || 0;
        
        console.log('📤 ID de usuario actual:', currentUserId);
        
        // Agregar mensaje del usuario
        addMessage(question, 'user');
        
        // Limpiar input
        input.value = '';
        autoResize(input);
        
        // Deshabilitar input mientras procesa
        setInputState(false);
        
        // Mostrar typing indicator
        showTyping();
        
        // Enviar al servidor
        console.log('📤 Enviando al servidor:', {
            question: question,
            user_id: currentUserId
        });
        
        fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                question: question,
                user_id: currentUserId
            })
        })
        .then(response => {
            console.log('📥 Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.text(); // Primero obtener como texto
        })
        .then(text => {
            console.log('📥 Response text:', text);
            try {
                return JSON.parse(text); // Luego parsear a JSON
            } catch (e) {
                console.error('❌ JSON parse error:', e);
                console.error('📄 Response was:', text);
                throw new Error('Respuesta inválida del servidor');
            }
        })
        .then(data => {
            hideTyping();
            
            if (data.success) {
                // El objeto response contiene la respuesta real
                const response = data.response || data;
                
                // Actualizar datos del usuario si vienen en el contexto
                if (response.context) {
                    if (response.context.user_name && response.context.user_name !== 'Usuario') {
                        window.USER_NAME = response.context.user_name;
                        console.log('✅ Nombre actualizado desde API:', window.USER_NAME);
                    }
                    if (response.context.user_photo) {
                        window.USER_PHOTO = response.context.user_photo;
                        console.log('✅ Foto actualizada desde API:', window.USER_PHOTO);
                    }
                }
                
                // Agregar respuesta del asistente
                const answerText = response.answer || data.answer || 'Lo siento, no obtuve respuesta.';
                addMessage(answerText, 'assistant');
                
                // Actualizar sugerencias
                if (response.suggestions && response.suggestions.length > 0) {
                    updateSuggestions(response.suggestions);
                }
                
                // Guardar en historial
                messageHistory.push({
                    question: question,
                    answer: answerText,
                    intent: response.intent || data.intent,
                    timestamp: Date.now()
                });
                
            } else {
                addMessage(
                    data.error || 'Lo siento, ocurrió un error. Intenta de nuevo.',
                    'assistant'
                );
            }
            
            setInputState(true);
            input.focus();
        })
        .catch(error => {
            console.error('❌ Error del asistente:', error);
            hideTyping();
            addMessage(
                'Lo siento, no pude conectarme al servidor. Intenta más tarde.',
                'assistant'
            );
            setInputState(true);
        });
    }
    
    function addMessage(text, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `assistant-message ${type === 'user' ? 'user-message' : 'assistant-msg'}`;
        
        const time = new Date().toLocaleTimeString('es', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        // Validar que text no sea undefined o null
        if (!text || text === undefined || text === null) {
            text = 'Lo siento, hubo un error al procesar la respuesta.';
        }
        
        // Convertir markdown simple a HTML
        text = formatMarkdown(text);
        
        if (type === 'user') {
            // Obtener datos actualizados desde variables globales
            const currentUserName = window.USER_NAME || userName;
            const currentUserPhoto = window.USER_PHOTO || userPhoto;
            
            // Mensaje del usuario con foto y nombre
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <img src="${currentUserPhoto}" alt="${currentUserName}" onerror="this.src='/Converza/public/avatars/defect.jpg'">
                </div>
                <div class="message-content">
                    <div class="message-name">${currentUserName}</div>
                    <div class="message-bubble">${text}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;
        } else {
            // Mensaje del asistente con icono
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">${text}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;
        }
        
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }
    
    function formatMarkdown(text) {
        // Validar que text no sea undefined, null o vacío
        if (!text || typeof text !== 'string') {
            return 'Error al procesar la respuesta.';
        }
        
        // Bold
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Lista con bullets
        text = text.replace(/^([•✅⚠️❌🎯📊🔔👥🛍️🥇🥈🥉])\s*(.*?)$/gm, '<li>$1 $2</li>');
        
        // Envolver listas
        text = text.replace(/(<li>.*?<\/li>\s*)+/gs, '<ul>$&</ul>');
        
        // Párrafos
        text = text.split('\n\n').map(p => p.trim() ? `<p>${p}</p>` : '').join('');
        
        return text;
    }
    
    function updateSuggestions(suggestions) {
        suggestionsContainer.innerHTML = '';
        
        suggestions.slice(0, 3).forEach(suggestion => {
            const btn = document.createElement('button');
            btn.className = 'suggestion-btn';
            btn.dataset.question = suggestion;
            btn.innerHTML = `<i class="bi bi-lightbulb"></i> ${suggestion}`;
            suggestionsContainer.appendChild(btn);
        });
    }
    
    function showTyping() {
        typingIndicator.style.display = 'flex';
        scrollToBottom();
    }
    
    function hideTyping() {
        typingIndicator.style.display = 'none';
    }
    
    function setInputState(enabled) {
        input.disabled = !enabled;
        sendBtn.disabled = !enabled;
    }
    
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
    }
    
    function scrollToBottom() {
        setTimeout(() => {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }, 100);
    }
    
    // =========================================
    // Inicialización
    // =========================================
    
    console.log('🤖 Converza Assistant initialized');
    
    // Cargar mensaje de bienvenida con el nombre actual
    const welcomeMessage = `¡Hola <strong>${getCurrentUserName()}</strong>! 👋 Soy el asistente de Converza.

Puedo ayudarte con:

• 🎯 Sistema de Karma
• 😊 Reacciones
• 🔔 Notificaciones
• 👥 Amigos y conexiones
• 🛍️ Tienda

¿En qué puedo ayudarte?`;
    
    addMessage(welcomeMessage, 'assistant');
    
    // Mostrar badge de ayuda si es primera visita
    if (!localStorage.getItem('assistant_visited')) {
        badge.style.display = 'flex';
        localStorage.setItem('assistant_visited', 'true');
    }
    
})();
