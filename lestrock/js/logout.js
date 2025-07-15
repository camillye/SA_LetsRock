document.addEventListener('DOMContentLoaded', function() {
    const logoutForm = document.getElementById('logoutForm');
    const logoutBtn = document.querySelector('.logout-btn');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    // Confirmação adicional antes do logout
    if (logoutForm) {
        logoutForm.addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja sair do sistema?')) {
                e.preventDefault();
                return false;
            }
            
            // Adicionar loading no botão
            if (logoutBtn) {
                logoutBtn.textContent = 'SAINDO...';
                logoutBtn.disabled = true;
                logoutBtn.style.opacity = '0.7';
            }
        });
    }
    
    // Efeito visual no botão cancelar
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            // Adicionar feedback visual
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    }
    
    // Adicionar efeitos de hover nos botões
    const buttons = document.querySelectorAll('.logout-btn, .cancel-btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
});