<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Você precisa estar logado para acessar o pagamento.'); window.location='login.php';</script>";
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar dados do usuário
$sql = "SELECT nome, email FROM usuarios WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();

// Buscar carrinho ativo
$sql = "SELECT id FROM carrinhos WHERE usuario_id = ? AND finalizado = 0 LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$carrinho = $stmt->fetch();

if (!$carrinho) {
    echo "<script>alert('Nenhum carrinho ativo encontrado.'); window.location='carrinho.php';</script>";
    exit();
}

$carrinho_id = $carrinho['id'];

// Buscar itens do carrinho e calcular total
$sql = "SELECT d.titulo, d.preco, ic.quantidade FROM itens_carrinho ic JOIN discos d ON ic.disco_id = d.id WHERE ic.carrinho_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$carrinho_id]);
$itens = $stmt->fetchAll();

if (!$itens) {
    echo "<script>alert('Carrinho vazio.'); window.location='carrinho.php';</script>";
    exit();
}

$total = 0;
foreach ($itens as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

// Função para gerar código PIX (simulado)
function gerarCodigoPix($valor, $nome, $cidade = "JOINVILLE") {
    // Em um sistema real, você usaria uma biblioteca específica para PIX
    // Este é um exemplo simplificado
    $chave_pix = "letsrockdisco@email.com"; // Sua chave PIX
    $codigo_pix = "00020101021126360014BR.GOV.BCB.PIX0114" . $chave_pix . "5204000053039865802BR5925" . str_pad($nome, 25) . "6009" . str_pad($cidade, 9) . "62070503***6304";
    return $codigo_pix;
}

$codigo_pix = gerarCodigoPix($total, "LETS ROCK DISCO STORE");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Let's Rock Disco Store</title>
    <link rel="stylesheet" href="css/pagamento.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
</head>
<body>
<header class="topo">
  <div class="logo">
    <a href="carrinho.php">
      <img src="imgs/voltar.png" alt="Voltar" class="logo">
    </a>
  </div>
</header>
    <div class="container">
        <h2>💳 Finalizar Pagamento</h2>
        
        <!-- Resumo do Pedido -->
        <table>
            <thead>
                <tr><th>Disco</th><th>Qtd</th><th>Preço Unit.</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['titulo']) ?></td>
                        <td><?= $item['quantidade'] ?></td>
                        <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Endereço de Entrega -->
        <div class="shipping-section">
            <h3>📍 Endereço de Entrega</h3>
            
            <form action="finalizar_compra.php" method="POST" id="paymentForm">
                <div class="form-group">
                    <label for="cep">CEP <span class="required">*</span></label>
                    <input type="text" name="cep" id="cep" placeholder="00000-000" maxlength="9" 
                           oninput="formatCEP(this)" onblur="consultarCEP()" required>
                    <div class="cep-info">
                        💡 Digite o CEP para calcular automaticamente o frete
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="endereco">Endereço <span class="required">*</span></label>
                        <input type="text" name="endereco" id="endereco" placeholder="Rua, Avenida..." required>
                    </div>
                    <div class="form-group">
                        <label for="numero">Número <span class="required">*</span></label>
                        <input type="text" name="numero" id="numero" placeholder="123" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="complemento">Complemento</label>
                        <input type="text" name="complemento" id="complemento" placeholder="Apto, Bloco, Casa...">
                    </div>
                    <div class="form-group">
                        <label for="bairro">Bairro <span class="required">*</span></label>
                        <input type="text" name="bairro" id="bairro" placeholder="Centro, Jardim..." required>
                    </div>
                </div>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label for="cidade">Cidade <span class="required">*</span></label>
                        <input type="text" name="cidade" id="cidade" placeholder="Nome da cidade" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado <span class="required">*</span></label>
                        <select name="estado" id="estado" required>
                            <option value="">Selecione</option>
                            <option value="AC">Acre</option>
                            <option value="AL">Alagoas</option>
                            <option value="AP">Amapá</option>
                            <option value="AM">Amazonas</option>
                            <option value="BA">Bahia</option>
                            <option value="CE">Ceará</option>
                            <option value="DF">Distrito Federal</option>
                            <option value="ES">Espírito Santo</option>
                            <option value="GO">Goiás</option>
                            <option value="MA">Maranhão</option>
                            <option value="MT">Mato Grosso</option>
                            <option value="MS">Mato Grosso do Sul</option>
                            <option value="MG">Minas Gerais</option>
                            <option value="PA">Pará</option>
                            <option value="PB">Paraíba</option>
                            <option value="PR">Paraná</option>
                            <option value="PE">Pernambuco</option>
                            <option value="PI">Piauí</option>
                            <option value="RJ">Rio de Janeiro</option>
                            <option value="RN">Rio Grande do Norte</option>
                            <option value="RS">Rio Grande do Sul</option>
                            <option value="RO">Rondônia</option>
                            <option value="RR">Roraima</option>
                            <option value="SC" selected>Santa Catarina</option>
                            <option value="SP">São Paulo</option>
                            <option value="SE">Sergipe</option>
                            <option value="TO">Tocantins</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="referencia">Ponto de Referência</label>
                        <input type="text" name="referencia" id="referencia" placeholder="Próximo ao...">
                    </div>
                </div>
                
                <!-- Opções de Frete -->
                <h4>🚚 Opções de Entrega</h4>
                <div class="shipping-options">
                    <div class="shipping-option" onclick="selectShipping('sedex')">
                        <strong>📦 SEDEX</strong>
                        <small>Entrega em 1-2 dias úteis</small>
                        <div class="shipping-price" id="sedex-price">R$ 15,90</div>
                    </div>
                    <div class="shipping-option" onclick="selectShipping('pac')">
                        <strong>📮 PAC</strong>
                        <small>Entrega em 3-5 dias úteis</small>
                        <div class="shipping-price" id="pac-price">R$ 12,50</div>
                    </div>
                    <div class="shipping-option" onclick="selectShipping('expressa')">
                        <strong>⚡ Entrega Expressa</strong>
                        <small>Entrega no mesmo dia*</small>
                        <div class="shipping-price" id="expressa-price">R$ 25,00</div>
                    </div>
                </div>
                <input type="hidden" name="tipo_frete" id="tipo_frete" required>
                <input type="hidden" name="valor_frete" id="valor_frete" value="0">
        </div>
        
        <div class="total-amount">
            Subtotal: R$ <?= number_format($total, 2, ',', '.') ?><br>
            Frete: R$ <span id="frete-display">0,00</span><br>
            <strong>Total: R$ <span id="total-display"><?= number_format($total, 2, ',', '.') ?></span></strong>
        </div>

        <!-- Seleção de Forma de Pagamento -->
        <h3>Escolha a forma de pagamento:</h3>
        <div class="payment-methods">
            <div class="payment-method" onclick="selectPayment('pix')">
                <i>📱</i>
                <strong>PIX</strong><br>
                <small>Aprovação imediata</small>
            </div>
            <div class="payment-method" onclick="selectPayment('cartao')">
                <i>💳</i>
                <strong>Cartão</strong><br>
                <small>Crédito ou Débito</small>
            </div>
            <div class="payment-method" onclick="selectPayment('boleto')">
                <i>🧾</i>
                <strong>Boleto</strong><br>
                <small>Vence em 3 dias</small>
            </div>
        </div>

        <input type="hidden" name="forma_pagamento" id="forma_pagamento" required>
        <input type="hidden" name="total" id="total_final" value="<?= $total ?>">
        
        <!-- Seção PIX -->
        <div id="pix-section" class="payment-section">
            <h3>📱 Pagamento via PIX</h3>
            <p>Escaneie o QR Code abaixo ou copie o código PIX:</p>
            
            <div id="qrcode"></div>
            
            <div class="form-group">
                <label>Código PIX:</label>
                <textarea readonly id="pix-code" class="pix-code"><?= $codigo_pix ?></textarea>
                <button type="button" class="copy-btn" onclick="copyPixCode()">Copiar Código</button>
            </div>
            
            <div style="background: #a50e0e; padding: 15px; border-radius: 5px; margin-top: 15px;">
                <p><strong>📋 Instruções:</strong></p>
                <ol>
                    <li>Abra o app do seu banco</li>
                    <li>Escolha a opção PIX</li>
                    <li>Escaneie o QR Code ou cole o código</li>
                    <li>Confirme o pagamento</li>
                </ol>
            </div>
        </div>

        <!-- Seção Cartão -->
        <div id="cartao-section" class="payment-section">
            <h3>💳 Pagamento com Cartão</h3>
            
            <div class="form-group">
                <label for="tipo_cartao">Tipo do Cartão:</label>
                <select name="tipo_cartao" id="tipo_cartao">
                    <option value="credito">Crédito</option>
                    <option value="debito">Débito</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="numero_cartao">Número do Cartão:</label>
                <input type="text" name="numero_cartao" id="numero_cartao" 
                       placeholder="0000 0000 0000 0000" maxlength="19" 
                       oninput="formatCardNumber(this)">
            </div>
            
            <div class="form-group">
                <label for="nome_cartao">Nome no Cartão:</label>
                <input type="text" name="nome_cartao" id="nome_cartao" 
                       placeholder="Nome como aparece no cartão" style="text-transform:uppercase;">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="validade">Validade:</label>
                    <input type="text" name="validade" id="validade" 
                           placeholder="MM/AA" maxlength="5" oninput="formatExpiry(this)">
                </div>
                <div class="form-group">
                    <label for="cvv">CVV:</label>
                    <input type="text" name="cvv" id="cvv" 
                           placeholder="123" maxlength="4" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                </div>
            </div>
            
            <div id="credito-options" style="display:none;">
                <div class="form-group">
                    <label for="parcelas">Parcelas:</label>
                    <select name="parcelas" id="parcelas">
                        <!-- Será preenchido dinamicamente com o total + frete -->
                    </select>
                </div>
            </div>
            
            <div class="security-icons">
                <span>🔒 Pagamento 100% Seguro</span>
            </div>
        </div>

        <!-- Seção Boleto -->
        <div id="boleto-section" class="payment-section">
            <h3>🧾 Pagamento com Boleto</h3>
            
            <div class="boleto-info">
                <p><strong>📋 Informações do Boleto:</strong></p>
                <ul>
                    <li>Valor: R$ <span id="boleto-total"><?= number_format($total, 2, ',', '.') ?></span></li>
                    <li>Vencimento: <?= date('d/m/Y', strtotime('+3 days')) ?></li>
                    <li>Após a confirmação, o boleto será enviado para: <?= htmlspecialchars($usuario['email']) ?></li>
                </ul>
            </div>
            
            <div style="background: #a50e0e; padding: 15px; border-radius: 5px; margin-top: 15px;">
                <p><strong>⚠️ Importante:</strong></p>
                <ul>
                    <li>O boleto vence em 3 dias úteis</li>
                    <li>Após o pagamento, a compensação ocorre em até 2 dias úteis</li>
                    <li>Você receberá o boleto por email</li>
                </ul>
            </div>
        </div>
        
        <button type="submit" id="submit-btn" style="margin-top: 30px;">
            Finalizar Compra
        </button>
        </form>
    </div>

    <script>
        let selectedPayment = null;
        let selectedShipping = null;
        let baseTotal = <?= $total ?>;
        let freteValue = 0;
        
        function formatCEP(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            input.value = value;
        }
        
        function consultarCEP() {
            const cep = document.getElementById('cep').value.replace(/\D/g, '');
            
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('endereco').value = data.logradouro || '';
                            document.getElementById('bairro').value = data.bairro || '';
                            document.getElementById('cidade').value = data.localidade || '';
                            document.getElementById('estado').value = data.uf || '';
                            
                            // Simular cálculo de frete baseado no CEP
                            calcularFrete(cep);
                        } else {
                            alert('CEP não encontrado!');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao consultar CEP:', error);
                    });
            }
        }
        
        function calcularFrete(cep) {
            // Simulação de cálculo de frete
            const estadoSC = ['88', '89']; // CEPs de SC
            const regiao = cep.substring(0, 2);
            
            let sedexPrice = 15.90;
            let pacPrice = 12.50;
            let expressaPrice = 25.00;
            
            if (!estadoSC.includes(regiao)) {
                sedexPrice += 5.00;
                pacPrice += 3.00;
                expressaPrice = 0; // Expressa não disponível fora de SC
            }
            
            document.getElementById('sedex-price').textContent = `R$ ${sedexPrice.toFixed(2).replace('.', ',')}`;
            document.getElementById('pac-price').textContent = `R$ ${pacPrice.toFixed(2).replace('.', ',')}`;
            
            if (expressaPrice === 0) {
                document.querySelector('.shipping-option:last-child').style.display = 'none';
            } else {
                document.getElementById('expressa-price').textContent = `R$ ${expressaPrice.toFixed(2).replace('.', ',')}`;
            }
        }
        
        function selectShipping(type) {
            // Remove seleção anterior
            document.querySelectorAll('.shipping-option').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Adiciona nova seleção
            event.target.closest('.shipping-option').classList.add('selected');
            document.getElementById('tipo_frete').value = type;
            
            // Define valor do frete
            let precoFrete = 0;
            switch(type) {
                case 'sedex':
                    precoFrete = parseFloat(document.getElementById('sedex-price').textContent.replace('R$ ', '').replace(',', '.'));
                    break;
                case 'pac':
                    precoFrete = parseFloat(document.getElementById('pac-price').textContent.replace('R$ ', '').replace(',', '.'));
                    break;
                case 'expressa':
                    precoFrete = parseFloat(document.getElementById('expressa-price').textContent.replace('R$ ', '').replace(',', '.'));
                    break;
            }
            
            freteValue = precoFrete;
            document.getElementById('valor_frete').value = precoFrete;
            
            // Atualizar totais
            updateTotals();
            selectedShipping = type;
        }
        
        function updateTotals() {
            const totalFinal = baseTotal + freteValue;
            
            document.getElementById('frete-display').textContent = freteValue.toFixed(2).replace('.', ',');
            document.getElementById('total-display').textContent = totalFinal.toFixed(2).replace('.', ',');
            document.getElementById('total_final').value = totalFinal;
            document.getElementById('boleto-total').textContent = totalFinal.toFixed(2).replace('.', ',');
            
            // Atualizar opções de parcelas
            updateParcelasOptions(totalFinal);
        }
        
        function updateParcelasOptions(total) {
            const parcelasSelect = document.getElementById('parcelas');
            parcelasSelect.innerHTML = '';
            
            for (let i = 1; i <= 6; i++) {
                const valorParcela = total / i;
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',')} sem juros`;
                parcelasSelect.appendChild(option);
            }
        }
        
        function selectPayment(method) {
            // Remove seleção anterior
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Remove seções anteriores
            document.querySelectorAll('.payment-section').forEach(el => {
                el.classList.remove('active');
            });
            
            // Adiciona nova seleção
            event.target.closest('.payment-method').classList.add('selected');
            document.getElementById(method + '-section').classList.add('active');
            document.getElementById('forma_pagamento').value = method;
            
            selectedPayment = method;
            
            // Gera QR Code para PIX
            if (method === 'pix') {
                generateQRCode();
            }
            
            // Mostra opções de parcelas para crédito
            if (method === 'cartao') {
                document.getElementById('tipo_cartao').addEventListener('change', function() {
                    const creditoOptions = document.getElementById('credito-options');
                    if (this.value === 'credito') {
                        creditoOptions.style.display = 'block';
                    } else {
                        creditoOptions.style.display = 'none';
                    }
                });
            }
        }
        
        function generateQRCode() {
            const qrCodeDiv = document.getElementById('qrcode');
            qrCodeDiv.innerHTML = ''; // Limpa QR code anterior
            
            const pixCode = document.getElementById('pix-code').value;
            QRCode.toCanvas(qrCodeDiv, pixCode, {
                width: 200,
                height: 200,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, function (error) {
                if (error) console.error(error);
            });
        }
        
        function copyPixCode() {
            const pixCode = document.getElementById('pix-code');
            pixCode.select();
            document.execCommand('copy');
            
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Copiado!';
            button.style.background = '#4CAF50';
            
            setTimeout(() => {
                button.textContent = originalText;
                button.style.background = '#666';
            }, 2000);
        }
        
        function formatCardNumber(input) {
            let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            input.value = formattedValue;
        }
        
        function formatExpiry(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            input.value = value;
        }
        
        // Validação do formulário
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            // Validar endereço
            const requiredFields = ['cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado'];
            let hasEmptyField = false;
            
            requiredFields.forEach(field => {
                if (!document.getElementById(field).value.trim()) {
                    hasEmptyField = true;
                }
            });
            
            if (hasEmptyField) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios do endereço.');
                return;
            }
            
            if (!selectedShipping) {
                e.preventDefault();
                alert('Por favor, selecione uma opção de entrega.');
                return;
            }
            
            if (!selectedPayment) {
                e.preventDefault();
                alert('Por favor, selecione uma forma de pagamento.');
                return;
            }
            
            if (selectedPayment === 'cartao') {
                const numero = document.getElementById('numero_cartao').value.replace(/\s/g, '');
                const nome = document.getElementById('nome_cartao').value;
                const validade = document.getElementById('validade').value;
                const cvv = document.getElementById('cvv').value;
                
                if (numero.length < 13 || !nome || validade.length < 5 || cvv.length < 3) {
                    e.preventDefault();
                    alert('Por favor, preencha todos os dados do cartão corretamente.');
                    return;
                }
            }
        });
        
        // Inicializar parcelas com valor base
        updateParcelasOptions(baseTotal);
    </script>
</body>
</html>