-- Schema do banco de dados para Sistema de Gestão da Doceria
-- Criado para gerenciar insumos, compras e controle de estoque

CREATE DATABASE IF NOT EXISTS confeitaria_db;
USE confeitaria_db;

-- Tabela de Insumos
CREATE TABLE insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    unidade_compra ENUM('kg', 'g', 'L', 'ml', 'un', 'cx', 'pct') NOT NULL,
    fator_conversao DECIMAL(10,3) DEFAULT 1.000,
    estoque_atual DECIMAL(10,3) DEFAULT 0,
    estoque_minimo DECIMAL(10,3) DEFAULT 0,
    custo_unitario_atual DECIMAL(10,2) DEFAULT 0,
    categoria VARCHAR(100),
    fornecedor VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de Compras
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insumo_id INT NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL,
    preco_total DECIMAL(10,2) NOT NULL,
    custo_unitario DECIMAL(10,2) NOT NULL,
    fornecedor VARCHAR(255),
    data_compra DATE NOT NULL,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);

-- Tabela de Histórico de Estoque (para controle de movimentações)
CREATE TABLE historico_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insumo_id INT NOT NULL,
    tipo_movimentacao ENUM('entrada', 'saida', 'ajuste', 'desperdicio') NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL,
    custo_unitario DECIMAL(10,2),
    motivo VARCHAR(255),
    referencia_id INT, -- ID da compra ou outro registro que originou a movimentação
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);

-- Tabela de Alertas de Estoque
CREATE TABLE alertas_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insumo_id INT NOT NULL,
    tipo_alerta ENUM('estoque_minimo', 'estoque_zerado') NOT NULL,
    quantidade_atual DECIMAL(10,3) NOT NULL,
    quantidade_minima DECIMAL(10,3) NOT NULL,
    data_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    visualizado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);

-- Índices para melhorar performance
CREATE INDEX idx_insumos_nome ON insumos(nome);
CREATE INDEX idx_insumos_categoria ON insumos(categoria);
CREATE INDEX idx_insumos_ativo ON insumos(ativo);
CREATE INDEX idx_compras_insumo ON compras(insumo_id);
CREATE INDEX idx_compras_data ON compras(data_compra);
CREATE INDEX idx_historico_insumo ON historico_estoque(insumo_id);
CREATE INDEX idx_historico_data ON historico_estoque(data_movimentacao);
CREATE INDEX idx_alertas_visualizado ON alertas_estoque(visualizado);

-- Tabela de Receitas
CREATE TABLE receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(100),
    rendimento DECIMAL(10,2) NOT NULL DEFAULT 1,
    unidade_rendimento VARCHAR(50) DEFAULT 'un',
    tempo_preparo INT DEFAULT 0, -- em minutos
    dificuldade ENUM('facil', 'medio', 'dificil') DEFAULT 'medio',
    instrucoes TEXT,
    custo_total DECIMAL(10,2) DEFAULT 0,
    preco_venda_sugerido DECIMAL(10,2) DEFAULT 0,
    margem_lucro DECIMAL(5,2) DEFAULT 30.00,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de Ingredientes das Receitas
CREATE TABLE receita_ingredientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receita_id INT NOT NULL,
    insumo_id INT NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL,
    unidade_uso ENUM('kg', 'g', 'L', 'ml', 'un', 'cx', 'pct') NOT NULL,
    observacoes TEXT,
    ordem INT DEFAULT 0,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE CASCADE,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);

-- Tabela de Produção (quando uma receita é produzida)
CREATE TABLE producoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receita_id INT NOT NULL,
    quantidade_produzida DECIMAL(10,2) NOT NULL,
    data_producao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    custo_total DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE CASCADE
);

-- Tabela de Controle de Validade de Insumos
CREATE TABLE controle_validade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insumo_id INT NOT NULL,
    lote VARCHAR(100),
    quantidade_lote DECIMAL(10,3) NOT NULL,
    data_fabricacao DATE,
    data_validade DATE NOT NULL,
    quantidade_atual DECIMAL(10,3) NOT NULL,
    status ENUM('valido', 'proximo_vencer', 'vencido') DEFAULT 'valido',
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);

-- Tabela de Alertas de Validade
CREATE TABLE alertas_validade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    controle_validade_id INT NOT NULL,
    insumo_id INT NOT NULL,
    tipo_alerta ENUM('proximo_vencer', 'vencido') NOT NULL,
    dias_para_vencer INT,
    data_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    visualizado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (controle_validade_id) REFERENCES controle_validade(id) ON DELETE CASCADE,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);

-- Tabela de Desperdício
CREATE TABLE desperdicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insumo_id INT NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL,
    motivo ENUM('validade', 'quebra', 'consumo_interno', 'outro') NOT NULL,
    descricao TEXT,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    registrado_por VARCHAR(255),
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);

-- Índices adicionais
CREATE INDEX idx_receitas_categoria ON receitas(categoria);
CREATE INDEX idx_receitas_ativo ON receitas(ativo);
CREATE INDEX idx_receita_ingredientes_receita ON receita_ingredientes(receita_id);
CREATE INDEX idx_receita_ingredientes_insumo ON receita_ingredientes(insumo_id);
CREATE INDEX idx_producoes_receita ON producoes(receita_id);
CREATE INDEX idx_producoes_data ON producoes(data_producao);
CREATE INDEX idx_controle_validade_insumo ON controle_validade(insumo_id);
CREATE INDEX idx_controle_validade_status ON controle_validade(status);
CREATE INDEX idx_controle_validade_data_validade ON controle_validade(data_validade);
CREATE INDEX idx_alertas_validade_visualizado ON alertas_validade(visualizado);
CREATE INDEX idx_desperdicios_insumo ON desperdicios(insumo_id);
CREATE INDEX idx_desperdicios_data ON desperdicios(data_registro);
CREATE INDEX idx_desperdicios_motivo ON desperdicios(motivo);

-- Tabela de Encomendas
CREATE TABLE encomendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_nome VARCHAR(255) NOT NULL,
    cliente_telefone VARCHAR(20),
    cliente_email VARCHAR(255),
    receita_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    preco_total DECIMAL(10,2) NOT NULL,
    data_entrega DATE NOT NULL,
    status ENUM('pendente', 'em_producao', 'pronta', 'entregue', 'cancelada') DEFAULT 'pendente',
    observacoes TEXT,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE RESTRICT
);

-- Índices para encomendas
CREATE INDEX idx_encomendas_status ON encomendas(status);
CREATE INDEX idx_encomendas_data_entrega ON encomendas(data_entrega);
CREATE INDEX idx_encomendas_receita ON encomendas(receita_id);
CREATE INDEX idx_encomendas_data_pedido ON encomendas(data_pedido);

-- Inserir alguns dados de exemplo
INSERT INTO insumos (nome, descricao, unidade_compra, fator_conversao, estoque_atual, estoque_minimo, custo_unitario_atual, categoria, fornecedor) VALUES
('Açúcar', 'Açúcar refinado para confeitaria', 'kg', 1000.000, 50.000, 10.000, 4.50, 'Ingredientes Básicos', 'Distribuidora ABC'),
('Farinha de Trigo', 'Farinha de trigo especial para bolos', 'kg', 1000.000, 25.000, 5.000, 3.80, 'Ingredientes Básicos', 'Moinho XYZ'),
('Ovos', 'Ovos frescos tipo A', 'un', 1.000, 100.000, 20.000, 0.35, 'Ingredientes Básicos', 'Granja São José'),
('Manteiga', 'Manteiga sem sal', 'kg', 1000.000, 8.000, 2.000, 12.50, 'Laticínios', 'Laticínio Central'),
('Chocolate em Pó', 'Cacau em pó 100%', 'kg', 1000.000, 5.000, 1.000, 18.90, 'Chocolates', 'Cacau Brasil'),
('Fermento', 'Fermento químico', 'kg', 1000.000, 2.000, 0.500, 8.90, 'Ingredientes Básicos', 'Distribuidora ABC'),
('Leite', 'Leite integral', 'L', 1000.000, 20.000, 5.000, 3.20, 'Laticínios', 'Laticínio Central'),
('Baunilha', 'Essência de baunilha', 'ml', 1.000, 500.000, 50.000, 0.15, 'Aromatizantes', 'Distribuidora ABC');

-- Inserir receitas de exemplo
INSERT INTO receitas (nome, descricao, categoria, rendimento, unidade_rendimento, tempo_preparo, dificuldade, instrucoes) VALUES
('Bolo de Chocolate', 'Delicioso bolo de chocolate tradicional', 'Bolos', 1.00, 'un', 60, 'facil', 'Misture todos os ingredientes secos. Adicione os líquidos aos poucos. Asse em forno médio por 40 minutos.'),
('Cupcake de Baunilha', 'Cupcakes fofinhos de baunilha', 'Cupcakes', 12.00, 'un', 45, 'facil', 'Prepare a massa e distribua nas forminhas. Asse por 20 minutos. Deixe esfriar antes de decorar.'),
('Torta de Morango', 'Torta cremosa com morangos frescos', 'Tortas', 1.00, 'un', 90, 'medio', 'Prepare a massa, o creme e monte a torta em camadas. Leve à geladeira por 2 horas antes de servir.');

-- Inserir ingredientes das receitas
INSERT INTO receita_ingredientes (receita_id, insumo_id, quantidade, unidade_uso, ordem) VALUES
-- Bolo de Chocolate
(1, 1, 2.000, 'kg', 1), -- Açúcar
(1, 2, 3.000, 'kg', 2), -- Farinha
(1, 3, 6.000, 'un', 3), -- Ovos
(1, 4, 0.500, 'kg', 4), -- Manteiga
(1, 5, 0.200, 'kg', 5), -- Chocolate em pó
(1, 6, 0.050, 'kg', 6), -- Fermento
(1, 7, 1.000, 'L', 7), -- Leite
(1, 8, 10.000, 'ml', 8), -- Baunilha

-- Cupcake de Baunilha
(2, 1, 1.500, 'kg', 1), -- Açúcar
(2, 2, 2.000, 'kg', 2), -- Farinha
(2, 3, 4.000, 'un', 3), -- Ovos
(2, 4, 0.300, 'kg', 4), -- Manteiga
(2, 6, 0.030, 'kg', 5), -- Fermento
(2, 7, 0.500, 'L', 6), -- Leite
(2, 8, 15.000, 'ml', 7), -- Baunilha

-- Torta de Morango
(3, 1, 1.000, 'kg', 1), -- Açúcar
(3, 2, 1.500, 'kg', 2), -- Farinha
(3, 3, 3.000, 'un', 3), -- Ovos
(3, 4, 0.400, 'kg', 4), -- Manteiga
(3, 7, 0.800, 'L', 5), -- Leite
(3, 8, 5.000, 'ml', 6); -- Baunilha
