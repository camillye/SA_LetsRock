document.querySelectorAll('.quantidade-input').forEach(input => {
    input.addEventListener('change', () => {
        const discoId = input.dataset.discoId;
        const novaQuantidade = input.value;

        console.log("Enviando para o servidor:", { discoId, novaQuantidade }); // DEBUG

        if (!discoId || !novaQuantidade || novaQuantidade <= 0) {
            alert('Quantidade inválida.');
            return;
        }

        fetch('atualizar_quantidade.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `disco_id=${encodeURIComponent(discoId)}&quantidade=${encodeURIComponent(novaQuantidade)}`
        })
        .then(response => response.json())
        .then(data => {
            const mensagemSpan = input.nextElementSibling;
            if (data.sucesso) {
                mensagemSpan.style.display = 'none';
                location.reload();
            } else if (data.erro) {
                mensagemSpan.textContent = data.erro;
                mensagemSpan.style.display = 'inline';
            }
        })
        .catch(error => {
            console.error('Erro ao atualizar:', error);
        });
    });
});
