document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalComprovante');
    const span = document.querySelector('.close');
    const btnImprimir = document.getElementById('btnImprimir');
   // const btnDownload = document.getElementById('btnDownload');

    // Fechar modal ao clicar no X
    span.onclick = function() {
        modal.style.display = 'none';
    }

    // Fechar modal ao clicar fora dele
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Função para imprimir
    btnImprimir.onclick = function() {
        const conteudo = document.getElementById('comprovanteContent').innerHTML;
        const janela = window.open('', '_blank');
        janela.document.write(`
            <html>
                <head>
                    <title>Comprovante de Pagamento</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .comprovante { max-width: 600px; margin: 0 auto; }
                        .header { text-align: center; border-bottom: 2px solid #8d0606; padding-bottom: 10px; }
                        .info { margin: 20px 0; }
                        .info-row { display: flex; justify-content: space-between; margin: 10px 0; }
                        .total { font-weight: bold; font-size: 18px; color: #8d0606; }
                    </style>
                </head>
                <body>
                    ${conteudo}
                </body>
            </html>
        `);
        janela.document.close();
        janela.print();
        janela.close();
    }

    // Função para download (simulada - você pode implementar com uma biblioteca como jsPDF)
   // btnDownload.onclick = function() {
     //   alert('Funcionalidade de download PDF em desenvolvimento. Use a opção imprimir e salve como PDF.');
    //}
});

// Função para gerar comprovante
function gerarComprovante(pedidoId) {
    // Fazer requisição AJAX para buscar dados do pedido
    fetch(`gerar_comprovante.php?pedido_id=${pedidoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const comprovante = criarComprovante(data.pedido);
                document.getElementById('comprovanteContent').innerHTML = comprovante;
                document.getElementById('modalComprovante').style.display = 'block';
            } else {
                alert('Erro ao gerar comprovante: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar comprovante');
        });
}

// Função para criar o HTML do comprovante
function criarComprovante(pedido) {
    const dataFormatada = new Date(pedido.data_pedido).toLocaleString('pt-BR');
    const dataPagamento = pedido.data_pagamento ? new Date(pedido.data_pagamento).toLocaleString('pt-BR') : 'N/A';
    
    return `
        <div class="comprovante">
            <div class="header">
                <h2>Let's Rock Disco Store</h2>
                <h3>Comprovante de Pagamento</h3>
            </div>
            
            <div class="info">
                <div class="info-row">
                    <strong>Pedido:</strong>
                    <span>#${pedido.pedido_id}</span>
                </div>
                <div class="info-row">
                    <strong>Data do Pedido:</strong>
                    <span>${dataFormatada}</span>
                </div>
                <div class="info-row">
                    <strong>Data do Pagamento:</strong>
                    <span>${dataPagamento}</span>
                </div>
                <div class="info-row">
                    <strong>Forma de Pagamento:</strong>
                    <span>${pedido.forma_pagamento}</span>
                </div>
                <div class="info-row">
                    <strong>Status:</strong>
                    <span>${pedido.status_pagamento.toUpperCase()}</span>
                </div>
                <div class="info-row">
                    <strong>Código da Transação:</strong>
                    <span>${pedido.codigo_transacao || 'N/A'}</span>
                </div>
            </div>

            <div class="itens">
                <h4>Itens do Pedido:</h4>
                <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
                    <thead>
                        <tr style="background-color: #8d0606; color: white;">
                            <th style="padding: 8px; border: 1px solid #ddd;">Item</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">Qtd</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">Valor Unit.</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${pedido.itens.map(item => `
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;">${item.titulo} - ${item.artista}</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">${item.quantidade}</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">R$ ${parseFloat(item.preco_unitario).toFixed(2).replace('.', ',')}</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">R$ ${(item.quantidade * item.preco_unitario).toFixed(2).replace('.', ',')}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>

            <div class="info-row total">
                <strong>TOTAL PAGO:</strong>
                <span>R$ ${parseFloat(pedido.valor_pago).toFixed(2).replace('.', ',')}</span>
            </div>

            <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
                <p>Este comprovante foi gerado automaticamente pelo sistema.</p>
                <p>Let's Rock Disco Store - Todos os direitos reservados</p>
            </div>
        </div>
    `;
}