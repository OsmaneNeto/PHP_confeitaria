-- Migração simples (sem procedures) para compatibilidade com phpMyAdmin
-- Execute este script após certificar-se de ter backup do banco de dados

USE confeitaria_db;

-- Adicionar coluna fator_conversao na tabela insumos (se não existir)
ALTER TABLE insumos ADD COLUMN fator_conversao DECIMAL(10,3) DEFAULT 1.000;

-- Caso ainda exista a coluna antiga nomeada unidade_medida, renomeie manualmente antes:
-- ALTER TABLE insumos CHANGE COLUMN unidade_medida unidade_compra ENUM('kg','g','L','ml','un','cx','pct') NOT NULL;

-- Atualizar valores padrão de fator_conversao (ajuste conforme necessário)
UPDATE insumos SET fator_conversao = 1000.000 WHERE unidade_compra IN ('kg','L') AND (fator_conversao = 1.000 OR fator_conversao IS NULL);

-- Modificar coluna tipo_movimentacao no historico_estoque para incluir 'desperdicio'
ALTER TABLE historico_estoque MODIFY COLUMN tipo_movimentacao ENUM('entrada','saida','ajuste','desperdicio') NOT NULL;

-- Renomear unidade_medida para unidade_uso na tabela receita_ingredientes se aplicável (execute somente se a coluna existir)
-- ALTER TABLE receita_ingredientes CHANGE COLUMN unidade_medida unidade_uso ENUM('kg','g','L','ml','un','cx','pct') NOT NULL;

-- Criar tabela desperdicios se não existir
CREATE TABLE IF NOT EXISTS desperdicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insumo_id INT NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL,
    motivo ENUM('validade','quebra','consumo_interno','outro') NOT NULL,
    descricao TEXT,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    registrado_por VARCHAR(255),
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);

-- Criar tabela custo_extra se não existir
CREATE TABLE IF NOT EXISTS custo_extra (
  id INT NOT NULL AUTO_INCREMENT,
  id_receita INT NOT NULL,
  descricao VARCHAR(150) NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY id_receita_idx (id_receita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Adicionar coluna baixa_realizada em encomendas (execute apenas se tabela encomendas existir)
ALTER TABLE encomendas ADD COLUMN baixa_realizada TINYINT(1) NOT NULL DEFAULT 0;

-- Observação: Algumas instruções acima (ALTER TABLE ADD COLUMN) podem falhar se a coluna já existir
-- ou se sua versão do MySQL não suportar certos tipos de cláusulas. Execute manualmente as alterações
-- comentadas (CHANGE COLUMN) apenas quando tiver certeza do estado atual do seu schema.
