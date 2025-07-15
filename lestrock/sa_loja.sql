CREATE DATABASE sa_loja;
USE sa_loja;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    nascimento DATE,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE discos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discogs_id INT UNIQUE,
    titulo VARCHAR(150) NOT NULL,
    artista VARCHAR(100) NOT NULL,
    ano INT,
    gravadora VARCHAR(50),
    genero VARCHAR(50),
    estilo VARCHAR(50),
    preco DECIMAL(10,2) NOT NULL,
    descricao VARCHAR(250) NOT NULL,
    pais VARCHAR(50),
    imagem_url VARCHAR(255),
    estoque INT DEFAULT 1
);

CREATE TABLE carrinhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    finalizado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE itens_carrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carrinho_id INT NOT NULL,
    disco_id INT NOT NULL,
    quantidade INT NOT NULL,
    FOREIGN KEY (carrinho_id) REFERENCES carrinhos(id) ON DELETE CASCADE,
    FOREIGN KEY (disco_id) REFERENCES discos(id) ON DELETE CASCADE
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    carrinho_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'pago', 'enviado', 'cancelado') DEFAULT 'pendente',
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (carrinho_id) REFERENCES carrinhos(id) ON DELETE CASCADE
);

CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    disco_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (disco_id) REFERENCES discos(id) ON DELETE CASCADE
);

CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    usuario_id INT,
    forma_pagamento VARCHAR(50) NOT NULL,
    valor_pago DECIMAL(10,2) NOT NULL,
    taxa DECIMAL(10,2) DEFAULT 0,
    status_pagamento ENUM('aguardando', 'pago', 'falhou', 'cancelado', 'estornado') DEFAULT 'aguardando',
    codigo_transacao VARCHAR(100) DEFAULT NULL,
    data_pagamento DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE logs_importacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_importacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    quantidade_importada INT,
    detalhes TEXT,
    status ENUM('sucesso', 'erro') DEFAULT 'sucesso'
);

INSERT INTO usuarios (nome, nascimento, email, senha_hash, telefone) VALUES
('João Silva', '1990-05-15', 'joao.silva@example.com', SHA2('senha123', 256), '11999999901'),
('Maria Oliveira', '1985-03-22', 'maria.oliveira@example.com', SHA2('senha123', 256), '11999999902'),
('Carlos Souza', '1992-11-09', 'carlos.souza@example.com', SHA2('senha123', 256), '11999999903'),
('Ana Lima', '1995-07-01', 'ana.lima@example.com', SHA2('senha123', 256), '11999999904'),
('Bruno Costa', '1988-02-17', 'bruno.costa@example.com', SHA2('senha123', 256), '11999999905'),
('Juliana Rocha', '1991-06-30', 'juliana.rocha@example.com', SHA2('senha123', 256), '11999999906'),
('Felipe Mendes', '1993-08-14', 'felipe.mendes@example.com', SHA2('senha123', 256), '11999999907'),
('Patrícia Gomes', '1987-01-11', 'patricia.gomes@example.com', SHA2('senha123', 256), '11999999908'),
('Lucas Fernandes', '1996-04-25', 'lucas.fernandes@example.com', SHA2('senha123', 256), '11999999909'),
('Camila Ribeiro', '1994-10-05', 'camila.ribeiro@example.com', SHA2('senha123', 256), '11999999910'),
('Thiago Martins', '1990-09-19', 'thiago.martins@example.com', SHA2('senha123', 256), '11999999911'),
('Renata Dias', '1989-12-03', 'renata.dias@example.com', SHA2('senha123', 256), '11999999912'),
('Eduardo Lima', '1997-03-28', 'eduardo.lima@example.com', SHA2('senha123', 256), '11999999913'),
('Vanessa Alves', '1986-05-20', 'vanessa.alves@example.com', SHA2('senha123', 256), '11999999914'),
('Rafael Cardoso', '1998-08-09', 'rafael.cardoso@example.com', SHA2('senha123', 256), '11999999915'),
('Letícia Moraes', '1991-02-13', 'leticia.moraes@example.com', SHA2('senha123', 256), '11999999916'),
('André Santos', '1984-11-21', 'andre.santos@example.com', SHA2('senha123', 256), '11999999917'),
('Gabriela Pinto', '1993-07-07', 'gabriela.pinto@example.com', SHA2('senha123', 256), '11999999918'),
('Rodrigo Azevedo', '1987-06-26', 'rodrigo.azevedo@example.com', SHA2('senha123', 256), '11999999919'),
('Fernanda Carvalho', '1992-01-30', 'fernanda.carvalho@example.com', SHA2('senha123', 256), '11999999920'),
('Kaio Eduardo Soares Fragoso', '2007-12-01', 'kaiodudu10@gmail.com', SHA2('123456', 256), '47999169146');

ALTER TABLE usuarios ADD COLUMN is_admin BOOLEAN DEFAULT FALSE;

INSERT INTO usuarios (nome, email, senha_hash, is_admin)
VALUES (
    'Administrador',
    'admin@letsrock.com',
    '$2y$10$1.emyd2jk96TSm.beHcl3OKSXFOXXBQRYKO96J3rXh2z5aZEGfu7C',
    true
);