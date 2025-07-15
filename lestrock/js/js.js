// Validação do formulário e feedback visual
document.getElementById('cadastroForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const inputs = this.querySelectorAll('.form-input');
    let allFilled = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            allFilled = false;
            input.style.borderColor = '#ff6b6b';
            input.style.boxShadow = '0 0 15px rgba(255, 107, 107, 0.4)';
        } else {
            input.style.borderColor = 'rgba(235, 208, 164, 0.3)';
            input.style.boxShadow = '';
        }
    });
    
    if (allFilled) {
        const btn = this.querySelector('.submit-btn');
        btn.style.background = 'linear-gradient(145deg, #4CAF50, #45a049)';
        btn.textContent = 'CADASTRADO!';
        
        setTimeout(() => {
            alert('Cadastro realizado com sucesso! Bem-vindo à Let\'s Rock Disco Store!');
            this.reset();
            btn.style.background = 'linear-gradient(145deg, #E5B87E 0%, #EBD0A4 50%, #E5B87E 100%)';
            btn.textContent = 'CADASTRAR';
        }, 1000);
    } else {
        alert('Por favor, preencha todos os campos obrigatórios.');
    }
});

// Máscara para o campo de nascimento: DD/MM/AAAA
document.querySelector('input[name="nascimento"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');

    if (value.length > 8) {
        value = value.substring(0, 8);
    }

    if (value.length >= 3 && value.length <= 4) {
        value = value.replace(/^(\d{2})(\d{1,2})$/, '$1/$2');
    } else if (value.length > 4) {
        value = value.replace(/^(\d{2})(\d{2})(\d{1,4})$/, '$1/$2/$3');
    }

    e.target.value = value;
});

// Máscara para o telefone: (XX) XXXXX-XXXX ou (XX) XXXX-XXXX
document.querySelector('input[name="telefone"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');

    if (value.length > 11) {
        value = value.substring(0, 11);
    }

    if (value.length <= 10) {
        value = value.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
    } else {
        value = value.replace(/^(\d{2})(\d{5})(\d{0,4})$/, '($1) $2-$3');
    }

    e.target.value = value;
});

// Partículas flutuantes
function createParticle() {
    const particle = document.createElement('div');
    particle.className = 'particle';
    particle.style.left = Math.random() * 100 + '%';
    particle.style.animationDelay = Math.random() * 15 + 's';
    particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
    
    document.getElementById('particles').appendChild(particle);
    
    setTimeout(() => {
        particle.remove();
    }, 25000);
}

// Criar partículas periodicamente
setInterval(createParticle, 3000);

// Criar algumas partículas iniciais
for(let i = 0; i < 5; i++) {
    setTimeout(createParticle, i * 1000);
}


