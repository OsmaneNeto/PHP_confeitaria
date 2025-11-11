-- Tabela de Encomendas
CREATE TABLE IF NOT EXISTS encomendas (
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
CREATE INDEX IF NOT EXISTS idx_encomendas_status ON encomendas(status);
CREATE INDEX IF NOT EXISTS idx_encomendas_data_entrega ON encomendas(data_entrega);
CREATE INDEX IF NOT EXISTS idx_encomendas_receita ON encomendas(receita_id);
CREATE INDEX IF NOT EXISTS idx_encomendas_data_pedido ON encomendas(data_pedido);

