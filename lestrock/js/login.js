// Aguardar o DOM estar carregado
document.addEventListener('DOMContentLoaded', function() {
    // Configurar formulário de login
    setupLoginForm();
    
    // Configurar animações dos inputs
    setupInputAnimations();
    
    // Configurar "Lembrar de mim"
    setupRememberMe();
});

// Configurar formulário de login
function setupLoginForm() {
    const form = document.getElementById('loginForm');
    const submitBtn = form.querySelector('.submit-btn');
    
    if (!form || !submitBtn) return;
    
    form.addEventListener('submit', function(e) {
        // Adicionar animação de loading ao botão
        submitBtn.style.opacity = '0.7';
        submitBtn.innerHTML = 'ENTRANDO...';
        submitBtn.disabled = true;
        
        // Validar campos
        if (!validateForm()) {
            e.preventDefault();
            resetSubmitButton();
            return false;
        }
    });
    
    function resetSubmitButton() {
        submitBtn.style.opacity = '1';
        submitBtn.innerHTML = 'ENTRAR';
        submitBtn.disabled = false;
    }
    
    function validateForm() {
        const email = form.querySelector('input[name="email"]').value.trim();
        const senha = form.querySelector('input[name="senha"]').value;
        
        if (!email || !senha) {
            showMessage('Por favor, preencha todos os campos!', 'erro');
            return false;
        }
        
        if (!isValidEmail(email)) {
            showMessage('Por favor, insira um email válido!', 'erro');
            return false;
        }
        
        if (senha.length < 6) {
            showMessage('A senha deve ter pelo menos 6 caracteres!', 'erro');
            return false;
        }
        
        return true;
    }
}

// Configurar animações dos inputs
function setupInputAnimations() {
    const inputs = document.querySelectorAll('.form-input');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
        
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.parentElement.classList.add('has-value');
            } else {
                this.parentElement.classList.remove('has-value');
            }
        });
    });
}

// Configurar funcionalidade "Lembrar de mim" (sem localStorage)
function setupRememberMe() {
    const checkbox = document.getElementById('lembrar');
    const emailInput = document.querySelector('input[name="email"]');
    
    if (!checkbox || !emailInput) return;
    
    // Apenas marcar visualmente, sem salvar no localStorage
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            console.log('Lembrar email ativado');
        } else {
            console.log('Lembrar email desativado');
        }
    });
}

// Função para validar email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Função para mostrar mensagens
function showMessage(message, type) {
    // Remover mensagem existente
    const existingMessage = document.querySelector('.mensagem');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Criar nova mensagem
    const messageDiv = document.createElement('div');
    messageDiv.className = `mensagem ${type}`;
    messageDiv.textContent = message;
    
    // Inserir antes do formulário
    const form = document.getElementById('loginForm');
    if (form && form.parentNode) {
        form.parentNode.insertBefore(messageDiv, form);
        
        // Remover mensagem após 5 segundos
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
}

// Adicionar suporte a teclas de atalho
document.addEventListener('keydown', function(e) {
    // Escape para limpar mensagens
    if (e.key === 'Escape') {
        const messages = document.querySelectorAll('.mensagem');
        messages.forEach(msg => msg.remove());
    }
});

// Adicionar CSS para estados dos inputs
const style = document.createElement('style');
style.textContent = `
    .form-group.focused .form-input {
        border-color: #E5B87E;
        box-shadow: 0 0 15px rgba(229, 184, 126, 0.3);
    }
    
    .form-group.has-value .form-input {
        background: rgba(81, 16, 1, 0.6);
    }
`;
document.head.appendChild(style);