<?php
// Iniciar sessão
session_start();

// Configurações de erro para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir conexão
require_once 'conexao.php';

// Variáveis para mensagens
$mensagem = '';
$erro = '';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Capturar dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $nascimento = trim($_POST['nascimento'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    // Validações básicas
    if (empty($nome) || empty($email) || empty($nascimento) || empty($telefone) || empty($senha)) {
        $erro = 'Todos os campos são obrigatórios!';
    } 
    // Validar formato do email
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido!';
    }
    // Validar formato da data
    else if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $nascimento)) {
        $erro = 'Data de nascimento deve estar no formato DD/MM/AAAA!';
    }
    // Se passou pelas validações básicas
    else {
        // Converter data para formato MySQL
        $data_parts = explode('/', $nascimento);
        $dia = $data_parts[0];
        $mes = $data_parts[1];
        $ano = $data_parts[2];
        
        // Validar se a data existe
        if (!checkdate($mes, $dia, $ano)) {
            $erro = 'Data de nascimento inválida!';
        } else {
            // Converter para formato MySQL (YYYY-MM-DD)
            $nascimento_mysql = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT);
            
            try {
                // Verificar se email já existe
                $sql_verificar = "SELECT COUNT(*) as total FROM usuarios WHERE email = ?";
                $stmt_verificar = $pdo->prepare($sql_verificar);
                $stmt_verificar->execute([$email]);
                $resultado = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
                
                if ($resultado['total'] > 0) {
                    $erro = 'Este email já está cadastrado!';
                } else {
                    // Gerar hash da senha (usando password_hash para segurança)
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    
                    // Inserir novo usuário
                    $sql_inserir = "INSERT INTO usuarios (nome, nascimento, email, telefone, senha_hash) VALUES (?, ?, ?, ?, ?)";
                    $stmt_inserir = $pdo->prepare($sql_inserir);
                    
                    if ($stmt_inserir->execute([$nome, $nascimento_mysql, $email, $telefone, $senha_hash])) {
                        // Sucesso! Redirecionar para login
                        $_SESSION['mensagem_sucesso'] = 'Cadastro realizado com sucesso! Faça seu login.';
                        header('Location: login.php');
                        exit();
                    } else {
                        $erro = 'Erro ao cadastrar usuário. Tente novamente.';
                    }
                }
                
            } catch (PDOException $e) {
                $erro = 'Erro no banco de dados: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Let's Rock Disco Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <header class="header">
        <div class="header-content">
            <h1 class="main-title">CADASTRE E DIVIRTA-SE</h1>
            <img src="imagens/logo.png" alt="Logo da página" title="Logo da página">
        </div>
    </header>

    <div class="container">
        <div class="contact-info">
            <h2 class="contact-title">Qualquer dúvida ou sugestão nos contate</h2>
            <div class="contact-email">
                <a href="mailto:contato@letsrock.com">LETSROCKDISOSTORE@GMAIL.COM</a>
            </div>
        </div>

        <div class="form-container">
            <h2 class="form-title">CADASTRO</h2>
            
            <?php if (!empty($mensagem)): ?>
                <div class="mensagem sucesso"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($erro)): ?>
                <div class="mensagem erro"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <input type="text" name="nome" class="form-input" placeholder="Seu nome" 
                           value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="nascimento" class="form-input" placeholder="Nascimento: DD/MM/AAAA" 
                           value="<?php echo isset($nascimento) ? htmlspecialchars($nascimento) : ''; ?>" 
                           pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}" title="Use o formato DD/MM/AAAA" required>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="E-mail" 
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="tel" name="telefone" class="form-input" placeholder="Número de Telefone" 
                           value="<?php echo isset($telefone) ? htmlspecialchars($telefone) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="senha" class="form-input" placeholder="Senha" 
                           minlength="6" required>
                </div>
                
                <button type="submit" class="submit-btn">CADASTRAR</button>
            </form>
            
            <div class="login-links">
                <a href="login.php" class="link">Já tem conta? Faça login</a>
            </div>
        </div>

        <div class="social-section">
            <h2 class="social-title">NOS ACOMPANHE EM NOSSAS<br>REDES SOCIAIS</h2>
            <div class="social-icons">
                <a href="https://instagram.com/letsrock_discostore" class="social-icon">@</a>
                <a href="https://x.com/letsrock_ds" class="social-icon">X</a>
            </div>
        </div>
    </div>
    
    <!-- Script para máscara de data -->
    <script>
        // Adicionar máscara na data de nascimento
        document.addEventListener('DOMContentLoaded', function() {
            const nascimentoInput = document.querySelector('input[name="nascimento"]');
            
            nascimentoInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
                
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2);
                }
                if (value.length >= 5) {
                    value = value.substring(0, 5) + '/' + value.substring(5, 9);
                }
                
                e.target.value = value;
            });
        });
    </script>
    
    <script src="js/js.js"></script>
</body>
</html>